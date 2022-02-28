<?php

namespace MichelePapucci\TextGenerator\TextFunction;

use MichelePapucci\TextGenerator\Tag\TagReplacer;
use MichelePapucci\TextGenerator\Tag\TagReplacerInterface;
use MichelePapucci\TextGenerator\ExpressionLanguage\ExpressionLanguage;

/**
 * Class ExprFunction
 * 'expr' function: handle expressions
 * More information about the syntax for the expressions : http://symfony.com/doc/current/components/expression_language/syntax.html
 *
 * @package Neveldo\TextGenerator\TextFunction
 */
class ExprFunction implements FunctionInterface
{
    /**
     * @var TagReplacerInterface Tag Replacer service
     */
    private $tagReplacer;

    /**
     * ExprFunction constructor.
     * @param TagReplacerInterface $tagReplacer
     */
    public function __construct(TagReplacerInterface $tagReplacer)
    {
        $this->tagReplacer = $tagReplacer;
    }

    /**
     * Handle Expr function
     * @param array $arguments list of arguments where tags have been replaced by their values
     * @param array $originalArguments list of original arguments
     * @return string
     * @throw InvalidArgumentException if the number of arguments is not valid
     */
    public function execute(array $arguments, array $originalArguments)
    {
        if (count($arguments) !== 1) {
            throw new \InvalidArgumentException(
                sprintf("ExprFunction expect exactly one parameter, %d given.", count($arguments))
            );
        }

        try {
            return (new ExpressionLanguage())->evaluate(
                $this->tagReplacer->sanitizeTagNames($originalArguments[0]),
                $this->tagReplacer->getTags()
            );
        } catch (\Exception $e) {
            return TagReplacer::EMPTY_TAG;
        }  catch (\Error $e) {
            return TagReplacer::EMPTY_TAG;
        }
    }

}