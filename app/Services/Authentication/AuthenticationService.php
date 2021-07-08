<?php

namespace App\Services\Authentication;

use App\Models\User;

class AuthenticationService
{

    public function register($name, $email, $password)
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $userWallet = $user->wallet()->create([
            'balance' => 30000,
        ]);

        return $user;
    }
}
