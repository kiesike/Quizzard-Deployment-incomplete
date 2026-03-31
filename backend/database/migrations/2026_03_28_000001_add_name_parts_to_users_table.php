<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'middle_initial')) {
                $table->string('middle_initial', 1)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'surname')) {
                $table->string('surname')->nullable()->after('middle_initial');
            }
        });

        // Backfill existing name into parts when possible (simple split)
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if (empty($user->first_name) && !empty($user->name)) {
                $parts = preg_split('/\s+/', trim($user->name));
                if (count($parts) === 1) {
                    $user->first_name = $parts[0];
                } elseif (count($parts) === 2) {
                    $user->first_name = $parts[0];
                    $user->surname = $parts[1];
                } else {
                    $user->first_name = array_shift($parts);
                    $user->surname = array_pop($parts);
                    $user->middle_initial = strtoupper(substr($parts[0], 0, 1));
                }
                $user->save();
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'surname']);
        });
    }
};