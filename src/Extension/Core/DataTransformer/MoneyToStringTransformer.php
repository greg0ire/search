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

namespace Rollerworks\Component\Search\Extension\Core\DataTransformer;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;
use Rollerworks\Component\Search\DataTransformerInterface;
use Rollerworks\Component\Search\Exception\TransformationFailedException;
use Rollerworks\Component\Search\Extension\Core\Model\MoneyValue;

/**
 * Transforms between a normalized format and a money string.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class MoneyToStringTransformer implements DataTransformerInterface
{
    private $defaultCurrency;
    private $moneyParser;
    private $formatter;

    /**
     * Constructor.
     *
     * @param string $defaultCurrency
     */
    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * Transforms a normalized format into a localized money string.
     *
     * @param MoneyValue $value Normalized number
     *
     * @throws TransformationFailedException If the given value is not numeric or
     *                                       if the value can not be transformed
     *
     * @return string Normalized money string
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof MoneyValue) {
            throw new TransformationFailedException('Expected a MoneyValue object.');
        }

        if (!$this->formatter) {
            $this->formatter = new DecimalMoneyFormatter(new ISOCurrencies());
        }

        if (!$value->withCurrency) {
            return $this->formatter->format($value->value);
        }

        return ((string) $value->value->getCurrency()).' '.$this->formatter->format($value->value);
    }

    /**
     * Transforms a localized money string into a normalized format.
     *
     * @param string $value Localized money string
     *
     * @throws TransformationFailedException If the given value is not a string
     *                                       or if the value can not be transformed
     *
     * @return MoneyValue Normalized number
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string value.');
        }

        if ('' === $value) {
            return null;
        }

        $withCurrency = true;
        $result = $value;

        if (false !== strpos($value, ' ')) {
            list($currency, $result) = explode(' ', $value, 2);

            if (strlen($currency) !== 3) {
                throw new TransformationFailedException(
                    sprintf('Value does not contain a valid 3 character currency code, got "%s".', $currency)
                );
            }
        } else {
            $withCurrency = false;
            $currency = $this->defaultCurrency;
        }

        if (!$this->moneyParser) {
            $this->moneyParser = new DecimalMoneyParser(new ISOCurrencies());
        }

        try {
            return new MoneyValue($this->moneyParser->parse($result, $currency), $withCurrency);
        } catch (\Money\Exception $e) {
            throw new TransformationFailedException(
                sprintf($e->getMessage(), 0, $e)
            );
        }
    }
}
