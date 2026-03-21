<?php

namespace App\Console\Commands;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

#[Signature('app:make-admin
    {email? : Email address for the admin account}
    {--name= : Display name for the admin account}
    {--password= : Password for the admin account}
    {--unverified : Require email verification before dashboard access}
    {--send-verification : Send a verification email when the account is left unverified}
')]
#[Description('Create or promote an administrator for the private portfolio dashboard')]
class MakeAdminUserCommand extends Command
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $email = $this->resolveEmail();
            $user = User::query()->firstWhere('email', $email);
            $password = $this->resolvePassword($user);
            $name = $this->resolveName($user);

            Validator::make([
                'name' => $name,
                'email' => $email,
            ], $this->profileRules($user?->id))->validate();

            $admin = $user ?? new User;

            $admin->forceFill([
                'name' => $name,
                'email' => $email,
                'is_admin' => true,
                'email_verified_at' => $this->option('unverified') ? null : now(),
            ]);

            if ($password !== null) {
                $admin->password = $password;
            }

            $admin->save();

            if ($this->shouldSendVerificationEmail($admin)) {
                $admin->sendEmailVerificationNotification();
            }
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->components->error($message);
                }
            }

            return self::FAILURE;
        }

        $this->components->info($user === null ? 'Admin account created.' : 'Admin account updated.');
        $this->line("Email: {$admin->email}");
        $this->line('Verified: '.($admin->hasVerifiedEmail() ? 'yes' : 'no'));
        $this->line('Admin access: yes');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('- Log in at /login.');

        if (! $admin->hasVerifiedEmail()) {
            $this->line('- Open the verification email before trying to access /dashboard.');
        }

        $this->line('- After the first login, enable two-factor authentication in Settings > Security.');
        $this->line('- If you ever need to rotate the password, use /forgot-password.');

        return self::SUCCESS;
    }

    private function resolveEmail(): string
    {
        $email = $this->argument('email');

        if (! is_string($email) || blank($email)) {
            if (! $this->input->isInteractive()) {
                throw new RuntimeException('An email address is required when running without interaction.');
            }

            $email = $this->ask('Email address');
        }

        return Str::lower(trim((string) $email));
    }

    private function resolveName(?User $user): string
    {
        $name = $this->option('name');

        if (is_string($name) && filled($name)) {
            return trim($name);
        }

        if ($user !== null) {
            return $user->name;
        }

        if (! $this->input->isInteractive()) {
            throw new RuntimeException('A name is required when creating an admin without interaction.');
        }

        return trim((string) $this->ask('Full name'));
    }

    private function resolvePassword(?User $user): ?string
    {
        $password = $this->option('password');

        if (is_string($password) && filled($password)) {
            $this->validatePassword($password, $password);

            return $password;
        }

        if (! $this->input->isInteractive()) {
            if ($user === null) {
                throw new RuntimeException('A password is required when creating an admin without interaction.');
            }

            return null;
        }

        if ($user !== null && ! $this->confirm('Set a new password for this account?', false)) {
            return null;
        }

        while (true) {
            $password = (string) $this->secret('Password');
            $confirmedPassword = (string) $this->secret('Confirm password');

            try {
                $this->validatePassword($password, $confirmedPassword);

                return $password;
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $messages) {
                    foreach ($messages as $message) {
                        $this->components->error($message);
                    }
                }
            }
        }
    }

    private function validatePassword(string $password, string $confirmedPassword): void
    {
        Validator::make([
            'password' => $password,
            'password_confirmation' => $confirmedPassword,
        ], [
            'password' => $this->passwordRules(),
        ])->validate();
    }

    private function shouldSendVerificationEmail(User $user): bool
    {
        return (bool) $this->option('send-verification') && ! $user->hasVerifiedEmail();
    }
}
