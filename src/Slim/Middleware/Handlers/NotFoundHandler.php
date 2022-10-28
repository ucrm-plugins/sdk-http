<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Middleware\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

/**
 * Class NotFoundHandler
 *
 * @package UCRM\HTTP\Slim\Error\Handlers
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
final class NotFoundHandler extends ErrorHandler
{
    /**
     * @param Request $request
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     *
     * @return Response
     */
    public function __invoke(Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors,
        bool $logErrorDetails): Response
    {
        // Setup some debugging information to pass along to the template...
        $data = [
            "debug"         => $displayErrorDetails,
            "vRoute"        => $request->getAttribute("vRoute"),
            "vQuery"        => $request->getAttribute("vQuery"),
            "authenticator" => $request->getAttribute("authenticator"),
            "routes"        => $this->app->getRouteCollector()->getRoutes(),
            "message"       => $exception->getMessage(),
        ];

        // Instantiate a response object and return the rendered template.
        $response = $this->app->getResponseFactory()->createResponse(404);
        return $this->render($response, __DIR__ . "/templates/404.php", $data);

    }

}
