<?php

namespace MichelePapucci\TextGenerator\TextFunction;

use MichelePapucci\TextGenerator\Tag\TagReplacer;
use MichelePapucci\TextGenerator\Tag\TagReplacerInterface;
use MichelePapucci\TextGenerator\ExpressionLanguage\ExpressionLanguage;

/**
 * Class IfFunction
 * 'if' function: handle conditions a return the "then statement" or the "else statement"
 * Depending on the condition evaluation
 * Examples :
 * #if{@val = 5|then statement}
 * #if{@val = 5|then statement|else statement}
 * #if{@val < 5 or %val% > 15|then statement|else statement}
 * #if{@val > 5 and val < 15|then statement|else statement}
 *
 * More information about the syntax for the condition : http://symfony.com/doc/current/components/expression_language/syntax.html
 *
 * @package Neveldo\TextGenerator\TextFunction
 */
class IfFunction implements FunctionInterface
{
    /**
     * @var TagReplacerInterface Tag Replacer service
     */
    private $tagReplacer;

    /**
     * IfFunction constructor.
     * @param TagReplacerInterface $tagReplacer
     */
    public function __construct(TagReplacerInterface $tagReplacer)
    {
        $this->tagReplacer = $tagReplacer;
    }

    /**
     * Handle If function
     * @param array $arguments list of arguments where tags have been replaced by their values
     * @param array $originalArguments list of original arguments
     * @return string
     * @throw InvalidArgumentException if the number of arguments is not valid
     */
    public function execute(array $arguments, array $originalArguments)
    {
        if (count($arguments) !== 2 && count($arguments) !== 3) {
            throw new \InvalidArgumentException(
                sprintf("IfFunction expect exactly two (condition, then statement) or three (condition, then statement, else statement) parameters, %d given.", count($arguments))
            );
        }

        $language = new ExpressionLanguage();

        if ($language->evaluate($this->tagReplacer->sanitizeTagNames($originalArguments[0]), $this->tagReplacer->getTags())) {
            return $arguments[1];
        } else if (isset($arguments[2])) {
            return $arguments[2];
        }
        return TagReplacer::EMPTY_TAG;
    }

}