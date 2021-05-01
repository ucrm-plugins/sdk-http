<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Services;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;
use UCRM\HTTP\Slim\Application;

/**
 * A Service to handle routing and delivery of static assets.
 *
 * @package UCRM\HTTP\Slim\Services
 * @final
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies, Inc.
 */
final class AssetService extends Service
{
    /**
     * @var string The base path to use when loading assets.
     */
    protected $path;

    /**
     * AssetService constructor.
     *
     * @param Application $app The {@see Application} to which this Service belongs.
     * @param string $path The optional base path to use when loading assets, defaults to "./assets/".
     * @param string $prefix An optional {@see RouteGroup} prefix to use for this Service, defaults to "".
     *
     */
    public function __construct(Application $app, string $path = "./assets/", string $prefix = "")
    {
        parent::__construct($app, $prefix);
        $this->path = $path;

    }

    /**
     * @inheritDoc
     *
     * @noinspection SpellCheckingInspection (Content-Type)
     */
    public function __invoke(Application $app): RouteGroupInterface
    {
        // Mapped, in cases where a DI Container replaces the $this context in Closures.
        $service = $this;

        return $this->group("", function(RouteCollectorProxyInterface $group) use ($service)
        {
            // NOTE: More asset types can be added as necessary...
            $group->map([ "GET" ], "/{file:.+}.{ext:jpg|png|pdf|txt|css|js|htm|html|svg|ttf|woff|woff2}",
                function (Request $request, Response $response, array $args) use ($service)
                {
                    /** @var ContainerInterface $this */

                    // Get the file and extension from the matched route.
                    $file = $args["file"];
                    $ext = $args["ext"];

                    // Interpolate the absolute path to the static asset.
                    $path = realpath(rtrim($service->path, "/") . "/$file.$ext");

                    // IF the static asset file does not exist, THEN throw a HTTP 404 Not Found Exception!
                    if (!$path)
                        throw new HttpNotFoundException($request);

                    // Write the contents of the file to the response body.
                    $response->getBody()->write(file_get_contents($path));

                    // Determine the Content-Type by the extension...
                    switch ($ext)
                    {
                        case "jpg"  :   $contentType = "image/jpg";                         break;
                        case "png"  :   $contentType = "image/png";                         break;
                        case "pdf"  :   $contentType = "application/pdf";                   break;
                        case "txt"  :   $contentType = "text/plain";                        break;
                        case "css"  :   $contentType = "text/css";                          break;
                        case "js"   :   $contentType = "text/javascript";                   break;
                        case "htm"  :
                        case "html" :   $contentType = "text/html";                         break;
                        case "svg"  :   $contentType = "image/svg+xml";                     break;
                        case "ttf"  :   $contentType = "application/x-font-ttf";            break;
                        case "otf"  :   $contentType = "application/x-font-opentype";       break;
                        case "woff" :   $contentType = "application/font-woff";             break;
                        case "woff2":   $contentType = "application/font-woff2";            break;
                        case "eot"  :   $contentType = "application/vnd.ms-fontobject";     break;
                        case "sfnt" :   $contentType = "application/font-sfnt";             break;

                        default     :   $contentType = "application/octet-stream";          break;
                    }

                    $service->logger->debug(
                        "{$service->prefix}/$file.$ext",
                        [
                            "service" => AssetService::class,
                            "path" => $path,
                            "content-type" => $contentType
                        ]
                    );

                    // Finally, set the response Content-Type header and return the response!
                    return $response->withHeader("Content-Type", $contentType);
                }
            )->setName(AssetService::class);
        });
    }

}
