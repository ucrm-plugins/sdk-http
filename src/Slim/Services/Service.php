<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Services;

use UCRM\HTTP\Slim\Application;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

/**
 * An abstract Service class, from which to extend all other Controllers.
 *
 * _NOTE: Controllers can only be added directly to an {@see Application} and can not be part of a {@see RouteGroup},
 * as they are special {@see RouteGroup}s themselves._
 *
 * @package UCRM\HTTP\Slim\Services
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies, Inc.
 */
abstract class Service extends RouteCollectorProxy implements RouteCollectorProxyInterface
{
    /**
     * Service constructor.
     *
     * @param Application $app The {@see Application} to which this Service belongs.
     * @param string $prefix An optional {@see RouteGroup} prefix to use for this Service, defaults to "".
     */
    public function __construct(Application $app, string $prefix = "")
    {
        parent::__construct(
            $app->getResponseFactory(),
            $app->getCallableResolver(),
            $app->getContainer(),
            $app->getRouteCollector(),
            $prefix
        );

    }

    /**
     * @param Application $app The {@see Application} to which this Service belongs.
     *
     * @return RouteGroupInterface Returns a {@see RouteGroup} for method chaining.
     */
    public abstract function __invoke(Application $app): RouteGroupInterface;

}
