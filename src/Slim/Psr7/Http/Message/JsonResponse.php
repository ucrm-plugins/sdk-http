<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Psr7\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Class JsonResponse
 *
 * @package UCRM\HTTP\Slim\Psr7\Http\Message
 *
 * @author Ryan Spaeth
 * @copyright 2020 Spaeth Technologies Inc.
 */
class JsonResponse
{
    /**
     * The default JSON encoding options.
     */
    protected const DEFAULT_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * @param array $data
     * @param int $options
     * @return ResponseInterface
     */
    public static function create(array $data, $options = self::DEFAULT_OPTIONS): ResponseInterface
    {
        $response = (new ResponseFactory())->createResponse(200);
        return self::fromResponse($response, $data, $options);

    }

    /**
     * Constructs a {@see JsonResponse} from an existing {@see ResponseInterface}.
     *
     * @param ResponseInterface $response An existing {@see ResponseInterface} object.
     * @param array $data The data to be encoded as JSON.
     * @param int $options Optional encoding options, defaults to (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).
     * @return ResponseInterface
     */
    public static function fromResponse(ResponseInterface $response, array $data, $options = self::DEFAULT_OPTIONS)
        : ResponseInterface
    {
        $response->getBody()->write(json_encode($data, $options));
        return $response->withHeader("Content-Type", "application/json");

    }

}
