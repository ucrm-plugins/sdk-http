<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

interface ControllerInterface
{

    public function index( ServerRequest $request, Response $response, array $args ): Response;

}
