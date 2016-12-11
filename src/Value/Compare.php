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

namespace Rollerworks\Component\Search\Value;

/**
 * Compare value structure.
 */
final class Compare implements RequiresComparatorValueHolder
{
    private $operator;
    private $value;

    const OPERATORS = ['>=', '<=', '<>', '<', '>'];

    /**
     * Constructor.
     *
     * @param mixed  $value
     * @param string $operator
     */
    public function __construct($value, $operator)
    {
        if (!in_array($operator, self::OPERATORS, true)) {
            throw new \InvalidArgumentException(
                sprintf('Unknown operator "%s".', $operator)
            );
        }

        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * Gets the comparison operator.
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
