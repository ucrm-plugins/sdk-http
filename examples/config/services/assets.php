<?php
declare(strict_types=1);

use UCRM\HTTP\Slim\Application;
use UCRM\HTTP\Slim\Middleware\Authentication\AuthenticationHandler;
use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\FixedAuthenticator;
use UCRM\HTTP\Slim\Services\AssetService;

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
    // This Service handles any static assets (i.e. png, jpg, html, pdf, etc.)...
    // NOTES:
    // - More specific paths must be listed first.
    // - Authenticators can be added to at the Service-level to "override" any default/global authenticator.
    $app->addService(new AssetService($app, __DIR__ . "/../../public/", "/public"));
    $app->addService(new AssetService($app, __DIR__ . "/../../assets/"))
        ->add(new AuthenticationHandler($app))
        ->add(new FixedAuthenticator(false)); // Here we block access to everyone!

    return $app;
};
