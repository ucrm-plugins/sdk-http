<?php
declare(strict_types=1);

use Slim\App;
use UCRM\HTTP\Slim\Middleware\Handlers\MethodNotAllowedHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\NotFoundHandler;
use UCRM\HTTP\Slim\Middleware\Handlers\UnauthorizedHandler;

return function (App $app) {
    //$app->add(SessionMiddleware::class);


    /**
     * Add Error Handling Middleware
     *
     * @param bool $displayErrorDetails Should be set to false in production
     * @param bool $logErrors Parameter is passed to the default ErrorHandler
     * @param bool $logErrorDetails Display error details in error log which can be replaced by any callable.
     * NOTE: This middleware should be added last, as it will not handle any errors for any middleware after it!
     */
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    // Add our own HTTP 401 Unauthorized handler.
    $errorMiddleware->setErrorHandler(HttpUnauthorizedException::class, new UnauthorizedHandler($app));

    // Add our own HTTP 404 Not Found handler.
    $errorMiddleware->setErrorHandler(HttpNotFoundException::class, new NotFoundHandler($app));

    // Add our own HTTP 405 Method Not Allowed handler.
    $errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, new MethodNotAllowedHandler($app));

};
