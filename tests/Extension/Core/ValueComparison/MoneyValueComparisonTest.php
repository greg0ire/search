<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Tests\Extension\Core\ValueComparison;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Rollerworks\Component\Search\Extension\Core\Model\MoneyValue;
use Rollerworks\Component\Search\Extension\Core\ValueComparison\MoneyValueComparison;

final class MoneyValueComparisonTest extends TestCase
{
    /** @var MoneyValueComparison */
    private $comparison;

    protected function setUp()
    {
        $this->comparison = new MoneyValueComparison();
    }

    /** @test */
    public function it_returns_true_equal()
    {
        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::EUR(1200));

        self::assertTrue($this->comparison->isEqual($value1, $value2, []));

        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::EUR(1200), false);

        self::assertTrue($this->comparison->isEqual($value1, $value2, []));
    }

    /** @test */
    public function it_returns_false_when_not_equal()
    {
        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::EUR(1215));

        self::assertFalse($this->comparison->isEqual($value1, $value2, []));

        // Compare with same amount but different currency
        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::USD(1200));

        self::assertFalse($this->comparison->isEqual($value1, $value2, []));
    }

    /** @test */
    public function it_returns_true_when_first_value_is_higher()
    {
        $value1 = new MoneyValue(Money::EUR(1500));
        $value2 = new MoneyValue(Money::EUR(1200));

        self::assertTrue($this->comparison->isHigher($value1, $value2, []));

        $value1 = new MoneyValue(Money::EUR(1210));
        $value2 = new MoneyValue(Money::EUR(1200));

        self::assertTrue($this->comparison->isHigher($value1, $value2, []));
    }

    /** @test */
    public function it_returns_true_when_first_value_is_lower()
    {
        $value1 = new MoneyValue(Money::EUR(1000));
        $value2 = new MoneyValue(Money::EUR(1200));

        self::assertTrue($this->comparison->isLower($value1, $value2, []));

        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::EUR(1210));

        self::assertTrue($this->comparison->isLower($value1, $value2, []));
    }

    /** @test */
    public function it_returns_false_when_first_value_is_not_higher()
    {
        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::EUR(1500));

        self::assertFalse($this->comparison->isHigher($value1, $value2, []));

        // Diff currency.
        $value1 = new MoneyValue(Money::EUR(1210));
        $value2 = new MoneyValue(Money::USD(1200));

        self::assertFalse($this->comparison->isHigher($value1, $value2, []));
    }

    /** @test */
    public function it_returns_false_when_first_value_is_not_lower()
    {
        $value1 = new MoneyValue(Money::EUR(1200));
        $value2 = new MoneyValue(Money::EUR(1000));

        self::assertFalse($this->comparison->isLower($value1, $value2, []));

        $value1 = new MoneyValue(Money::EUR(1000));
        $value2 = new MoneyValue(Money::USD(1200));

        self::assertFalse($this->comparison->isLower($value1, $value2, []));
    }

    /** @test */
    public function it_increments_value_by_amount()
    {
        $value = new MoneyValue(Money::EUR(1210));
        $valueBak = clone $value;

        self::assertEquals(
            new MoneyValue(Money::EUR(1300)),
            $this->comparison->getIncrementedValue($value, ['increase_by' => 'amount'])
        );

        self::assertEquals(
            new MoneyValue(Money::EUR(1400)),
            $this->comparison->getIncrementedValue($value, ['increase_by' => 'amount'], 2)
        );

        // Check original value is not changed.
        self::assertEquals($valueBak, $value);
    }

    /** @test */
    public function it_increments_value_by_cents()
    {
        $value = new MoneyValue(Money::EUR(1210));
        $valueBak = clone $value;

        self::assertEquals(
            new MoneyValue(Money::EUR(1211)),
            $this->comparison->getIncrementedValue($value, [])
        );

        self::assertEquals(
            new MoneyValue(Money::EUR(1212)),
            $this->comparison->getIncrementedValue($value, [], 2)
        );

        // Check original value is not changed.
        self::assertEquals($valueBak, $value);
    }
}
