<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use FastRoute\BadRouteException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 * Class ExampleController
 *
 * @package UCRM\HTTP\Controllers
 *
 */
abstract class Controller implements ControllerInterface
{
    protected $container;

    protected $logger;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }


    public function __invoke( ServerRequest $request, Response $response, array $args ): Response
    {
        if( !$args || !array_key_exists( "action", $args ) )
            $args["action"] = "index";

        if( !method_exists($this, $args["action"] ) )
            throw new BadRouteException(
                "Controller '" . get_class($this). "' does not contain a method for Action '" . $args['action'] . "'");

        $method = $args["action"];

        return $this->$method($request, $response, $args);
    }


    /**
     * @param string $action
     * @return string
     */
    public static function action(string $action): string
    {
        return get_called_class() . ":$action";
    }

}
