<?php
declare(strict_types=1);

namespace App\Controllers;

use UCRM\HTTP\Slim\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 * Class ExampleController
 *
 * @package App\Controllers
 *
 */
class ExampleController extends Controller
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $this->logger->debug(__FUNCTION__, [ "class" => __CLASS__, "args" => $args ]);

        // your code to access items in the container... $this->container->get('');
        $response->getBody()->write("HOME");
        return $response;
    }

    public function contact(Request $request, Response $response, array $args): Response
    {
        $this->logger->debug(__FUNCTION__, [ "class" => __CLASS__, "args" => $args ]);

        // your code to access items in the container... $this->container->get('');
        $response->getBody()->write("CONTACT");
        return $response;
    }

    public function users(Request $request, Response $response, array $args): Response
    {
        $this->logger->debug(__FUNCTION__, [ "class" => __CLASS__, "args" => $args ]);

        // your code to access items in the container... $this->container->get('');
        $response->getBody()->write("USERS: " . $args["name"]);
        return $response;
    }

}
