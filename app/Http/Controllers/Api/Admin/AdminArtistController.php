<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use App\Services\FirebaseUserProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminArtistController extends Controller
{
    public function store(Request $request, FirebaseUserProvisioner $firebaseProvisioner): JsonResponse
    {
        $this->authorize('create', Artist::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'artist_type' => ['nullable', Rule::in(['single', 'group', 'Single', 'Group'])],
            'gender' => ['nullable', Rule::in(['male', 'female', 'they', 'Male', 'Female', 'They'])],
            'verification_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $plainPassword = $validated['password'];
        $email = strtolower(trim($validated['email']));

        $existingUser = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existingUser?->artist) {
            throw ValidationException::withMessages([
                'email' => ['This email already has an artist profile.'],
            ]);
        }

        $user = null;
        $artist = null;

        try {
            $result = DB::transaction(function () use ($validated, $email, $plainPassword, $existingUser, &$user, &$artist) {
                if ($existingUser) {
                    $existingUser->forceFill([
                        'name' => $validated['name'],
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'role' => User::ROLE_ARTIST,
                    ])->save();
                    $user = $existingUser->fresh();
                } else {
                    $userData = [
                        'name' => $validated['name'],
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'role' => User::ROLE_ARTIST,
                    ];

                    if (Schema::hasColumn('users', 'is_banned')) {
                        $userData['is_banned'] = false;
                    }

                    $user = User::create($userData);
                }

                $bioParts = array_filter([
                    ! empty($validated['artist_type'])
                        ? 'Type: '.ucfirst(strtolower($validated['artist_type']))
                        : null,
                    ! empty($validated['gender'])
                        ? 'Gender: '.ucfirst(strtolower($validated['gender']))
                        : null,
                ]);

                $slugBase = Str::slug($validated['name']) ?: 'artist';
                $slug = $slugBase;
                $counter = 2;
                while (Artist::where('slug', $slug)->exists()) {
                    $slug = $slugBase.'-'.$counter++;
                }

                $verificationStatus = $validated['verification_status'] ?? 'pending';

                $artist = Artist::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'bio' => $bioParts ? implode(' | ', $bioParts) : null,
                    'is_active' => true,
                    'verification_status' => $verificationStatus,
                    'verified_at' => $verificationStatus === 'approved' ? now() : null,
                ]);

                return [
                    'user' => $user,
                    'artist' => $artist,
                    'upgraded_from_user' => $existingUser !== null,
                ];
            });
        } catch (\Throwable $throwable) {
            throw $throwable;
        }

        $user = $result['user'];
        $artist = $result['artist'];

        try {
            $provision = $firebaseProvisioner->provisionWithPassword($user, $plainPassword);
            if (empty($provision['firebase_uid'])) {
                throw new \RuntimeException('Firebase UID was not returned.');
            }

            $user->forceFill(['firebase_uid' => $provision['firebase_uid']])->save();
        } catch (\Throwable $throwable) {
            $artist->delete();
            if ($result['upgraded_from_user']) {
                $user->forceFill(['role' => User::ROLE_USER])->save();
            } else {
                $user->delete();
            }

            return response()->json([
                'message' => 'Could not create Firebase login for this artist.',
                'error' => $throwable->getMessage(),
                'hint' => 'Check FIREBASE_CREDENTIALS on the server (service account for echo-panda-auth).',
            ], 502);
        }

        $user->refresh();

        return response()->json([
            'message' => $result['upgraded_from_user']
                ? 'Existing account upgraded to artist.'
                : 'Artist account created successfully.',
            'firebase_message' => 'Firebase account is ready. They can sign in on the app with this email and password.',
            'firebase_provisioned' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'artist' => [
                'id' => $artist->id,
                'name' => $artist->name,
                'slug' => $artist->slug,
                'verification_status' => $artist->verification_status,
            ],
        ], 201);
    }
}
