<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\RecordFilterBundle\Type;

use Rollerworks\RecordFilterBundle\Type\ValueMatcherInterface;

/**
 * Time Formatter-validation type
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class DateTime extends Time implements ValueMatcherInterface
{
    /**
     * Is the time-part optional
     *
     * @var boolean
     */
    protected $timeOptional = false;

    /**
     * Constructor
     *
     * @param boolean $time_optional
     */
    public function __construct($time_optional = false)
    {
        $this->timeOptional = $time_optional;
    }

    /**
     * Sanitize the input string to an normal useful value
     *
     * @param string $input
     * @return string
     */
    public function sanitizeString($input)
    {
        return DateTimeHelper::dateToISO($input);
    }

    /**
     * Returns whether the input value is legally formatted
     *
     * @param string $input
     * @param string $message
     * @return boolean
     */
    public function validateValue($input, &$message = null)
    {
        $message = 'This value is not an valid date with ' . ($this->timeOptional ? 'optional ' : '') . 'time';

        return DateTimeHelper::isDate($input, ($this->timeOptional ? 1 : true));
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRegex()
    {
        return '(?:\d{4}[-/. ]\d{1,2}[-/. ]\d{1,2}|\d{1,2}[-/. ]\d{1,2}[-/. ]\d{4}(?:(?:[T]|\s+)\d{1,2}[:.]\d{2}(?:[:.]\d{2})?(?:\s+[ap]m|(?:[+-]\d{1,2}(?:[:.]?\d{1,2})?))?)' . ($this->timeOptional ? '?' : '') .')';
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function supportsJs()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getHigherValue($input)
    {
        $date = new \DateTime($input);

        if (preg_match('#\d{1,2}:\d{1,2}:\d{1,2}([+-]\d{1,2}([:.]?\d{1,2})?)?$#', $input)) {
            $date->modify('+1 second');
        }
        else {
            $date->modify('+1 minute');
        }

        return $date->format('Y-m-d H:i:s');
    }
}