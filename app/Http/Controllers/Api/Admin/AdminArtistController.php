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

class AdminArtistController extends Controller
{
    public function store(Request $request, FirebaseUserProvisioner $firebaseProvisioner): JsonResponse
    {
        $this->authorize('create', Artist::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'artist_type' => ['nullable', Rule::in(['single', 'group', 'Single', 'Group'])],
            'gender' => ['nullable', Rule::in(['male', 'female', 'they', 'Male', 'Female', 'They'])],
            'verification_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $plainPassword = $validated['password'];
        $createdUser = null;

        try {
            $result = DB::transaction(function () use ($validated, &$createdUser) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => User::ROLE_ARTIST,
                ];

                if (Schema::hasColumn('users', 'is_banned')) {
                    $userData['is_banned'] = false;
                }

                $createdUser = User::create($userData);

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
                    'user_id' => $createdUser->id,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'bio' => $bioParts ? implode(' | ', $bioParts) : null,
                    'is_active' => true,
                    'verification_status' => $verificationStatus,
                    'verified_at' => $verificationStatus === 'approved' ? now() : null,
                ]);

                return [
                    'user' => $createdUser->fresh(),
                    'artist' => $artist,
                ];
            });
        } catch (\Throwable $throwable) {
            if ($createdUser) {
                $createdUser->delete();
            }

            throw $throwable;
        }

        $user = $result['user'];
        $firebaseMessage = null;

        try {
            $provision = $firebaseProvisioner->provisionWithPassword($user, $plainPassword);
            if (! empty($provision['firebase_uid'])) {
                $user->forceFill(['firebase_uid' => $provision['firebase_uid']])->save();
            }
            $firebaseMessage = ($provision['created'] ?? false)
                ? 'Firebase account created. They can sign in on mobile with this email and password.'
                : 'Firebase account updated. They can sign in on mobile with this email and password.';
        } catch (\Throwable $throwable) {
            $firebaseMessage = 'Artist saved, but Firebase setup failed: '.$throwable->getMessage();
        }

        return response()->json([
            'message' => 'Artist account created successfully.',
            'firebase_message' => $firebaseMessage,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'artist' => [
                'id' => $result['artist']->id,
                'name' => $result['artist']->name,
                'slug' => $result['artist']->slug,
                'verification_status' => $result['artist']->verification_status,
            ],
        ], 201);
    }
}
