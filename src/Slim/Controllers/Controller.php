<?php
declare(strict_types=1);

namespace UCRM\HTTP\Slim\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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

    protected $logger;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param string $action
     * @return string
     */
    public static function action(string $action): string
    {
        return get_called_class() . ":$action";
    }



}
