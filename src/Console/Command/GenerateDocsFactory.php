<?php

namespace Codeacious\ApiServer\Console\Command;

use Codeacious\Stdlib\ArrayTool;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GenerateDocsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = ArrayTool::getArrayAtPath($container->get('config'), 'api:modules');
        $moduleNames = array_keys($config);
        return new GenerateDocs($moduleNames);
    }
}