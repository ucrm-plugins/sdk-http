<?php
declare(strict_types=1);

use UCRM\HTTP\Slim\Application;
use UCRM\HTTP\Slim\Services\ScriptService;

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
    // This Service handles any PHP scripts...
    $app->addService(new ScriptService($app, __DIR__ . "/../../scripts/"));

    return $app;
};
