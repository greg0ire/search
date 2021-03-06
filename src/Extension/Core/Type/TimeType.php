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

namespace Rollerworks\Component\Search\Extension\Core\Type;

use Rollerworks\Component\Search\AbstractFieldType;
use Rollerworks\Component\Search\Exception\InvalidConfigurationException;
use Rollerworks\Component\Search\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Rollerworks\Component\Search\FieldConfigInterface;
use Rollerworks\Component\Search\SearchFieldView;
use Rollerworks\Component\Search\Value\Compare;
use Rollerworks\Component\Search\Value\Range;
use Rollerworks\Component\Search\ValueComparisonInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class TimeType extends AbstractFieldType
{
    /**
     * @var ValueComparisonInterface
     */
    private $valueComparison;

    /**
     * Constructor.
     *
     * @param ValueComparisonInterface $valueComparison
     */
    public function __construct(ValueComparisonInterface $valueComparison)
    {
        $this->valueComparison = $valueComparison;
    }

    /**
     * {@inheritdoc}
     */
    public function buildType(FieldConfigInterface $config, array $options)
    {
        $format = 'H';

        if ($options['with_seconds'] && !$options['with_minutes']) {
            throw new InvalidConfigurationException('You can not disable minutes if you have enabled seconds.');
        }

        if ($options['with_minutes']) {
            $format .= ':i';
        }

        if ($options['with_seconds']) {
            $format .= ':s';
        }

        $config->setValueTypeSupport(Range::class, true);
        $config->setValueTypeSupport(Compare::class, true);
        $config->setValueComparison($this->valueComparison);

        $config->setViewTransformer(
            new DateTimeToStringTransformer(
                'UTC',
                'UTC',
                $format
            )
        );

        $config->setNormTransformer(
            new DateTimeToStringTransformer(
                'UTC',
                'UTC',
                $format
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(SearchFieldView $view, FieldConfigInterface $config, array $options)
    {
        $pattern = 'H';

        if ($options['with_minutes']) {
            $pattern .= ':i';
        }

        if ($options['with_seconds']) {
            $pattern .= ':s';
        }

        $view->vars['pattern'] = $pattern;
        $view->vars['with_minutes'] = $options['with_minutes'];
        $view->vars['with_seconds'] = $options['with_seconds'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'with_minutes' => true,
                'with_seconds' => false,
            ]
        );
    }
}
