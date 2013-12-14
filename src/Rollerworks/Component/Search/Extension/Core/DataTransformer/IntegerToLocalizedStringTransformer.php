<?php

/*
 * This file is part of the Rollerworks Search Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Component\Search\Extension\Core\DataTransformer;

/**
 * Transforms between an integer and a localized number with grouping
 * (each thousand) and comma separators.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IntegerToLocalizedStringTransformer extends NumberToLocalizedStringTransformer
{
    /**
     * Constructs a transformer.
     *
     * @param integer $precision    Unused.
     * @param Boolean $grouping     Whether thousands should be grouped.
     * @param integer $roundingMode One of the ROUND_ constants in this class.
     */
    public function __construct($precision = null, $grouping = null, $roundingMode = self::ROUND_DOWN)
    {
        if (null === $roundingMode) {
            $roundingMode = self::ROUND_DOWN;
        }

        parent::__construct(0, $grouping, $roundingMode);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        $result = parent::reverseTransform($value);

        return null !== $result ? (int) $result : null;
    }
}