<?php

namespace MichelePapucci\TextGenerator\TextFunction;

use MichelePapucci\TextGenerator\Tag\TagReplacer;
use MichelePapucci\TextGenerator\Tag\TagReplacerInterface;

/**
 * Class FilterFunction
 * 'filter' function: allow to filter strings or numeric values through PHP native functions
 *
 * @package Neveldo\TextGenerator\TextFunction
 */
class FilterFunction implements FunctionInterface
{
    /**
     * @var TagReplacerInterface Tag Replacer service
     */
    private $tagReplacer;

    /**
     * @var array array of available filters
     */
    protected $availableFilters = [];

    /**
     * FilterFunction constructor.
     * @param TagReplacerInterface $tagReplacer
     */
    public function __construct(TagReplacerInterface $tagReplacer)
    {
        $this->tagReplacer = $tagReplacer;

        // Init defaultfilters
        $this->availableFilters = [
            'round' => [
                'function' => 'round',
                'minArgs' => 1,
                'maxArgs' => 3
            ],
            'ceil' => [
                'function' => 'ceil',
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'floor' => [
                'function' => 'floor',
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'max' => [
                'function' => 'max',
                'minArgs' => 2
            ],
            'min' => [
                'function' => 'min',
                'minArgs' => 2
            ],
            'rand' => [
                'function' => 'rand',
                'minArgs' => 2,
                'maxArgs' => 2
            ],
            'number' => [
                'function' => function($value, $decimals = 0, $decPoint = '.', $thousands_sep = ',') {
                    if (!is_numeric($value)) {
                        return $value;
                    }

                    return number_format($value, $decimals, $decPoint, $thousands_sep);
                },

                'minArgs' => 1,
                'maxArgs' => 4
            ],

            'lower' => [
                'function' => 'mb_strtolower',
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'upper' => [
                'function' => 'mb_strtoupper',
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'lowerfirst' => [
                'function' => function($value) {
                    return mb_strtolower(mb_substr($value, 0, 1)) . mb_substr($value, 1, mb_strlen($value) - 1);
                },
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'upperfirst' => [
                'function' => function($value) {
                    return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1, mb_strlen($value) - 1);
                },
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'upperwords' => [
                'function' => function($value) {
                    return mb_convert_case($value, MB_CASE_TITLE);
                },
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'trim' => [
                'function' => 'trim',
                'minArgs' => 1,
                'maxArgs' => 1
            ],
            'substring' => [
                'function' => 'mb_substr',
                'minArgs' => 2,
                'maxArgs' => 3
            ],
            'replace' => [
                'function' => 'str_replace',
                'minArgs' => 3,
                'maxArgs' => 3
            ],
            'timestamp' => [
                'function' => function($format, $timestamp = null) {
                    if (isset($timestamp)) {
                        $timestamp = (int) $timestamp;
                    } else {
                        $timestamp = time();
                    }
                    return date($format, $timestamp);
                },
                'minArgs' => 1,
                'maxArgs' => 2
            ],
            'date' => [
                'function' => function($date, $fromFormat, $toFormat) {
                    $datetime = \DateTime::createFromFormat($fromFormat, $date);

                    if ($datetime === false) {
                        throw new \InvalidArgumentException(
                            sprintf("Wrong date format %s for the date %s.", $fromFormat, $date)
                        );
                    }
                    return $datetime->format($toFormat);
                },
                'minArgs' => 3,
                'maxArgs' => 3
            ],
        ];
    }

    /**
     * Handle Filter function
     * @param array $arguments list of arguments where tags have been replaced by their values
     * @param array $originalArguments list of original arguments
     * @return string
     * @throw InvalidArgumentException if the number of arguments is not valid
     */
    public function execute(array $arguments, array $originalArguments)
    {
        if (count($arguments) < 2) {
            throw new \InvalidArgumentException(
                sprintf("FilterFunction expect at least two parameters, %d given.", count($arguments))
            );
        }

        if (!isset($this->availableFilters[$arguments[0]])) {
            throw new \InvalidArgumentException(
                sprintf("The filter %s is not available.", $arguments[0])
            );
        }

        $filterName = $arguments[0];
        $filter = $this->availableFilters[$filterName];
        array_shift($arguments);

        if (count($arguments) < $filter['minArgs']) {
            throw new \InvalidArgumentException(
                sprintf("Filter %s expect at least %d parameters, %d given.", $filterName, $filter['minArgs'], (count($arguments)))
            );
        }

        if ($arguments[0] === $this->tagReplacer->getEmptyTag() || $arguments[0] === '') {
            return $arguments[0];
        }

        if (isset($filter['maxArgs']) && count($arguments) > $filter['maxArgs']) {
            throw new \InvalidArgumentException(
                sprintf("Filter %s expect maximum %d parameters, %d given.", $filterName, $filter['maxArgs'], (count($arguments)))
            );
        }

        if (is_callable($filter['function'])) {
            try {
                return call_user_func_array($filter['function'], $arguments);
            } catch (\Exception $e) {
                return TagReplacer::EMPTY_TAG;
            }
        }

        return TagReplacer::EMPTY_TAG;
    }

    /**
     * Add a filter
     * @param $name filter name
     * @param array $filter filter conf array
     */
    public function addFilter($name, array $filter)
    {
        $this->availableFilters[$name] = $filter;
    }

}