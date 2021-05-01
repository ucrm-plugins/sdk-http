<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim;

use Slim\App;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Views\TwigMiddleware;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\Authenticator;
use UCRM\HTTP\Slim\Middleware\Handlers\MethodNotAllowedHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\NotFoundHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\UnauthorizedHandler;
use UCRM\HTTP\Slim\Middleware\Routing\QueryStringRouter;
use UCRM\HTTP\Slim\Services\Service;
use UCRM\HTTP\Twig\Extensions\QueryStringRouterExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\Twig;

/**
 * Class DefaultApp
 *
 * @package UCRM\HTTP\Slim
 *
 * @author Ryan Spaeth
 * @copyright 2020 - Spaeth Technologies, Inc.
 */
class Application extends App
{

    public static function fromApp (App $app): Application
    {
        return new Application(
            $app->responseFactory,
            $app->container,
            $app->callableResolver,
            $app->routeCollector,
            $app->routeResolver,
            $app->middlewareDispatcher
        );
    }

    /**
     * DefaultApp constructor.
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param ContainerInterface|null $container
     * @param CallableResolverInterface|null $callableResolver
     * @param RouteCollectorInterface|null $routeCollector
     * @param RouteResolverInterface|null $routeResolver
     * @param MiddlewareDispatcherInterface|null $middlewareDispatcher
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ?ContainerInterface $container,
        ?CallableResolverInterface $callableResolver = null,
        ?RouteCollectorInterface $routeCollector = null,
        ?RouteResolverInterface $routeResolver = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null)
    {
        parent::__construct(
            $responseFactory,
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


    /**
     * Adds and configures the Twig middleware.
     *
     * @param array $paths
     * @param array $options
     * @param bool $debug
     *
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function useTwigTemplateEngine(array $paths = [ "./views/" ], array $options = [], bool $debug = false)
    {
        // Use our customized Twig instance for template rendering, using the default name "view".
        $self = $this;

        $this->getContainer()->set("view", function (ContainerInterface $container) use ($self, $paths, $options, $debug)
        {
            $twig = Twig::create($paths, $options);
            //$twig->getEnvironment()->addGlobal("home", "/index.php");

            $twig->addExtension(new QueryStringRouterExtension($this, $_SERVER["SCRIPT_NAME"], [], $debug));

            //QueryStringRouterExtension::addGlobal("user", "Ryan", "ucrm");



            return $twig;

        });

        // Add and configure the Slim/Twig middleware.
        $this->addMiddleware(TwigMiddleware::createFromContainer($this)); //, "view"));


    }

    /**
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     *
     * @return ErrorMiddleware
     */
    public function addDefaultErrorHandlers(bool $displayErrorDetails, bool $logErrors = true,
        bool $logErrorDetails = true): ErrorMiddleware
    {
        /**
         * Add Error Handling Middleware
         *
         * @param bool $displayErrorDetails Should be set to false in production
         * @param bool $logErrors Parameter is passed to the default ErrorHandler
         * @param bool $logErrorDetails Display error details in error log which can be replaced by any callable.
         * NOTE: This middleware should be added last, as it will not handle any errors for any middleware after it!
         */
        $errorMiddleware = $this->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

        // Add our own HTTP 401 Unauthorized handler.
        $errorMiddleware->setErrorHandler(HttpUnauthorizedException::class, new UnauthorizedHandler($this));

        // Add our own HTTP 404 Not Found handler.
        $errorMiddleware->setErrorHandler(HttpNotFoundException::class, new NotFoundHandler($this));

        // Add our own HTTP 405 Method Not Allowed handler.
        $errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, new MethodNotAllowedHandler($this));

        return $errorMiddleware;

    }


    public function addTemplateGlobal(string $name, $value, string $namespace = "app")
    {
        QueryStringRouterExtension::addGlobal($name, $value, $namespace);
    }

    /**
     * A convenience function that simply requires the specified file and then executes it as an IIFE, passing the
     * current {@see Application} and any optional arguments.
     *
     * @param string $file              The path of the file to require.
     * @param mixed ...$args            Any optional arguments to pass along to the call.
     *
     * @return $this                    The current {@see Application}.
     *
     * @noinspection PhpIncludeInspection
     */
    public function require(string $file, ...$args): self
    {
        $func = require $file;
        $func($this, $args);

        return $this;
    }



}

