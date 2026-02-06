<?php

declare(strict_types=1);

/*
The MIT License (MIT)

Copyright (c) Spatie bvba info@spatie.be
https://github.com/spatie/laravel-translatable (as per version 6.3)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 */

namespace MetaFramework\Polyglote\Exceptions;

use Exception;

/**
 * @deprecated Catch Spatie\Translatable\Exceptions\AttributeIsNotTranslatable in new code.
 */
class AttributeIsNotTranslatable extends Exception
{
    public static function make(string $key, $model): static
    {
        $translatableAttributes = implode(', ', $model->getTranslatableAttributes());

        return new static("Cannot translate attribute `{$key}` as it's not one of the translatable attributes: `$translatableAttributes`");
    }
}
