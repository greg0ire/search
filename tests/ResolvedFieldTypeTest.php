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

namespace Rollerworks\Component\Search\Tests;

use PHPUnit\Framework\TestCase;
use Rollerworks\Component\Search\AbstractFieldType;
use Rollerworks\Component\Search\AbstractFieldTypeExtension;
use Rollerworks\Component\Search\FieldConfigInterface;
use Rollerworks\Component\Search\FieldTypeExtensionInterface;
use Rollerworks\Component\Search\FieldTypeInterface;
use Rollerworks\Component\Search\ResolvedFieldType;
use Rollerworks\Component\Search\SearchFieldView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ResolvedFieldTypeTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FieldTypeInterface
     */
    private $parentType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FieldTypeInterface
     */
    private $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FieldTypeExtensionInterface
     */
    private $extension1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FieldTypeExtensionInterface
     */
    private $extension2;

    /**
     * @var ResolvedFieldType
     */
    private $parentResolvedType;

    /**
     * @var ResolvedFieldType
     */
    private $resolvedType;

    protected function setUp()
    {
        $this->parentType = $this->getMockFieldType();
        $this->type = $this->getMockFieldType();
        $this->extension1 = $this->getMockFieldTypeExtension();
        $this->extension2 = $this->getMockFieldTypeExtension();
        $this->parentResolvedType = new ResolvedFieldType($this->parentType);
        $this->resolvedType = new ResolvedFieldType(
            $this->type,
            [$this->extension1, $this->extension2],
            $this->parentResolvedType
        );
    }

    /**
     * @test
     */
    public function its_resolved_options_in_correct_order()
    {
        $i = 0;

        $assertIndexAndAddOption = function ($index, $option, $default) use (&$i) {
            return function (OptionsResolver $resolver) use (&$i, $index, $option, $default) {
                self::assertEquals($index, $i, 'Executed at index '.$index);

                ++$i;

                $resolver->setDefaults([$option => $default]);
            };
        };

        // First the default options are generated for the super type
        $this->parentType->expects($this->once())
            ->method('configureOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(0, 'a', 'a_default')));

        // The field type itself
        $this->type->expects($this->once())
            ->method('configureOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(1, 'b', 'b_default')));

        // And its extensions
        $this->extension1->expects($this->once())
            ->method('configureOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(2, 'c', 'c_default')));

        $this->extension2->expects($this->once())
            ->method('configureOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(3, 'd', 'd_default')));

        $givenOptions = ['a' => 'a_custom', 'c' => 'c_custom'];
        $resolvedOptions = ['a' => 'a_custom', 'b' => 'b_default', 'c' => 'c_custom', 'd' => 'd_default'];

        $resolver = $this->resolvedType->getOptionsResolver();

        self::assertEquals($resolvedOptions, $resolver->resolve($givenOptions));
    }

    /**
     * @test
     */
    public function it_creates_a_field()
    {
        $givenOptions = ['a' => 'a_custom', 'c' => 'c_custom'];
        $resolvedOptions = ['a' => 'a_custom', 'b' => 'b_default', 'c' => 'c_custom', 'd' => 'd_default'];
        $optionsResolver = $this->createOptionsResolverMock();

        $this->resolvedType = $this->getMockBuilder(ResolvedFieldType::class)
            ->setConstructorArgs([$this->type, [$this->extension1, $this->extension2], $this->parentResolvedType])
            ->setMethods(['getOptionsResolver'])
            ->getMock();

        $this->resolvedType->expects($this->once())
            ->method('getOptionsResolver')
            ->will($this->returnValue($optionsResolver));

        $optionsResolver->expects($this->once())
            ->method('resolve')
            ->with($givenOptions)
            ->will($this->returnValue($resolvedOptions));

        $field = $this->resolvedType->createField('name', $givenOptions);

        self::assertSame($this->resolvedType, $field->getType());
        self::assertSame($resolvedOptions, $field->getOptions());
    }

    /**
     * @test
     */
    public function it_builds_the_type()
    {
        $i = 0;

        $assertIndex = function ($index) use (&$i) {
            return function () use (&$i, $index) {
                self::assertEquals($index, $i, 'Executed at index '.$index);

                ++$i;
            };
        };

        $options = ['a' => 'Foo', 'b' => 'Bar'];
        $field = $this->createFieldMock();

        // First the field is built for the super type
        $this->parentType->expects($this->once())
            ->method('buildType')
            ->with($field, $options)
            ->will($this->returnCallback($assertIndex(0)));

        // Then the type itself
        $this->type->expects($this->once())
            ->method('buildType')
            ->with($field, $options)
            ->will($this->returnCallback($assertIndex(1)));

        // Then its extensions
        $this->extension1->expects($this->once())
            ->method('buildType')
            ->with($field, $options)
            ->will($this->returnCallback($assertIndex(2)));

        $this->extension2->expects($this->once())
            ->method('buildType')
            ->with($field, $options)
            ->will($this->returnCallback($assertIndex(3)));

        $this->resolvedType->buildType($field, $options);
    }

    public function testCreateView()
    {
        $field = $this->createFieldMock();
        $view = $this->resolvedType->createFieldView($field);

        self::assertInstanceOf(SearchFieldView::class, $view);
    }

    public function testBuildView()
    {
        $options = ['a' => '1', 'b' => '2'];
        $field = $this->createFieldMock();
        $view = $this->createSearchFieldViewMock();

        $i = 0;

        $assertIndex = function ($index) use (&$i) {
            return function () use (&$i, $index) {
                self::assertEquals($index, $i, 'Executed at index '.$index);

                ++$i;
            };
        };

        // First the super type
        $this->parentType->expects($this->once())
            ->method('buildView')
            ->with($view, $field, $options)
            ->will($this->returnCallback($assertIndex(0)));

        // Then the type itself
        $this->type->expects($this->once())
            ->method('buildView')
            ->with($view, $field, $options)
            ->will($this->returnCallback($assertIndex(1)));

        // Then its extensions
        $this->extension1->expects($this->once())
            ->method('buildView')
            ->with($field, $view)
            ->will($this->returnCallback($assertIndex(2)));

        $this->extension2->expects($this->once())
            ->method('buildView')
            ->with($field, $view)
            ->will($this->returnCallback($assertIndex(3)));

        $this->resolvedType->buildFieldView($view, $field, $options);
    }

    /**
     * @param string $typeClass
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFieldType($typeClass = AbstractFieldType::class)
    {
        return $this->getMockBuilder($typeClass)
            ->setMethods(['configureOptions', 'buildView', 'buildType'])
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFieldTypeExtension()
    {
        return $this->getMockBuilder(AbstractFieldTypeExtension::class)
            ->setMethods(['getExtendedType', 'configureOptions', 'buildView', 'buildType']
        )->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createOptionsResolverMock()
    {
        return $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createFieldMock()
    {
        return $this->getMockBuilder(FieldConfigInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createSearchFieldViewMock()
    {
        return $this->getMockBuilder(SearchFieldView::class)->getMock();
    }
}
