<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller\Plugin;

use Codeacious\ApiServer\HalCollectionBuilder as Builder;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class HalCollectionBuilder extends AbstractPlugin
{
    /**
     * @param string $name
     * @param int $totalItems
     * @return Builder
     */
    public function __invoke($name, $totalItems)
    {
        $controller = $this->getController();
        if (! $controller instanceof AbstractController)
            throw new \RuntimeException('Controller class not supported');

        $request = $controller->getRequest();
        if (! $request instanceof Request)
            throw new \RuntimeException('Request class not supported');

        $params = $request->getQuery();

        $itemsPerPage = intval($params->get('perPage'));
        if ($itemsPerPage < 1)
            $itemsPerPage = 25;

        $pageNumber = intval($params->get('page'));
        if ($pageNumber < 1)
            $pageNumber = 1;

        $builder = new Builder($name, $request->getUriString());
        $builder
            ->setPageNumber($pageNumber)
            ->setItemsPerPage($itemsPerPage)
            ->setTotalItems($totalItems);

        return $builder;
    }
}