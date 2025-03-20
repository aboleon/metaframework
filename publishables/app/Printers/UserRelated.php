<?php

namespace App\Printers;

use App\Interfaces\CreatorInterface;

class UserRelated
{
    public static function creator(CreatorInterface $model): string
    {
        return $model->getCreator?->names();
    }

}
