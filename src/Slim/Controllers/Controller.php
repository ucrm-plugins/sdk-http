<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

/**
 * @copyright 2019 Spaeth Technologies, Inc.
 * @author    Ryan Spaeth (rspaeth@mvqn.net)
 *
 * The base Controller class from which all controllers should extend.
 *
 * @package UCRM\HTTP
 * @abstract
 */
abstract class Controller implements ControllerInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var LoggerInterface */
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
     * Handles dynamic Controller actions, based upon the "action" argument passed with the route.
     *
     * @param ServerRequest $request    The Request object.
     * @param Response $response        The current Response object.
     * @param array $args               Any arguments parsed form the route.
     *
     * @return Response                 Returns the modified Response object.
     *
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
     * A {@see Controller} "helper" function to return a callable action for Slim.
     *
     * @param string $action            The method name on the {@see Controller}.
     * @return string                   Returns a Slim compatible controller action.
     */
    public static function action(string $action): string
    {
        return get_called_class() . ":$action";
    }

}
