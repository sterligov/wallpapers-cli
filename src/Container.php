<?php


namespace App;


use App\Exception\UnregisteredContainerException;
use Psr\Container\ContainerInterface;

class Container
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    private function __construct()
    {
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws UnregisteredContainerException
     * @throws \ReflectionException
     */
    public static function __callStatic($name, $arguments)
    {
        if (!self::$container) {
            throw new UnregisteredContainerException('Container does not exist');
        }

        $method = new \ReflectionMethod(self::$container, $name);

        return $method->invoke(self::$container, ...$arguments);
    }

    /**
     * @param ContainerInterface $container
     */
    public static function register(ContainerInterface $container)
    {
        self::$container = $container;
    }
}