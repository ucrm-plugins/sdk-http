<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

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


    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequest $request, Response $response, array $args ): Response
    {
        if( !$args || !array_key_exists( "action", $args ) )
            $args["action"] = "index";

        if( !method_exists($this, $args["action"] ) )
            throw new HttpNotFoundException($request,
                sprintf( "No Action method '%s' was not found in Controller class '%s'",
                $args['action'], get_class( $this ) )
            );

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
