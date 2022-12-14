<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Services;

use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;
use UCRM\HTTP\Slim\Application;

/**
 * An abstract Service class, from which to extend all other Services.
 *
 * _NOTE: Controllers can only be added directly to an {@see Application} and can not be part of a {@see RouteGroup},
 * as they are a special {@see RouteGroup} themselves._
 *
 * @package UCRM\HTTP\Slim\Services
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
abstract class Service extends RouteCollectorProxy implements RouteCollectorProxyInterface
{
    protected $container;

    protected $logger;

    protected $prefix;

    /**
     * Service constructor.
     *
     * @param Application $app The {@see Application} to which this Service belongs.
     * @param string $prefix An optional {@see RouteGroup} prefix to use for this Service, defaults to "".
     */
    public function __construct(Application $app, string $prefix = "")
    {
        $this->container = $app->getContainer();
        $this->logger = $this->container ? $this->container->get(LoggerInterface::class) : null;

        parent::__construct(
            $app->getResponseFactory(),
            $app->getCallableResolver(),
            $app->getContainer(),
            $app->getRouteCollector(),
            $this->prefix = $prefix ?: ""
        );

    }

    /**
     * @param Application $app The {@see Application} to which this Service belongs.
     *
     * @return RouteGroupInterface Returns a {@see RouteGroup} for method chaining.
     */
    public abstract function __invoke(Application $app): RouteGroupInterface;

}
