<?php
declare(strict_types=1);

use App\Controllers\ExampleController;
use Slim\Routing\RouteCollectorProxy;
use UCRM\HTTP\Slim\Application;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UCRM\HTTP\Slim\Middleware\Authentication\AuthenticationHandler;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\CallbackAuthenticator;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\FixedAuthenticator;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 * @param Application $app              The {@see Application} to which this config file belongs.
 *
 * @return Application                  The {@see Application} with the newly appended config.
 */
return function(Application $app): Application
{
    #region Authenticator (Examples)

    $app->group("/auth", function(RouteCollectorProxy $group) use ($app)
    {
        $group
            ->get('/none',
                function (Request $request, Response $response, $args): Response
                {
                    $response->getBody()->write("Authenticated!");
                    return $response;
                });

        $group
            ->get('/fixed',
                function (Request $request, Response $response, $args): Response
                {
                    $response->getBody()->write("Authenticated!");
                    return $response;
                })
            ->add(new AuthenticationHandler($app))
            //->add(AuthenticationHandler::class) // Using DI Container!
            ->add(new FixedAuthenticator(true));

        $group
            ->get('/callback',
                function (Request $request, Response $response, $args): Response
                {
                    $response->getBody()->write("Authenticated!");
                    return $response;
                })
            ->add(new AuthenticationHandler($app))
            ->add(new CallbackAuthenticator(
                function(Request $request): bool
                {
                    return true;
                }
            ));
    });

    #endregion

    // You can use any valid Slim 4 routes/groups here...
    $app->get("/test/{name}", function (Request $request, Response $response, $args): Response {
        $response->getBody()->write($args["name"]);
        return $response;
    })->setName("test");

    // OR use a Controller class...
    $app->get("/contact", ExampleController::class . ":contact");
    // Even with authentication...
    $app->get("/users/{name}", ExampleController::action("users"))
        ->setName("users")
        ->add(new AuthenticationHandler($app));
    // A named routed, etc...
    $app->get("[/]", ExampleController::action("index"))
        ->setName("home");

    return $app;
};
