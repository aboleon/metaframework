<?php

namespace App\Accessors;



use MetaFramework\Models\Setting;

class Cached
{

    public static function multilang(): bool
    {
        return cache()->rememberForever('multilang', fn() => config('translatable.multilang'));
    }

    public static function settings(string $key): string
    {
        return cache()->rememberForever($key, fn() => Setting::get($key) ?: Setting::getDefaultValueForField($key));
    }


    /*
        public static function contacts()
        {
            return cache()->rememberForever('contacts', fn() => Meta\Contacts::signature()->value('content'));
        }

        public function socials()
        {
            return cache()->rememberForever('socials', fn() => array_filter(Meta\Socials::signature()->value('content')));
        }

        public function footer()
        {
            return cache()->rememberForever('footer', fn() => Meta\Footer::signature()->with('media')->select('id','abstract','access_key')->first());
        }
    */
}
