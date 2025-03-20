<?php

namespace App\MailTemplates;

use App\MailTemplates\Groups\Event;
use App\MailTemplates\Groups\Group;
use App\MailTemplates\Groups\Manager;
use App\MailTemplates\Groups\Participant;
use App\MailTemplates\Groups\Service;

class Config
{

    public static function activeGroups(): array
    {
        return [
            Event::class,
            Group::class,
            Participant::class,
            Manager::class
        ];
    }

}
