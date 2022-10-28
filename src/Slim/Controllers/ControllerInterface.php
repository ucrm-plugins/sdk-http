<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

/**
 * @copyright 2019 Spaeth Technologies Inc.
 * @author    Ryan Spaeth (rspaeth@spaethtech.com)
 *
 * The Interface that all controllers shouild implement.
 *
 * @package UCRM\HTTP
 *
 */
interface ControllerInterface
{
    /**
     * The "default" action handler, which is required for all {@see Controller} classes.
     *
     * @param ServerRequest $request    The Request object.
     * @param Response $response        The current Response object.
     * @param array $args               Any arguments parsed form the route.
     *
     * @return Response                 Returns the modified Response object.
     */
    public function index( ServerRequest $request, Response $response, array $args ): Response;

}
