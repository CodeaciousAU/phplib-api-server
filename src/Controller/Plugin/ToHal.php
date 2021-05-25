<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller\Plugin;

use Codeacious\Mapper\Mapper;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class ToHal extends AbstractPlugin
{
    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @param array|object $value
     * @return \Nocarrier\Hal
     */
    public function __invoke($value)
    {
        if (!$this->mapper)
        {
            $controller = $this->getController();
            if (! $controller instanceof AbstractController)
                throw new \RuntimeException('Controller class not supported');

            $mapper = $controller->getEvent()->getParam('mapper');
            if (!$mapper)
                throw new \RuntimeException('No Mapper instance is available');

            $this->mapper = $mapper;
        }

        return $this->mapper->createHalResource($value);
    }
}