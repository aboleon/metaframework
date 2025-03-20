<?php

namespace App\Actions\Front;

use App\Models\EventContact;
use App\Models\EventContactToken;
use Illuminate\Support\Str;

class AutoConnectHelper
{
    public static function generateAutoConnectUrlForEventContact(EventContact $ec): string
    {
        $token = EventContactToken::create([
            'event_contact_id' => $ec->id,
        ]);
        return route('front.connect-by-token', ['token' => $token->token]);
    }
}
