<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Middleware\Authentication;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Exception\HttpUnauthorizedException;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\Authenticator;

/**
 * Class AuthenticationHandler
 *
 * @package UCRM\HTTP\Slim\Middleware\Authentication
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
class AuthenticationHandler implements MiddlewareInterface
{
    /**
     * @var App
     */
    protected $app;

    /**
     * AuthenticationHandler constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     *
     * @throws HttpUnauthorizedException
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        // IF there is an "authenticated" attribute contained within the request, then a handler must have passed!
        if($request->getAttribute("authenticated"))
        {
            return $handler->handle($request);
        }
        else
        {
            throw new HttpUnauthorizedException($request);
        }

    }

}
