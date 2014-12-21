<?php

/*
 * This file is part of the RollerworksSearch Component package.
 *
 * (c) 2012-2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Tests\Formatter;

use Rollerworks\Component\Search\Formatter\RangeOptimizer;
use Rollerworks\Component\Search\SearchConditionBuilder;
use Rollerworks\Component\Search\Test\FormatterTestCase;
use Rollerworks\Component\Search\Value\Range;
use Rollerworks\Component\Search\Value\SingleValue;
use Rollerworks\Component\Search\ValuesBag;

final class RangeOptimizerTest extends FormatterTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->formatter = new RangeOptimizer();
    }

    /**
     * @test
     */
    public function it_removes_singleValues_overlapping_in_ranges()
    {
        $condition = SearchConditionBuilder::create($this->fieldSet)
            ->field('id')
                ->addSingleValue(new SingleValue(90))
                ->addSingleValue(new SingleValue(21))
                ->addSingleValue(new SingleValue(15)) // overlapping in ranges[0]
                ->addSingleValue(new SingleValue(65)) // overlapping in ranges[2]
                ->addSingleValue(new SingleValue(40))
                ->addSingleValue(new SingleValue(1)) // this is overlapping, but the range lower-bound is exclusive

                ->addRange(new Range(11, 20))
                ->addRange(new Range(25, 30))
                ->addRange(new Range(50, 70))
                ->addRange(new Range(1, 10, false))
            ->end()
            ->getSearchCondition()
        ;

        $this->formatter->format($condition);
        $valuesGroup = $condition->getValuesGroup();

        $expectedValuesBag = new ValuesBag();
        $expectedValuesBag
            ->addSingleValue(new SingleValue(90))
                ->addSingleValue(new SingleValue(21))
                ->addSingleValue(new SingleValue(40))
                ->addSingleValue(new SingleValue(1)) // this is overlapping, but the range lower-bound is exclusive
                ->addRange(new Range(11, 20))
                ->addRange(new Range(25, 30))
                ->addRange(new Range(50, 70))
                ->addRange(new Range(1, 10, false))
        ;

        $this->assertValueBagsEqual($expectedValuesBag, $valuesGroup->getField('id'));
    }

    /**
     * @test
     */
    public function it_removes_ranges_overlapping_in_ranges()
    {
        $condition = SearchConditionBuilder::create($this->fieldSet)
            ->field('id')
                ->addRange(new Range(1, 10))
                ->addRange(new Range(20, 30))
                ->addRange(new Range(2, 5)) // overlapping in 0
                ->addRange(new Range(3, 7)) // overlapping in 0
                ->addRange(new Range(50, 70))
                ->addRange(new Range(51, 71, true, false))  // overlapping with bounds
                ->addRange(new Range(51, 69)) // overlapping in 4
                ->addRange(new Range(52, 69)) // overlapping in 4
                ->addRange(new Range(51, 71))
                ->addRange(new Range(49, 71, false, false)) // overlapping in 8
                ->addRange(new Range(51, 71, false)) // overlapping but lower-bound is exclusive
            ->end()
            ->getSearchCondition()
        ;

        $this->formatter->format($condition);
        $valuesGroup = $condition->getValuesGroup();

        $expectedValuesBag = new ValuesBag();
        $expectedValuesBag
            ->addRange(new Range(1, 10))
            ->addRange(new Range(20, 30))
            ->addRange(new Range(50, 70))
            ->addRange(new Range(51, 71))
            ->addRange(new Range(51, 71, false)) // overlapping but lower-bound is exclusive
        ;

        $this->assertValueBagsEqual($expectedValuesBag, $valuesGroup->getField('id'));
    }

    /**
     * @test
     */
    public function it_removes_excludedValues_overlapping_in_excludedRanges()
    {
        $condition = SearchConditionBuilder::create($this->fieldSet)
            ->field('id')
                ->addExcludedValue(new SingleValue(90))
                ->addExcludedValue(new SingleValue(21))
                ->addExcludedValue(new SingleValue(15)) // overlapping in ranges[0]
                ->addExcludedValue(new SingleValue(65)) // overlapping in ranges[2]
                ->addExcludedValue(new SingleValue(40))
                ->addExcludedValue(new SingleValue(1)) // this is overlapping, but the range lower-bound is exclusive

                ->addExcludedRange(new Range(11, 20))
                ->addExcludedRange(new Range(25, 30))
                ->addExcludedRange(new Range(50, 70))
                ->addExcludedRange(new Range(1, 10, false))
            ->end()
            ->getSearchCondition()
        ;

        $this->formatter->format($condition);
        $valuesGroup = $condition->getValuesGroup();

        $expectedValuesBag = new ValuesBag();
        $expectedValuesBag
            ->addExcludedValue(new SingleValue(90))
            ->addExcludedValue(new SingleValue(21))
            ->addExcludedValue(new SingleValue(40))
            ->addExcludedValue(new SingleValue(1)) // this is overlapping, but the range lower-bound is exclusive

            ->addExcludedRange(new Range(11, 20))
            ->addExcludedRange(new Range(25, 30))
            ->addExcludedRange(new Range(50, 70))
            ->addExcludedRange(new Range(1, 10, false))
        ;

        $this->assertValueBagsEqual($expectedValuesBag, $valuesGroup->getField('id'));
    }

    /**
     * @test
     */
    public function it_removes_excludedRanges_overlapping_in_excludedRanges()
    {
        $condition = SearchConditionBuilder::create($this->fieldSet)
            ->field('id')
                ->addExcludedRange(new Range(1, 10))
                ->addExcludedRange(new Range(20, 30))
                ->addExcludedRange(new Range(2, 5)) // overlapping in 0
                ->addExcludedRange(new Range(3, 7)) // overlapping in 0
                ->addExcludedRange(new Range(50, 70))
                ->addExcludedRange(new Range(51, 71, true, false)) // overlapping with bounds
                ->addExcludedRange(new Range(51, 69))
                ->addExcludedRange(new Range(52, 69))
                ->addExcludedRange(new Range(51, 71))
                ->addExcludedRange(new Range(49, 71, false, false)) // overlapping in 8
                ->addExcludedRange(new Range(51, 71, false)) // overlapping but lower-bound is exclusive
            ->end()
            ->getSearchCondition()
        ;

        $this->formatter->format($condition);
        $valuesGroup = $condition->getValuesGroup();

        $expectedValuesBag = new ValuesBag();
        $expectedValuesBag
            ->addExcludedRange(new Range(1, 10))
            ->addExcludedRange(new Range(20, 30))
            ->addExcludedRange(new Range(50, 70))
            ->addExcludedRange(new Range(51, 71))
            ->addExcludedRange(new Range(51, 71, false))  // overlapping but lower-bound is exclusive
        ;

        $this->assertValueBagsEqual($expectedValuesBag, $valuesGroup->getField('id'));
    }

    /**
     * @test
     */
    public function it_merges_connected_ranges()
    {
        $condition = SearchConditionBuilder::create($this->fieldSet)
            ->field('id')
                ->addRange(new Range(10, 20))
                ->addRange(new Range(30, 40))
                ->addRange(new Range(20, 25))
                ->addRange(new Range(20, 28, false)) // this should not be changed as the bounds do not equal 1
                ->addRange(new Range(20, 26))
            ->end()
            ->getSearchCondition()
        ;

        $this->formatter->format($condition);
        $valuesGroup = $condition->getValuesGroup();

        $expectedValuesBag = new ValuesBag();
        $expectedValuesBag
            ->addRange(new Range(10, 26))
            ->addRange(new Range(30, 40))
            ->addRange(new Range(20, 28, false)) // this should not be changed as the bounds do not equal 1
        ;

        $this->assertValueBagsEqual($expectedValuesBag, $valuesGroup->getField('id'));
    }

    /**
     * @test
     */
    public function it_merges_connected_excludedRanges()
    {
        $condition = SearchConditionBuilder::create($this->fieldSet)
            ->field('id')
                ->addExcludedRange(new Range(10, 20))
                ->addExcludedRange(new Range(30, 40))
                ->addExcludedRange(new Range(20, 25))
                ->addExcludedRange(new Range(20, 28, false)) // this should not be changed as the bounds do not equal 1
                ->addExcludedRange(new Range(20, 26))
            ->end()
            ->getSearchCondition()
        ;

        $this->formatter->format($condition);
        $valuesGroup = $condition->getValuesGroup();

        $expectedValuesBag = new ValuesBag();
        $expectedValuesBag
            ->addExcludedRange(new Range(10, 26))
            ->addExcludedRange(new Range(30, 40))
            ->addExcludedRange(new Range(20, 28, false)) // this should not be changed as the bounds do not equal 1
        ;

        $this->assertValueBagsEqual($expectedValuesBag, $valuesGroup->getField('id'));
    }
}