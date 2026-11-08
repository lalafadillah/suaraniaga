<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Membuat akun admin.
 *
 * Peran admin tidak bisa dipilih saat pendaftaran publik, jadi akun admin
 * dibuat lewat perintah ini.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create
                            {--name= : Nama lengkap admin}
                            {--email= : Email admin}
                            {--password= : Kata sandi minimal 8 karakter}';

    protected $description = 'Membuat akun admin SuaraNiaga';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama lengkap');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Kata sandi (minimal 8 karakter)');

        if (! is_string($name) || ! is_string($email) || ! is_string($password)) {
            $this->components->error('Nama, email, dan kata sandi wajib diisi.');

            return self::FAILURE;
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->components->error($pesan);
            }

            return self::FAILURE;
        }

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->components->info('Akun admin berhasil dibuat: '.$admin->name.' ('.$admin->email.')');

        return self::SUCCESS;
    }
}
