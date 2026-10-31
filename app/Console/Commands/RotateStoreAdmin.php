<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RotateStoreAdmin extends Command
{
    protected $signature = 'store:rotate-admin {--current-email=} {--email=} {--output=}';
    protected $description = 'Rotate an existing store owner login and save the generated password to a private file';

    public function handle(): int
    {
        $email = strtolower(trim($this->option('email') ?? ''));
        $current = $this->option('current-email'); $output = $this->option('output');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $current || ! $output || ! is_dir(dirname($output))) {
            $this->error('Supply current-email, valid email and a private output file in an existing directory.'); return self::FAILURE;
        }
        $directory = realpath(dirname($output));
        if (str_starts_with(str_replace('\\', '/', $directory).'/', str_replace('\\', '/', public_path()).'/')) {
            $this->error('Credentials must be saved outside the web root.'); return self::FAILURE;
        }
        $password = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        DB::transaction(function () use ($current, $email, $password, $output) {
            $u = DB::table('users')->where('email', $current)->where('role', 'super_admin')->lockForUpdate()->first();
            if (! $u) throw new \RuntimeException('Existing Super Admin not found. No changes made.');
            if (DB::table('users')->where('email', $email)->where('id', '!=', $u->id)->exists()) throw new \RuntimeException('That email belongs to another user.');
            DB::table('users')->where('id', $u->id)->update(['email' => $email, 'password_hash' => Hash::make($password), 'auth_version' => $u->auth_version + 1, 'active' => true]);
            if (file_put_contents($output, "Rang Mahal admin\nURL: ".rtrim(config('app.url'), '/')."/admin\nEmail: $email\nPassword: $password\n", LOCK_EX) === false) throw new \RuntimeException('Could not save private credentials.');
            chmod($output, 0600);
        });
        $this->info('Existing owner login rotated. Password saved to the private file; prior sessions are invalidated.');
        return self::SUCCESS;
    }
}
