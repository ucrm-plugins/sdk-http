<?php
declare(strict_types=1);

namespace UCRM\HTTP\Twig\TokenParsers;

use UCRM\HTTP\Twig\Nodes\SwitchNode;
use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class SwitchTokenParser that parses {% switch %}{% case %}{% default %}{% endswitch %} tags.
 * Based on the rejected Twig pull request: https://github.com/fabpot/Twig/pull/185
 *
 * @package UCRM\HTTP\Twig
 * @final
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
final class SwitchTokenParser extends AbstractTokenParser
{
    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return "switch";

    }

    /**
     * @inheritdoc
     *
     * @noinspection PhpUnused
     */
    public function parse(Token $token)
    {
        $line = $token->getLine();
        $stream = $this->parser->getStream();

        $nodes = [
            "value" => $this->parser->getExpressionParser()->parseExpression(),
        ];

        $stream->expect(Token::BLOCK_END_TYPE);

        // There can be some whitespace between the {% switch %} and first {% case %} tag.
        while ($stream->getCurrent()->getType() == Token::TEXT_TYPE && trim($stream->getCurrent()->getValue()) === "")
        {
            $stream->next();
        }

        $stream->expect(Token::BLOCK_START_TYPE);

        $expressionParser = $this->parser->getExpressionParser();
        $cases = [];
        $end = false;

        while (!$end)
        {
            $next = $stream->next();

            switch ($next->getValue())
            {
                case "case":
                    $values = [];
                    while (true)
                    {
                        $values[] = $expressionParser->parsePrimaryExpression();

                        // Multiple allowed values?
                        if ($stream->test(Token::OPERATOR_TYPE, "or"))
                            $stream->next();
                        else
                            break;
                    }
                    $stream->expect(Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse([$this, "decideIfFork"]);
                    $cases[] = new Node([
                        "values" => new Node($values),
                        "body" => $body
                    ]);
                    break;
                case "default":
                    $stream->expect(Token::BLOCK_END_TYPE);
                    $nodes["default"] = $this->parser->subparse([$this, "decideIfEnd"]);
                    break;
                case "endswitch":
                    $end = true;
                    break;
                default:
                    throw new SyntaxError(
                        "Unexpected end of template. Twig was looking for the following tags: {% case %}, " .
                        "{% default %}, or {% endswitch %} to close the {% switch %} block started at line $line",
                        -1
                    );
            }
        }

        $nodes['cases'] = new Node($cases);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new SwitchNode($nodes, [], $line, $this->getTag());

    }

    /**
     * @param Token $token
     *
     * @return bool
     *
     * @noinspection PhpUnused
     */
    public function decideIfFork(Token $token): bool
    {
        return $token->test(["case", "default", "endswitch"]);

    }

    /**
     * @param Token $token
     *
     * @return bool
     *
     * @noinspection PhpUnused
     */
    public function decideIfEnd(Token $token): bool
    {
        return $token->test(["endswitch"]);

    }

}
