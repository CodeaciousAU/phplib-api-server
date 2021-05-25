<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller\Plugin;

use Codeacious\ApiServer\Rest\ModelService;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class RestModel extends AbstractPlugin
{
    /**
     * @var ModelService
     */
    private $modelService;


    /**
     * @return ModelService
     */
    public function __invoke()
    {
        if (!$this->modelService)
        {
            $controller = $this->getController();
            if (! $controller instanceof AbstractController)
                throw new \RuntimeException('Controller class not supported');

            $modelService = $controller->getEvent()->getParam('ModelService');
            if (!$modelService)
                throw new \RuntimeException('No ModelService instance is available');

            $this->modelService = $modelService;
        }

        return $this->modelService;
    }
}