<?php /** @noinspection PhpUnused, PhpUnusedParameterInspection */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Services;

use UCRM\HTTP\Slim\Application;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;

/**
 * A Service to handle routing and delivery of PHP scripts.
 *
 * @package UCRM\HTTP\Slim\Services
 * @final
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies, Inc.
 */
final class ScriptService extends Service
{
    /**
     * @var string The base path to use when loading scripts.
     */
    protected $path;

    /**
     * ScriptService constructor.
     *
     * @param Application $app The {@see Application} to which this Service belongs.
     * @param string $path The base path to use when loading scripts, defaults to "./scripts/".
     * @param string $prefix An optional {@see RouteGroup} prefix to use for this Service, defaults to "".
     */
    public function __construct(Application $app, string $path = "./scripts/", string $prefix = "")
    {
        parent::__construct($app, $prefix);
        $this->path = $path;

    }

    /**
     * @inheritDoc
     */
    public function __invoke(Application $app): RouteGroupInterface
    {
        // Mapped, in cases where a DI Container replaces the $this context in Closures.
        $self = $this;

        return $this->group("", function(RouteCollectorProxyInterface $group) use ($self)
        {
            $group->map([ "GET", "POST" ], "/{file:.+}.{ext:php}",
                function (Request $request, Response $response, array $args) use ($self)
                {
                    // Get the file and extension from the matched route.
                    $file = $args["file"] ?? "index";
                    $ext = $args["ext"] ?? "php";

                    /*
                    // Interpolate the absolute path to the PHP script.
                    $path = rtrim($self->path, "/") . "/$file.$ext";

                    // IF the PHP script file does not exist, THEN return a 404 page!
                    if(!file_exists($path))
                    {
                        // Return the default 404 page!
                        throw new HttpNotFoundException($request);
                    }
                    */

                    // Interpolate the absolute path to the static asset.
                    $path = realpath(rtrim($self->path, "/") . "/$file.$ext");

                    // IF the static asset file does not exist, THEN throw a HTTP 404 Not Found Exception!
                    if (!$path)
                        throw new HttpNotFoundException($request);

                    /** @noinspection PhpIncludeInspection */

                    // Pass execution to the specified PHP file.
                    include $path;

                    // The PHP script should handle everything and since there is no Response to return, simply die()!
                    die();
                }
            )->setName(ScriptService::class);

        });

    }

}
