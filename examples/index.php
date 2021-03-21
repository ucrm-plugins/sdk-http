<?php /** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use App\Controllers\ExampleController;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Factory\AppFactory;
use UCRM\HTTP\Slim\Middleware\Handlers\MethodNotAllowedHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\NotFoundHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\UnauthorizedHandler;
use UCRM\HTTP\Slim\Services\TemplateService;
use UCRM\HTTP\Slim\Application;
use UCRM\HTTP\Slim\Services\AssetService;
use UCRM\HTTP\Slim\Services\ScriptService;
use UCRM\HTTP\Slim\Middleware\Authentication\AuthenticationHandler;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\FixedAuthenticator;
use UCRM\HTTP\Twig\Extensions\QueryStringRouterExtension;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\CallbackAuthenticator;
use Slim\Routing\RouteCollectorProxy;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 */

// Create and configure our DI Container.
$container = require __DIR__ . "/config/container.php";

// Create our Application.
AppFactory::setContainer($container);
$app = Application::fromApp(AppFactory::create());

// Add and configure our routing middleware.
$app->addRoutingMiddleware();
$app->useQueryStringRouter("/"); //, ["#^/public/#" => "/"]);

// Add our default error handlers.
// NOTE: Be sure to set the "displayErrorDetails" to false in production!
$app->addDefaultErrorHandlers(true, true, true);

// Add and configure the Twig middleware.
$app->useTwigTemplateEngine([ __DIR__ . "/views/" ], [ /* "cache" => __DIR__ . "/views/.cache/" */ ], true);

//QueryStringRouterExtension::addGlobal("home", "/", ""); // {{ home }}
$app->addTemplateGlobal("home", "/", "");
//QueryStringRouterExtension::addGlobal("test", [ "TEST1", "TEST2" ]); // {{ app.test }}
$app->addTemplateGlobal("test", [ "TEST1", "TEST2" ]);

// Add an application-level Authenticator.
//$app->setDefaultAuthenticator(new FixedAuthenticator(true));
$app->add(new FixedAuthenticator(true));

// NOTE: This Service handles any static assets (i.e. png, jpg, html, pdf, etc.)...
$app->addService(new AssetService($app, __DIR__."/public/", "/public")); // Deeper paths must be listed first!
$app->addService(new AssetService($app, __DIR__."/assets/"))
    ->add(new AuthenticationHandler($app))
    ->add(new FixedAuthenticator(false));

// NOTE: This Service handles any PHP scripts...
$app->addService(new ScriptService($app, __DIR__ . "/scripts/"));

// NOTE: This Service handles any Twig templates...
$app->addService(new TemplateService($app, __DIR__."/views/"));

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

$app->get("/test/{name}", function (Request $request, Response $response, $args): Response {
    $response->getBody()->write($args["name"]);
    return $response;
})->setName("test");


// NOTE: You can use any valid Slim 4 routes/groups here...

// Handle the default route, with or without the trailing slash...
/*
$app->get("[/]", function (Request $request, Response $response, $args): Response {
    $response->getBody()->write("HOME");
    return $response;
})->setName("home");
*/
$app->get("/contact", ExampleController::class . ":contact");
$app->get("[/]", ExampleController::action("index"));
$app->get("/users/{name}", ExampleController::action("users"))->setName("users");

// Finally, run the Application!
$app->run();
