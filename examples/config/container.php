<?php
declare(strict_types=1);

use UCRM\HTTP\Slim\Application;
use UCRM\HTTP\Slim\Middleware\Authentication\AuthenticationHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;
use UCRM\HTTP\Twig\Extensions\QueryStringRouterExtension;

$containerBuilder = new DI\ContainerBuilder();

$containerBuilder->addDefinitions(
    [
        ResponseFactoryInterface ::class => DI\create(ResponseFactory::class),
        App::class => DI\autowire(Application::class),
        AuthenticationHandler::class => DI\create(AuthenticationHandler::class)->constructor(DI\get(App::class)),
    ]
);

$settings = (require __DIR__ . "/settings.php")($containerBuilder);

return $containerBuilder->build();
