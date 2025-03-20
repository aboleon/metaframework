<?php

namespace App\Accessors;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Users
{

    public static function getSearchResults(string $searchTerm = ""): array
    {
        $query = User::select('id', 'first_name', 'last_name')
            ->where('first_name', 'LIKE', '%'.$searchTerm.'%')
            ->orWhere('last_name', 'LIKE', '%'.$searchTerm.'%');
        $users = $query->get();

        return $users->map(function ($user) {
            return [
                'value' => $user->id,
                'text'  => $user->names(),
            ];
        })->toArray();
    }

    public static function guessPreferredLang(User $user): string
    {
        $dicLang = $user->accountProfile?->language?->name;

        return match ($dicLang) {
            'English', 'Anglais' => 'en',
            default => 'fr',
        };
    }

    public static function createBlankTmpUserByEmail(string $email): Account
    {
        return Account::create([
            'first_name' => "Votre prénom",
            'last_name'  => "Votre nom",
            'email'      => $email,
            'password'   => Hash::make("123456789"),
        ]);
    }
}
