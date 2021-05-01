<?php
declare(strict_types=1);

use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use UCRM\HTTP\Slim\Application;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 */
return (function (): ContainerInterface
{
    $containerBuilder = new DI\ContainerBuilder();

    $containerBuilder->addDefinitions(
        [
            // Use the Slim PSR-7 Response implementation.
            ResponseFactoryInterface ::class => DI\create(ResponseFactory::class),

            // Use our custom Slim App.
            App::class => DI\autowire(Application::class),

            //AuthenticationHandler::class => DI\create(AuthenticationHandler::class)->constructor(DI\get(App::class)),

            LoggerInterface::class => function () {
                $logger = new Logger("App");

                $processor = new UidProcessor();
                $logger->pushProcessor($processor);

                $handler = new StreamHandler(
                    isset($_ENV["docker"])
                        ? "php://stdout"
                        : __DIR__ . "/../logs/app.log"
                    ,
                    Logger::DEBUG
                );
                $logger->pushHandler($handler);

                $sqlite = new \UCRM\Logging\Monolog\Handlers\Sqlite3Handler(__DIR__ . "/../logs/app.sqlite");
                $logger->pushHandler($sqlite);

                if (getenv("ENVIRONMENT") === "dev") {
                    $devHandler = new ChromePHPHandler(__DIR__ . "/../logs/app.log", Logger::DEBUG);
                    $devHandler->setFormatter(new ChromePHPFormatter());
                    $logger->pushHandler($devHandler);

                }

                return $logger;
            },


        ]
    );

    return $containerBuilder->build();

})();
