<?php
declare(strict_types=1);

namespace UCRM\HTTP\Twig\Nodes;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Class SwitchNode
 * Based on the rejected Twig pull request: https://github.com/fabpot/Twig/pull/185
 *
 * @package UCRM\HTTP\Twig
 * @final
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
final class SwitchNode extends Node
{
    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("switch (")
            ->subcompile($this->getNode("value"))
            ->raw(") {\n")
            ->indent();

        foreach ($this->getNode("cases") as $case) {
            /** @var Node $case */
            // The 'body' node may have been removed by Twig if it was an empty text node in a sub-template, outside of
            // any blocks.
            if (!$case->hasNode("body")) {
                continue;
            }

            foreach ($case->getNode("values") as $value) {
                $compiler
                    ->write("case ")
                    ->subcompile($value)
                    ->raw(":\n");
            }

            $compiler
                ->write("{\n")
                ->indent()
                ->subcompile($case->getNode("body"))
                ->write("break;\n")
                ->outdent()
                ->write("}\n");
        }

        if ($this->hasNode("default")) {
            $compiler
                ->write("default:\n")
                ->write("{\n")
                ->indent()
                ->subcompile($this->getNode("default"))
                ->outdent()
                ->write("}\n");
        }

        $compiler
            ->outdent()
            ->write("}\n");

    }

}
