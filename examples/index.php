<?php /** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use Slim\Factory\AppFactory;
use UCRM\HTTP\Slim\Application;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\FixedAuthenticator;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 */

// Create, configure and use our DI Container.
$container = require __DIR__ . "/config/container.php";
AppFactory::setContainer($container);

// Create our Application.
$app = Application::fromApp(AppFactory::create());

// Add and configure our routing middleware.
$app->addRoutingMiddleware();
$app->useQueryStringRouter(); // "/", ["#^/public/#" => "/"]);

// Add our default error handlers.
$app->addDefaultErrorHandlers(getenv("ENVIRONMENT") === "dev", true, true);

// Add an application-level Authenticator.
$app->setDefaultAuthenticator(new FixedAuthenticator(true));

// Configure asset handling...
$app->require(__DIR__ . "/config/services/assets.php");

// Configure script handling...
$app->require(__DIR__ . "/config/services/scripts.php");

// Configure template handling...
$app->require(__DIR__ . "/config/services/templates.php");

// Configure routes...
$app->require(__DIR__ . "/config/routes/examples.php");

// Run the Application!
$app->run();
