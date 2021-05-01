<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Twig\Extensions;

use DateTime;
use Exception;
use UCRM\HTTP\Slim\Middleware\Routing\QueryStringRouter;
use UCRM\HTTP\Slim\Application;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class QueryStringRouterExtension
 *
 * @package UCRM\HTTP\Twig
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies, Inc.
 */
class QueryStringRouterExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var Application The {@see Application} on which this Twig Extension operates.
     */
    protected $app;

    /**
     * @var array An array of extension-wide global values.
     */
    protected static $globals = [
        "app" => [
            // NOTE: Add any desired fixed globals here...
        ]
    ];

    /**
     * QueryStringRouterExtension constructor.
     *
     * @param Application $app The {@see Application} on which this Twig Extension operates.
     * @param string $controller The front-controller script as an URL prefix, defaults to "/index.php".
     * @param array $globals An optional array of global values to be made available to all Twig templates.
     * @param bool $debug Determines whether or not to display additional debug messages, defaults to FALSE.
     */
    public function __construct(Application $app, string $controller = "/index.php", array $globals = [],
                                bool $debug = false)
    {
        $this->app = $app;

        self::$globals["base_path"] =
        self::$globals["app"]["url"] =
            (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];

        self::$globals["app"]["controller"] = $controller;
        self::$globals["app"]["debug"] = $debug;

        foreach($globals as $key => $value)
            self::$globals["app"][$key] = $value;

    }

    /**
     * Gets the name of the extension.
     *
     * @return string The name of the extension.
     */
    public function getName(): string
    {
        return "QueryStringRouterExtension";

    }

    /**
     * Gets all token parsers, provided by this extension.
     *
     * @return TokenParserInterface[] An array of {@see TokenParserInterface TokenParser} objects.
     */
    public function getTokenParsers(): array
    {
        return [];

    }

    #region FILTERS

    /**
     * Gets all filters, provided by this extension.
     *
     * @return TwigFilter[] An array of {@see TwigFilter} objects.
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter("uncached", [$this, "uncached"]),
        ];

    }

    /**
     * @param string $path
     *
     * @return string
     * @throws Exception
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function uncached(string $path): string
    {
        $uncachedPath = "";

        //if(Strings::contains($path, "?"))
        if (strpos($path, "?") !== false)
        {
            $parts = explode("?", $path);

            $route = QueryStringRouter::extractRouteFromQueryString($parts[1]);

            parse_str($parts[1], $query);

            $query["v"] = (new DateTime())->getTimestamp();
            $queryParts = [];

            foreach($query as $key => $value)
                $queryParts[] = "$key=$value";

            $uncachedPath = $parts[0]."?".($route ? $route.($queryParts ? "&" : "") : "").implode("&", $queryParts);
        }
        else
        {
            $uncachedPath = $path."?v=".(new DateTime())->getTimestamp();
        }

        return $uncachedPath;

    }

    #endregion

    #region FUNCTIONS

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction("dump", function($data) { if(self::$globals["app"]["debug"]) var_dump($data); }),

            new TwigFunction("link", [$this, "link"]),
            new TwigFunction("route", [$this, "route"]),
        ];

    }

    /**
     * @param string $path
     *
     * @return string
     * @throws Exception
     */
    public function link(string $path): string
    {
        // Temporarily remove any URL fragment...
        $fragment = "";
        if(strpos($path, "#") !== false)
        {
            $fragment = substr($path, strpos($path, "#"));
            $path = str_replace($fragment, "", $path);
        }

        // Split the provided path into path and query string (if provided).
        list($path, $query) = $path !== "" ? explode("?", strpos("?", $path) !== false ? $path : "$path?") : ["", ""];

        //$url = self::$globals["app"]["url"] ?? "";
        $controller = self::$globals["app"]["controller"] ?? "";

        $path = ($path === "/" && $controller !== "") || $path === "" ? "" : ($controller !== "" ? "?" : "")."$path";

        $link = /* $relative ? */ $controller.$path /* : $url.$controller.$path */;
        $link .= $query !== "" ? ($controller !== "" && $path !== "" ? "&" : "?")."$query" : "";

        return $link.$fragment ?: $path;

    }

    /**
     * Gets the url for a named route.
     *
     * @param string $name The name of the route to find.
     * @param array $data Optional Route placeholders.
     * @param array $params Optional Query parameters.
     *
     * @return string
     */
    public function route(string $name, array $data = [], array $params = []): string
    {
        return $this->app->getRouteCollector()->getRouteParser()->urlFor($name, $data, $params);

    }

    #endregion

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return self::$globals;

    }

    public static function addGlobal(string $name, $value, string $namespace = "app")
    {
        if(!$namespace || $namespace === "")
            self::$globals[$name] = $value;
        else
            self::$globals[$namespace][$name] = $value;

    }

}
