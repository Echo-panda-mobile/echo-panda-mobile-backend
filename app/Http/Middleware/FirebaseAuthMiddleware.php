<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\FirebaseUserProvisioner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuthMiddleware
{
    public function __construct(protected FirebaseUserProvisioner $firebaseUserProvisioner)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $idToken = $request->bearerToken()
            ?: $request->json('id_token')
            ?: $request->input('id_token')
            ?: $request->query('id_token');

        if (! $idToken) {
            return response()->json([
                'message' => 'Firebase ID token is required.',
            ], 401);
        }

        try {
            $verifiedToken = $this->firebaseUserProvisioner->verifyIdToken($idToken);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Invalid Firebase ID token.',
            ], 401);
        }

        $claims = $verifiedToken->claims();
        $firebaseUid = (string) $claims->get('sub');
        $email = (string) ($claims->get('email') ?: $request->input('email', ''));
        $name = (string) ($claims->get('name') ?: $request->input('name', ''));

        if (! $firebaseUid) {
            return response()->json([
                'message' => 'Firebase UID missing from token.',
            ], 401);
        }

        $user = User::query()->where('firebase_uid', $firebaseUid)->first();

        if (! $user && $email) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $user = User::create([
                'firebase_uid' => $firebaseUid,
                'email' => $email ?: $firebaseUid.'@firebase.local',
                'name' => $name ?: 'Firebase User',
                'password' => Hash::make((string) Str::uuid()),
                'role' => User::ROLE_USER,
            ]);
        } else {
            $updates = [];

            if (! $user->firebase_uid) {
                $updates['firebase_uid'] = $firebaseUid;
            }

            if ($email && $user->email !== $email) {
                $updates['email'] = $email;
            }

            if ($name && $user->name !== $name) {
                $updates['name'] = $name;
            }

            if ($updates) {
                $user->forceFill($updates)->save();
            }
        }

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('firebase_uid', $firebaseUid);
        $request->attributes->set('firebase_claims', $claims);

        return $next($request);
    }
}