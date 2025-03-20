<?php


namespace App\Traits;

use Illuminate\Support\Collection;

trait Users
{
    use \MetaFramework\Traits\Users;


    public function adminUsers(): Collection
    {
        return collect($this->userTypes())->whereIn('profile', ['admin', 'dev']);
    }
    public function devUsers(): Collection
    {
        return collect($this->userTypes())->whereIn('profile', ['dev']);
    }

    public function adminContact(): array
    {
        return collect($this->userTypes())->where('profile', 'admin')->first();
    }

    public function publicUsers(): Collection
    {
        return collect($this->userTypes())->where('profile', 'public');
    }

    public function backOfficeUsers(): Collection
    {
        return collect($this->userTypes())->where('subgroup', '!=', 'public');
    }

    public function userTypes(): array
    {
        return config('mfw-users');
    }

    public function names(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function fullName(): string
    {
        return ucfirst(strtolower($this->last_name)) . ' ' . ucfirst(strtolower($this->first_name));
    }

    public function belongsToSubgroup(string|array $group): bool
    {
        return $this->userTypeParser('subgroup', $group);
    }

    public function belongsToProfile(string|array $profile): bool
    {
        return $this->userTypeParser('profile', $profile);
    }
}
