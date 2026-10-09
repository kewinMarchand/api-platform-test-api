<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain;

use App\Catalog\Domain\Price;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    public function testRejectsNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Price::fromCents(-1);
    }

    public function testComparesAmounts(): void
    {
        self::assertSame(-1, Price::fromCents(1290)->compareTo(Price::fromCents(1490)));
        self::assertSame(0, Price::fromCents(1490)->compareTo(Price::fromCents(1490)));
    }

    public function testBoundsAreInclusiveAndOptional(): void
    {
        $price = Price::fromCents(2490);

        self::assertTrue($price->isBetween(Price::fromCents(2490), Price::fromCents(2490)));
        self::assertTrue($price->isBetween(null, null));
        self::assertFalse($price->isBetween(Price::fromCents(2500), null));
        self::assertFalse($price->isBetween(null, Price::fromCents(2489)));
    }
}
