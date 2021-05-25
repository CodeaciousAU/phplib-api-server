<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer;

use Nocarrier\Hal;
use Laminas\Uri\Uri;

/**
 * Creates a HAL resource representing a paginated collection of other HAL resources.
 */
class HalCollectionBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $pageNumber = 1;

    /**
     * @var int
     */
    private $itemsPerPage = 0;

    /**
     * @var int
     */
    private $totalItems = 0;

    /**
     * @var Hal[]
     */
    private $resources = [];

    /**
     * @var array
     */
    private $extraProps = [];


    /**
     * @param string $name
     * @param string $url
     */
    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @param int $pageNumber
     * @return $this
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = intval($pageNumber);
        return $this;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        if ($this->itemsPerPage == 0)
            return 1;
        return ceil($this->totalItems / $this->itemsPerPage) ?: 1;
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $itemsPerPage
     * @return $this
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = intval($itemsPerPage);
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @param int $totalItems
     * @return $this
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = intval($totalItems);
        return $this;
    }

    /**
     * @return Hal[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param Hal[] $resources
     * @return $this
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraProperties()
    {
        return $this->extraProps;
    }

    /**
     * @param array $extraProps
     * @return $this
     */
    public function setExtraProperties(array $extraProps)
    {
        $this->extraProps = $extraProps;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addExtraProperty($key, $value)
    {
        $this->extraProps[$key] = $value;
        return $this;
    }

    /**
     * @return Hal
     */
    public function build()
    {
        $baseUrl = new Uri($this->url);
        $queryParams = $baseUrl->getQueryAsArray();

        $queryParams['page'] = $this->pageNumber;
        $currentPageUrl = $baseUrl->setQuery($queryParams)->toString();

        $pageSize = $this->itemsPerPage;
        if (!$pageSize)
            $pageSize = count($this->resources);

        $totalItems = $this->totalItems;
        if (!$totalItems)
            $totalItems = count($this->resources);

        $pageCount = $this->getPageCount();

        $collection = new Hal($currentPageUrl, [
            'page' => $this->pageNumber,
            'page_count' => $pageCount,
            'page_size' => $pageSize,
            'total_items' => $totalItems,
        ] + $this->extraProps);

        if (count($this->resources))
        {
            foreach ($this->resources as $resource)
                $collection->addResource($this->name, $resource, true);
        }
        else
        {
            //Add an empty collection
            $collection->addResource($this->name, null, true);
        }

        $queryParams['page'] = 1;
        $collection->addLink('first', $baseUrl->setQuery($queryParams)->toString());
        $queryParams['page'] = $pageCount;
        $collection->addLink('last', $baseUrl->setQuery($queryParams)->toString());

        if ($this->pageNumber < $pageCount)
        {
            $queryParams['page'] = $this->pageNumber+1;
            $collection->addLink('next', $baseUrl->setQuery($queryParams)->toString());
        }
        if ($this->pageNumber > 1)
        {
            $queryParams['page'] = $this->pageNumber-1;
            $collection->addLink('prev', $baseUrl->setQuery($queryParams)->toString());
        }

        return $collection;
    }
}