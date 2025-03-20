<?php

namespace App\Interfaces;

interface UserCustomDataInterface
{
    public function profileData(): array;
    public function mediaSettings(): array;
}
