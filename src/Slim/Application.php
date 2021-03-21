<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim;

use UCRM\HTTP\Slim\Middleware\Authentication\AuthenticationHandler;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\Authenticator;
use UCRM\HTTP\Slim\Middleware\Routing\QueryStringRouter;
use UCRM\HTTP\Slim\Services\Service;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Extends the default {@see App}, but also automatically creates a PSR-7 {@see ResponseFactory} when none is provided.
 *
 * @package UCRM\HTTP\Slim\Controllers
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies, Inc.
 */
class Application extends App
{

    /**
     * @inheritDoc
     */
    public function __construct(
        ?ResponseFactoryInterface $responseFactory = null,
        ?ContainerInterface $container = null,
        ?CallableResolverInterface $callableResolver = null,
        ?RouteCollectorInterface $routeCollector = null,
        ?RouteResolverInterface $routeResolver = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null)
    {
        parent::__construct(
            $responseFactory ?? new ResponseFactory(),
            $container,
            $callableResolver,
            $routeCollector,
            $routeResolver,
            $middlewareDispatcher
        );
    }

    /**
     * @var QueryStringRouter|null
     */
    protected $queryStringRoutingMiddleware = null;

    /**
     * Simplifies the addition of our customer {@see QueryStringRouter} Middleware.
     *
     * @param string $defaultRoute The default route to return when none is specified.
     * @param array $rewriteRules An array of optional RegEx rewrite rules to use prior to inspecting the route.
     *
     * @return QueryStringRouter
     */
    public function useQueryStringRouter(string $defaultRoute = "/", array $rewriteRules = []): QueryStringRouter
    {
        // Prevent additional calls of this method from doing anything, as they will cause problems with routing!
        if(!$this->queryStringRoutingMiddleware)
        {
            $router = new QueryStringRouter($defaultRoute, $rewriteRules);
            $this->addMiddleware($this->queryStringRoutingMiddleware = $router);
            return $router;

        }
        else
        {
            return $this->queryStringRoutingMiddleware;

        }

    }

    /**
     * Adds an optional (application-level) {@see Authenticator}.
     *
     * _NOTE: Due to the way middleware operates in Slim, if this method is called multiple times, the first occurrence
     * takes precedence._
     *
     * @param Authenticator $authenticator The {@see Authenticator} to add to the application.
     *
     * @return Authenticator Returns the added {@see Authenticator} to accommodate method chaining.
     */
    public function setDefaultAuthenticator(Authenticator $authenticator): Authenticator
    {
        $this->addMiddleware($authenticator);
        return $authenticator;

    }



    /**
     * Simplifies the addition of {@see Service}s.
     *
     * @param Service $service A {@see Service} to add to the application.
     *
     * @return RouteGroupInterface Returns the added {@see Service} to accommodate method chaining.
     */
    public function addService(Service $service): RouteGroupInterface
    {
        return $service($this);
    }

}
