<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use Psr\Container\ContainerInterface;

/**
 * @copyright 2020 - Spaeth Technologies, Inc.
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 * Class ExampleController
 *
 * @package UCRM\HTTP\Controllers
 *
 */
abstract class Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function action(string $action): string
    {
        return get_called_class() . ":$action";
    }

}
