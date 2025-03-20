<?php

namespace App\MailTemplates\Contracts;

interface GroupVariables
{
    public static function variables(): array;
    public static function title(): string;
    public static function icon(): string;
}
