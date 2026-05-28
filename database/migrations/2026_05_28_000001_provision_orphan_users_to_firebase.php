<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Services\FirebaseUserProvisioner;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfill firebase_uid for all orphan users (NULL firebase_uid).
     * First tries Firebase provisioning; falls back to generating mock UIDs if unavailable.
     * This prevents orphan users that could cause account mismatches.
     */
    public function up(): void
    {
        $provisioner = app(FirebaseUserProvisioner::class);
        $orphanUsers = User::whereNull('firebase_uid')->get();
        
        foreach ($orphanUsers as $user) {
            $firebaseUid = null;
            
            try {
                // Try to provision to Firebase (creates user without password)
                $result = $provisioner->provision($user);
                $firebaseUid = $result['firebase_uid'] ?? null;
                
                // Send invite email only if user has artist/admin role
                if ($firebaseUid && in_array($user->role, ['artist', 'publicer', 'admin'])) {
                    try {
                        $provisioner->sendInvite($user->fresh());
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send invite to {$user->email}: {$e->getMessage()}");
                    }
                }
                
                if ($firebaseUid) {
                    \Log::info("Provisioned orphan user: {$user->email} → firebase_uid: {$firebaseUid}");
                }
            } catch (\Exception $e) {
                \Log::warning("Firebase provisioning unavailable for {$user->email}: {$e->getMessage()}");
            }
            
            // If Firebase provisioning failed or didn't return a UID, generate a mock one
            // This ensures data integrity and allows the NOT NULL constraint to apply
            // In production, these should be provisioned via Firebase Admin Console or API
            if (!$firebaseUid) {
                $firebaseUid = 'dev_' . uniqid() . '_' . hash('crc32', $user->email);
                \Log::warning("Generated mock firebase_uid for {$user->email}: {$firebaseUid}. This should be provisioned in Firebase.");
            }
            
            // Backfill firebase_uid
            $user->forceFill(['firebase_uid' => $firebaseUid])->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set firebase_uid back to NULL for users that were provisioned in this migration
        // (conservative approach - only reset those with 'dev_' prefix mock IDs)
        User::where('firebase_uid', 'like', 'dev_%')->update(['firebase_uid' => null]);
    }
};
