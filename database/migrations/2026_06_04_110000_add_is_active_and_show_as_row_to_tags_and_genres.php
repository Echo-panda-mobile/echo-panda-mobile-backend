<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['tags', 'genres'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('slug');
                }

                if (Schema::hasColumn($tableName, 'show_value')) {
                    $table->dropColumn('show_value');
                }

                if (! Schema::hasColumn($tableName, 'show_as_row')) {
                    $table->boolean('show_as_row')->default(true)->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['tags', 'genres'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'show_as_row')) {
                    $table->dropColumn('show_as_row');
                }
                if (Schema::hasColumn($tableName, 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
