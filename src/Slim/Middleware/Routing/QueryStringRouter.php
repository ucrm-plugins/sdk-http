<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Middleware\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class QueryStringRouter
 *
 * @package UCRM\HTTP\Slim\Middlware\Routing
 * @final
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
final class QueryStringRouter implements MiddlewareInterface
{
    /**
     * @var string The default route.
     */
    protected $defaultRoute;

    /**
     * @var array Any optional rewrite rules.
     */
    protected $rewriteRules;

    /**
     * QueryStringRouter constructor.
     *
     * @param string $defaultRoute The default route to use when no match is found in the query string.
     * @param array $rewriteRules Any optional rewrite rules to use on the found route, applied in order.
     */
    public function __construct(string $defaultRoute = "/", array $rewriteRules = [])
    {
        $this->defaultRoute = $defaultRoute;
        $this->rewriteRules = $rewriteRules;

    }

    /**
     * Extracts the route from a query string, while keeping the remainder of the query string intact.
     *
     * @param string $queryString The query string for which to perform the extraction.
     * @param array $rewriteRules Any optional rewrite rules to apply to the extracted route.
     *
     * @return string Returns the route as a string.
     */
    public static function extractRouteFromQueryString(string &$queryString, array $rewriteRules = []): string
    {
        // NOTE: We use our our parameter parsing here, to make sure things are handled OUR way!

        // Convert any URL encodings back to slashes.
        $queryString = str_replace("%2F", "/", $queryString);

        // Split the query parameters.
        $parts = explode("&", $queryString);

        // Set some initialized values.
        $route = "";
        $query = [];

        // Loop through each parameter...
        foreach($parts as $part)
        {
            // IF the parameter starts with "/", THEN assume it's a route.
            if (strpos($part, "/") === 0)
                $route = $part;

            // IF the parameter starts with "route=/" OR "r=/", THEN assume it's a route.
            else if (strpos($part, "route=/") === 0)
                $route = str_replace("route=/", "/", $part);
            else if (strpos($part, "r=/") === 0)
                $route = str_replace("r=/", "/", $part);

            // OTHERWISE, assume it's a normal query parameter.
            else
                $query[] = $part;
        }

        // NOTE: IF multiple route parameters are found, the last one takes precedence!

        // Loop through any provided rewrite rules and execute them as necessary...
        foreach($rewriteRules as $pattern => $replacement)
            $route = preg_replace($pattern, $replacement, $route);

        // Restructure the remaining parts of the query.
        $queryString = implode("&", $query);

        // Finally, return the route.
        return $route;

    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the current query if set, otherwise set it as the default route only.
        $queryString = $_SERVER["QUERY_STRING"] ?? $this->defaultRoute;

        // Extract (and remove) the route from the query string, using the default route if none found!
        $vRoute = $this->extractRouteFromQueryString($queryString, $this->rewriteRules) ?: $this->defaultRoute;

        // Convert the query string into an associative array.
        parse_str($queryString, $vQuery);

        // Get the current request, altering the extracted route and query string.
        $uri = $request->getUri()
            ->withPath($vRoute)
            ->withQuery($queryString);

        // Create a new request using this new information and append the route and query as attributes.
        $request = $request
            ->withUri($uri)
            ->withQueryParams($vQuery)
            ->withAttribute("vRoute", $vRoute)
            ->withAttribute("vQuery", $vQuery);

        // Update the actual PHP Super Globals with the newly parsed query information, so that other scripts have
        // access to the altered information.
        $_GET = $vQuery;
        $_SERVER["QUERY_STRING"] = $queryString;

        // Finally, pass the altered request to the middleware pipeline.
        return $handler->handle($request);

    }

}
