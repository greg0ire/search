<?php

/*
 * This file is part of the Rollerworks Search Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Component\Search\Doctrine\Dbal;

/**
 * ValueConversionInterface provides a value conversion for the SQL generating process.
 *
 * The $hints parameter always receives the following information.
 *
 * * search_field: Rollerworks\Component\Search\FieldConfigInterface
 * * connection: Doctrine\DBAL\Connection
 * * db_type: Doctrine\DBAL\Types\Type
 *
 * Value conversion only:
 * * value_object: The value object currently being processed (SingleValue, Range, Compare, PatternMatch).
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
interface ValueConversionInterface
{
    /**
     * Returns whether the base-conversion of the value is required.
     *
     * The base conversion uses the convertToDatabaseValue()
     * of the database type.
     *
     * The original value is still available
     * with originalValue in the $hints parameter
     *
     * @param mixed $input   Input value
     * @param array $options Options of the Field configuration
     * @param array $hints   Special information for the conversion process (search_field, connection, db_type, value_object)
     *
     * @return boolean
     */
    public function requiresBaseConversion($input, array $options, array $hints);

    /**
     * Returns the converted input.
     *
     * @param mixed $input   Query-parameter reference or real input depending on requiresRealValue()
     * @param array $options Options of the Field configuration
     * @param array $hints   Special information for the conversion process (search_field, connection, db_type, original_value, value_object)
     *
     * @return string
     */
    public function convertValue($input, array $options, array $hints);
}