<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use MetaFramework\Casts\Datepicker;
use PHPUnit\Framework\TestCase;

class DatepickerTest extends TestCase
{
    public function test_get_formats_valid_storage_date(): void
    {
        $cast = new Datepicker;

        $formattedDate = $cast->get(null, 'date', '2026-02-15', []);

        $this->assertSame('15/02/2026', $formattedDate);
    }

    public function test_get_returns_null_for_invalid_storage_date(): void
    {
        $cast = new Datepicker;

        $formattedDate = $cast->get(null, 'date', '0000-00-00', []);

        $this->assertNull($formattedDate);
    }

    public function test_set_formats_valid_display_date(): void
    {
        $cast = new Datepicker;

        $storageDate = $cast->set(null, 'date', '15/02/2026', []);

        $this->assertSame('2026-02-15', $storageDate);
    }

    public function test_set_returns_null_for_invalid_display_date(): void
    {
        $cast = new Datepicker;

        $storageDate = $cast->set(null, 'date', '31/02/2026', []);

        $this->assertNull($storageDate);
    }
}
