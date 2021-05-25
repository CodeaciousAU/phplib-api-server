<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Codeacious\Stdlib\ArrayTool;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AllowedMethodsListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        return new AllowedMethodsListener(ArrayTool::getArrayAtPath($config, 'api:allowed_methods'));
    }
}