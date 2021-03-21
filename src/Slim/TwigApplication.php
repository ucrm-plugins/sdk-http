<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim;

use UCRM\HTTP\Slim\Middleware\Handlers\MethodNotAllowedHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\NotFoundHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\UnauthorizedHandler;
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
class TwigApplication extends Application
{

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

            // Add and configure the Slim/Twig middleware.
            //$self->addMiddleware(TwigMiddleware::create($self, $twig)); //, "view"));

            return $twig;

        });

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

}

