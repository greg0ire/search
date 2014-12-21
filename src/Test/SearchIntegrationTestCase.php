<?php

/*
 * This file is part of the RollerworksSearch Component package.
 *
 * (c) 2012-2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Test;

use Rollerworks\Component\Search\Extension\Core\CoreExtension;
use Rollerworks\Component\Search\FieldRegistry;
use Rollerworks\Component\Search\FieldSetBuilder;
use Rollerworks\Component\Search\ResolvedFieldTypeFactory;
use Rollerworks\Component\Search\SearchFactory;
use Rollerworks\Component\Search\ValuesBag;

abstract class SearchIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchFactory
     */
    protected $factory;

    protected function setUp()
    {
        $resolvedTypeFactory = new ResolvedFieldTypeFactory();

        $extensions = array(new CoreExtension());
        $extensions = array_merge($extensions, $this->getExtensions());

        $typesRegistry = new FieldRegistry($extensions, $resolvedTypeFactory);
        $this->factory = new SearchFactory($typesRegistry, $resolvedTypeFactory, null);
    }

    protected function getExtensions()
    {
        return array();
    }

    protected function getFieldSet($build = true)
    {
        $fieldSet = new FieldSetBuilder('test', $this->factory);
        $fieldSet->add($this->factory->createField('id', 'integer')->setAcceptRange(true));
        $fieldSet->add('name', 'text');

        return $build ? $fieldSet->getFieldSet() : $fieldSet;
    }

    protected function assertValueBagsEqual(ValuesBag $expected, ValuesBag $result)
    {
        $expectedArray = array(
            'single' => $expected->getSingleValues(),
            'excluded' => $expected->getExcludedValues(),
            'ranges' => $expected->getRanges(),
            'excludedRanges' => $expected->getExcludedRanges(),
            'compares' => $expected->getComparisons(),
            'matchers' => $expected->getPatternMatchers(),
        );

        // use array_merge to renumber indexes and prevent mismatches
        $resultArray = array(
            'single' => array_merge(array(), $result->getSingleValues()),
            'excluded' => array_merge(array(), $result->getExcludedValues()),
            'ranges' => array_merge(array(), $result->getRanges()),
            'excludedRanges' => array_merge(array(), $result->getExcludedRanges()),
            'compares' => array_merge(array(), $result->getComparisons()),
            'matchers' => array_merge(array(), $result->getPatternMatchers()),
        );

        $this->assertEquals($expectedArray, $resultArray);
    }
}
