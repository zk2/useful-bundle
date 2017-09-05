<?php

namespace Zk2\UsefulBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Zk2UsefulBundle extends Bundle
{
    /**
     * @var ContainerInterface
     */
    private static $containerInstance = null;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        self::$containerInstance = $container;
    }

    public static function getFullWebPath()
    {
        return realpath(self::$containerInstance->getParameter('zk2_useful.web_path'));
    }
}
