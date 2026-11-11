<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

final class LoginAccount
{
    public function createStaff(
        string $name,
        string $username,
        string $password,
        RoleName $role,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $mobile = null,
    ): User {
        $user = User::query()->create([
            'name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mobile' => $mobile,
            'username' => $username,
            'email' => $email ?: $this->uniqueEmail($username, 'staff'),
            'password' => $password,
        ]);

        $roleId = Role::query()->where('name', $role->value)->value('id');

        if (is_int($roleId) || is_numeric($roleId)) {
            $user->roles()->sync([(int) $roleId]);
        }

        return $user;
    }

    public function createPortal(string $name, string $username, string $password, ?string $email = null): User
    {
        return User::query()->create([
            'name' => $name,
            'username' => $username,
            'email' => $email ?: $this->uniqueEmail($username, 'customer'),
            'password' => $password,
        ]);
    }

    public function update(
        User $user,
        string $name,
        string $username,
        ?string $password = null,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $mobile = null,
    ): User {
        $user->fill([
            'name' => $name,
            'username' => $username,
        ]);

        if ($firstName !== null) {
            $user->first_name = $firstName;
        }

        if ($lastName !== null) {
            $user->last_name = $lastName;
        }

        if ($mobile !== null) {
            $user->mobile = $mobile;
        }

        if ($email !== null && $email !== '') {
            $user->email = $email;
        }

        if ($password !== null && $password !== '') {
            $user->password = $password;
        }

        $user->save();

        return $user;
    }

    public function uniqueEmail(string $username, string $suffix): string
    {
        $safe = Str::lower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $username));
        $safe = trim($safe, '-');

        if ($safe === '') {
            $safe = 'user';
        }

        $email = $safe.'@'.$suffix.'.local';
        $i = 1;

        while (User::query()->where('email', $email)->exists()) {
            $email = $safe.'-'.$i.'@'.$suffix.'.local';
            $i++;
        }

        return $email;
    }
}
