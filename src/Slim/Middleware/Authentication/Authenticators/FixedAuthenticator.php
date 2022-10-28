<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Middleware\Authentication\Authenticators;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class FixedAuthenticator
 *
 * @package UCRM\HTTP\Slim\Middleware\Authentication\Authenticators
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
class FixedAuthenticator extends Authenticator
{
    /**
     * @var bool
     */
    protected $fixed;

    /**
     * @param bool $fixed
     */
    public function __construct(bool $fixed)
    {
        $this->fixed = $fixed;
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $request = $request
            ->withAttribute("authenticator", get_class($this))
            ->withAttribute("authenticated", $this->fixed);

        return $handler->handle($request);

    }

}
