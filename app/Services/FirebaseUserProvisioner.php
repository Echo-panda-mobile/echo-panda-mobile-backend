<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use RuntimeException;

class FirebaseUserProvisioner
{
    protected ?FirebaseAuth $auth = null;

    protected function auth(): FirebaseAuth
    {
        if ($this->auth) {
            return $this->auth;
        }

        $credentials = config('firebase.credentials');

        if (! $credentials) {
            throw new RuntimeException('Firebase credentials are not configured.');
        }

        $factory = (new Factory())->withServiceAccount($credentials);

        if (config('firebase.project_id')) {
            $factory = $factory->withProjectId(config('firebase.project_id'));
        }

        return $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken)
    {
        return $this->auth()->verifyIdToken($idToken);
    }

    public function provision(User $user): array
    {
        $firebaseAuth = $this->auth();

        $firebaseUser = null;

        if ($user->firebase_uid) {
            try {
                $firebaseUser = $firebaseAuth->getUser($user->firebase_uid);
            } catch (\Throwable $throwable) {
                $firebaseUser = null;
            }
        }

        if (! $firebaseUser) {
            try {
                $firebaseUser = $firebaseAuth->getUserByEmail($user->email);
            } catch (\Throwable $throwable) {
                $firebaseUser = null;
            }
        }

        $displayName = $user->name;

        if ($firebaseUser) {
            $updated = $firebaseAuth->updateUser($firebaseUser->uid, [
                'displayName' => $displayName,
                'email' => $user->email,
                'disabled' => false,
            ]);

            return [
                'firebase_uid' => $updated->uid,
                'invite_link' => $this->createPasswordResetLink($user),
                'created' => false,
            ];
        }

        $created = $firebaseAuth->createUser([
            'email' => $user->email,
            'emailVerified' => false,
            'displayName' => $displayName,
            'disabled' => false,
        ]);

        return [
            'firebase_uid' => $created->uid,
            'invite_link' => $this->createPasswordResetLink($user),
            'created' => true,
        ];
    }

    /**
     * Provision (or update) a Firebase user and set the given password for mobile sign-in.
     */
    public function provisionWithPassword(User $user, string $password): array
    {
        $firebaseAuth = $this->auth();
        $firebaseUser = null;

        if ($user->firebase_uid) {
            try {
                $firebaseUser = $firebaseAuth->getUser($user->firebase_uid);
            } catch (\Throwable) {
                $firebaseUser = null;
            }
        }

        if (! $firebaseUser) {
            try {
                $firebaseUser = $firebaseAuth->getUserByEmail($user->email);
            } catch (\Throwable) {
                $firebaseUser = null;
            }
        }

        if ($firebaseUser) {
            $updated = $firebaseAuth->updateUser($firebaseUser->uid, [
                'displayName' => $user->name,
                'email' => $user->email,
                'password' => $password,
                'disabled' => false,
            ]);

            return [
                'firebase_uid' => $updated->uid,
                'created' => false,
            ];
        }

        $created = $firebaseAuth->createUser([
            'email' => $user->email,
            'password' => $password,
            'emailVerified' => false,
            'displayName' => $user->name,
            'disabled' => false,
        ]);

        return [
            'firebase_uid' => $created->uid,
            'created' => true,
        ];
    }

    public function createPasswordResetLink(User $user): string
    {
        $settings = ValidatedActionCodeSettings::fromArray([
            'continueUrl' => config('app.url').'/login',
        ]);

        return $this->auth()->getPasswordResetLink($user->email, $settings);
    }

    public function sendInvite(User $user): string
    {
        $inviteLink = $this->createPasswordResetLink($user);

        Mail::raw(
            "Set your Echo Panda password using this link:\n\n{$inviteLink}",
            function ($message) use ($user): void {
                $message->to($user->email)->subject('Set your Echo Panda password');
            }
        );

        return $inviteLink;
    }

    public function deleteByUid(?string $firebaseUid): void
    {
        if (! $firebaseUid) {
            return;
        }

        try {
            $this->auth()->deleteUser($firebaseUid);
        } catch (\Throwable $throwable) {
            // Keep deletion best-effort so DB cleanup can still succeed.
        }
    }
}