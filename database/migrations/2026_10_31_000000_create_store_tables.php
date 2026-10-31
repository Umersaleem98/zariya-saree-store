<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (explode(';', file_get_contents(database_path('schema.sql'))) as $sql) {
            if (trim($sql)) {
                DB::statement($sql);
            }
        }
        if (! Schema::hasColumn('users', 'password_hash')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('password_hash', 200)->nullable();
                $t->enum('role', ['customer', 'admin'])->default('customer');
            });
        }
        if (! Schema::hasColumn('users', 'google_subject')) {
            Schema::table('users', fn (Blueprint $t) => $t->string('google_subject')->nullable()->unique());
        }
        // Laravel's default users migration has a non-null password column.
        if (Schema::hasColumn('users', 'password')) {
            DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Store tables contain commerce records. Restore a backup rather than dropping data.');
    }
};
