<?php
declare(strict_types=1);

use UCRM\HTTP\Slim\Application;
use UCRM\HTTP\Slim\Services\TemplateService;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 * @param Application $app              The {@see Application} to which this config file belongs.
 *
 * @return Application                  The {@see Application} with the newly appended config.
 */
return function (Application $app): Application
{
    // Add and configure the Twig middleware.
    $app->useTwigTemplateEngine(
        // Multiple paths CAN be provided.
        [
            __DIR__ . "/../../views/"
        ],
        // Any valid Twig options.
        [
            // Cache Directory (or FALSE to disable, default is FALSE)
            "cache" => (getenv("ENVIRONMENT") === "dev") ? false : __DIR__ . "/../views/.cache/"
        ],
        // Debug?  All this current does is make an "app.debug" variable available to ALL templates.
        (getenv("ENVIRONMENT") === "dev")
    );

    // This Service handles any Twig templates...
    $app->addService(new TemplateService($app, __DIR__ . "/../../views/"));

    // Globals available to ALL Twig templates...
    $app->addTemplateGlobal("home", "/", ""); // Forces the removal of the "app" prefix to our variable name.
    $app->addTemplateGlobal("test", [ "TEST1", "TEST2" ]); // Any type of value can be passed along to the templates.

    return $app;
};
