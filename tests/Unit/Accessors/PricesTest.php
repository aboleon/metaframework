<?php

declare(strict_types=1);

namespace Tests\Unit\Accessors;

use MetaFramework\Accessors\Prices;
use PHPUnit\Framework\TestCase;

class PricesTest extends TestCase
{
    public function test_readable_format_keeps_existing_truncation_when_round_is_disabled(): void
    {
        $formattedPrice = Prices::readableFormat(12.5, showDecimals: false);

        $this->assertSame('12 €', $formattedPrice);
    }

    public function test_readable_format_rounds_up_when_decimal_part_is_greater_than_or_equal_to_fifty(): void
    {
        $formattedPrice = Prices::readableFormat(12.5, showDecimals: false, round: true);

        $this->assertSame('13 €', $formattedPrice);
    }

    public function test_readable_format_rounds_down_when_decimal_part_is_below_fifty(): void
    {
        $formattedPrice = Prices::readableFormat(12.49, showDecimals: false, round: true);

        $this->assertSame('12 €', $formattedPrice);
    }
}
