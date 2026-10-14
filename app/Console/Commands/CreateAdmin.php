<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

#[Signature('idlaps:admin {email} {--generate : Generate local preview credentials in private storage}')]
#[Description('Create a CMS administrator without a public registration page')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower($this->argument('email'));
        if (Validator::make(['email' => $email], ['email' => 'required|email|max:255'])->fails()) {
            $this->error('Email tidak valid.');

            return self::FAILURE;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('Email sudah terdaftar. Akun yang ada tidak diubah.');

            return self::FAILURE;
        }
        if ($this->option('generate') && ! app()->environment('local')) {
            $this->error('--generate hanya tersedia pada lingkungan local.');

            return self::FAILURE;
        }
        $password = $this->option('generate') ? Str::password(24) : $this->secret('Kata sandi (minimal 12 karakter, huruf dan angka)');
        if (! is_string($password) || strlen($password) < 12 || ! preg_match('/[a-zA-Z]/', $password) || ! preg_match('/[0-9]/', $password)) {
            $this->error('Kata sandi harus minimal 12 karakter, berisi huruf dan angka.');

            return self::FAILURE;
        }
        $user = new User(['name' => 'Administrator IDLAPS', 'email' => $email, 'password' => $password]);
        $user->is_admin = true;
        $user->save();
        if ($this->option('generate')) {
            Storage::disk('local')->put('cms-login.txt', 'CMS lokal: '.route('login')."\nEmail: {$email}\nKata sandi: {$password}\n");
            chmod(Storage::disk('local')->path('cms-login.txt'), 0600);
            $this->info('Kredensial lokal tersimpan di storage/app/private/cms-login.txt (tidak masuk Git).');
        }
        $this->info('Administrator berhasil dibuat.');

        return self::SUCCESS;
    }
}
