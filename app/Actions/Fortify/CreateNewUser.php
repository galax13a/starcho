<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (! SiteSetting::isPublicRegistrationEnabled()) {
            throw ValidationException::withMessages([
                'email' => __('admin_ui.site.notify.registration_disabled_support'),
            ]);
        }

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // A fresh database can accept registrations before the optional
        // configuration seeder has created the base roles. In that case the
        // user must remain unprivileged, but registration itself should not
        // become a 500 response.
        try {
            if (Role::query()->where('name', 'user')->where('guard_name', 'web')->exists()) {
                $user->assignRole('user');
            }
        } catch (\Throwable) {
            // Permission tables may not exist during first-run provisioning.
        }

        return $user;
    }
}
