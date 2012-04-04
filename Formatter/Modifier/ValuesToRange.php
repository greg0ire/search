<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\RecordFilterBundle\Formatter\Modifier;

use Rollerworks\RecordFilterBundle\Formatter\FormatterInterface;
use Rollerworks\RecordFilterBundle\Formatter\ValuesToRangeInterface;
use Rollerworks\RecordFilterBundle\Formatter\FilterTypeInterface;
use Rollerworks\RecordFilterBundle\Formatter\FilterConfig;
use Rollerworks\RecordFilterBundle\Struct\Range;
use Rollerworks\RecordFilterBundle\Struct\Value;
use Rollerworks\RecordFilterBundle\FilterStruct;

/**
 * Converts a connected-list of values to ranges.
 *
 * 1,2,3,4,5 is converted to 1-5.
 * 1,2,3,4,5,7,9,11,12,13 is converted to 1-5,7,9,11-13.
 *
 * For this to work properly the filter-type must implement the ValuesToRangeInterface
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class ValuesToRange implements PostModifierInterface
{
    /**
     * {@inheritdoc}
     */
    protected $messages = array();

    /**
     * @var array
     */
    protected $removeIndexes = array();

    /**
     * @var FilterStruct
     */
    protected $filterStruct;

    /**
     * {@inheritdoc}
     */
    public function getModifierName()
    {
        return 'listToRange';
    }

    /**
     * Add an new message to the list
     *
     * @param string  $transMessage
     * @param array   $params
     */
    protected function addMessage($transMessage, $params = array())
    {
        $this->messages[] = array($transMessage, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function modFilters(FormatterInterface $formatter, FilterConfig $filterConfig, FilterStruct $filterStruct, $groupIndex)
    {
        $this->messages = array();

        if (!$filterConfig->hasType() || !$filterConfig->getType() instanceof ValuesToRangeInterface || (!$filterStruct->hasSingleValues() && !$filterStruct->hasExcludes())) {
            return true;
        }

        $this->removeIndexes = array();
        $this->filterStruct  = $filterStruct;

        /** @var ValuesToRangeInterface $type */
        $type = $filterConfig->getType();

        if ($filterStruct->hasSingleValues()) {
            $values = $filterStruct->getSingleValues();
            uasort($values, array(&$type, 'sortValuesList'));

            $this->listToRanges($values, $type);
        }

        if ($filterStruct->hasExcludes()) {
            $excludes = $filterStruct->getExcludes();
            uasort($excludes, array(&$type, 'sortValuesList'));

            $this->listToRanges($excludes, $type, true);
        }

        return $this->removeIndexes;
    }

    /**
     * Converts a list of values to ranges.
     *
     * @param Value[]                 $values
     * @param ValuesToRangeInterface  $type
     * @param bool                    $exclude
     */
    protected function listToRanges($values, ValuesToRangeInterface $type, $exclude = false)
    {
        $prevIndex = null;
        $prevValue = null;

        $rangeLower = null;
        $rangeUpper = null;

        $valuesCount = count($values);
        $curCount    = 0;

        /** @var \Rollerworks\RecordFilterBundle\Struct\Value $value */
        foreach ($values as $valIndex => $value) {
            $curCount++;

            if (null === $prevValue) {
                $prevIndex = $valIndex;
                $prevValue = $value;

                continue;
            }

            $increasedValue = $type->getHigherValue($prevValue->getValue());

            if ($value->getValue() === $increasedValue) {
                if (null === $rangeLower) {
                    $rangeLower = $prevValue;
                }

                $rangeUpper = $value;
            }

            if (null !== $rangeUpper) {
                $this->unsetVal($prevIndex, $exclude);

                if ($value->getValue() !== $increasedValue || $curCount == $valuesCount) {
                    $range = new Range($rangeLower->getValue(), $rangeUpper->getValue(), $rangeLower->getOriginalValue(), $rangeUpper->getOriginalValue());

                    if ($exclude) {
                        $this->filterStruct->addExcludedRange($range);
                    }
                    else {
                        $this->filterStruct->addRange($range);
                    }

                    $this->unsetVal($prevIndex, $exclude);

                    if ($value->getValue() === $increasedValue && $curCount == $valuesCount) {
                        $this->unsetVal($valIndex, $exclude);
                    }

                    $rangeLower = $rangeUpper = null;
                }

                $prevIndex = $valIndex;
                $prevValue = $value;
            }
        }
    }

    /**
     * Remove an single-value
     *
     * @param integer  $index
     * @param bool     $exclude
     */
    protected function unsetVal($index, $exclude = false)
    {
        if ($exclude) {
            $this->filterStruct->removeExclude($index);
        }
        else {
            $this->filterStruct->removeSingleValue($index);
        }

        $this->removeIndexes[] = $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->messages;
    }
}