<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Services;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;
use UCRM\HTTP\Slim\Application;

/**
 * Class TemplateService
 *
 * Handles routing and subsequent rendering of Twig templates.
 *
 * @package UCRM\Slim\Services
 * @final
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies, Inc.
 */
final class TemplateService extends Service
{
    /**
     * @var string The base path to use when loading templates.
     */
    protected $path;

    /**
     * @var string The container key to use when looking up Twig, defaults to "view".
     */
    protected $twigContainerKey;

    /**
     * TemplateService constructor.
     *
     * @param Application $app The Slim Application for which to configure routing.
     * @param string $path The absolute path to the templates directory.
     * @param string $twigContainerKey An optional container key, if the default key "view" is not used.
     */
    public function __construct(Application $app, string $path, string $twigContainerKey = "view")
    {
        parent::__construct($app);
        $this->path = $path;
        $this->twigContainerKey = $twigContainerKey;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(Application $app): RouteGroupInterface
    {
        // Mapped, in cases where a DI Container replaces the $this context in Closures.
        $service = $this;

        return $this->group("", function(RouteCollectorProxyInterface $group) use ($service)
        {
            $group->map([ "GET" ], "/{file:.+}.{ext:twig}",
                function (Request $request, Response $response, array $args) use ($service)
                {
                    /** @var ContainerInterface $this */

                    // Get the file and extension from the matched route.
                    list($file, $ext) = array_values($args);

                    // Interpolate the absolute path to the Twig template.
                    $template = realpath(rtrim($service->path, "/") . "/$file.$ext");

                    // Get local references to the Twig Environment and Loader.
                    /** @var Twig $twig */
                    $twig = $this->get($service->twigContainerKey);

                    /** @var FilesystemLoader $loader */
                    $loader = $twig->getLoader();

                    // IF the TemplateService's path is not already in the Loader's list of paths, THEN add it!
                    if(!in_array(realpath($service->path), $loader->getPaths()))
                        $loader->addPath(realpath($service->path));

                    // Assemble some standard data to send along to the Twig template!
                    $data = [
                        "attributes" => $request->getAttributes(),
                        "uri" => $request->getUri(),
                    ];

                    $service->logger->debug(
                        "{$service->prefix}/$file.$ext",
                        [
                            "service" => TemplateService::class,
                            "path" => $template
                        ]
                    );

                    // IF the template file exists AND is not a directory...
                    if (file_exists($template) && !is_dir($template))
                    {
                        // ...THEN render it!
                        return $twig->render($response, "$file.$ext", $data);
                    }
                    else
                    {
                        // OTHERWISE, return a HTTP 404 Not Found!
                        throw new HttpNotFoundException($request);
                    }
                }
            )->setName(TemplateService::class);

        });

    }

}
