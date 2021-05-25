<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Rest;

use Codeacious\Mapper\ModelFactory;
use Codeacious\Stdlib\ArrayTool;
use Interop\Container\ContainerInterface;
use Laminas\Http\Request;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;

class ModelService implements ModelFactory
{
    /**
     * @var ContainerInterface
     */
    private $mappers;

    /**
     * @var RouteStackInterface
     */
    private $router;

    /**
     * @var array
     */
    private $versionConfig;

    /**
     * @var int
     */
    private $activeVersion;


    /**
     * @param ContainerInterface $mappers
     * @param RouteStackInterface $router
     * @param array $versionConfig
     */
    public function __construct(ContainerInterface $mappers, RouteStackInterface $router,
                                array $versionConfig)
    {
        $this->mappers = $mappers;
        $this->router = $router;
        $this->versionConfig = $versionConfig;
    }

    /**
     * @return int
     */
    public function getActiveVersion()
    {
        return $this->activeVersion;
    }

    /**
     * @param int $activeVersion
     * @return $this
     */
    public function setActiveVersion($activeVersion)
    {
        $this->activeVersion = $activeVersion;
        return $this;
    }

    /**
     * @param string $serviceName
     * @return ModelMapper|null
     */
    public function getMapper($serviceName)
    {
        $mapper = $this->mappers->get($serviceName);
        if (! ($mapper instanceof ModelMapper))
            return null;

        return $mapper;
    }

    /**
     * @param string $class A domain model class name
     * @return ModelMapper|null
     */
    public function getMapperForClass($class)
    {
        $mapperName = $this->getMapperNameForModelClass($class);
        if (!$mapperName)
            return null;

        return $this->getMapper($mapperName);
    }

    /**
     * @param RouteMatch $routeMatch
     * @return ModelMapper|null
     */
    public function getMapperForRouteMatch(RouteMatch $routeMatch)
    {
        $routeName = $routeMatch->getMatchedRouteName();
        if (!$routeName)
            return null;

        $routeMap = $this->getRouteMap();
        if (!array_key_exists($routeName, $routeMap))
            return null;

        return $this->getMapper($routeMap[$routeName]);
    }

    /**
     * @param object $sourceModel A domain model
     * @return object|null A serializable object representation of the source model, or null
     */
    public function createModel($sourceModel)
    {
        $mapperName = $this->getMapperNameForModel($sourceModel);
        if (!$mapperName)
            return null;

        $mapper = $this->getMapper($mapperName);
        if (!$mapper)
            return null;

        return $mapper->createRestModel($sourceModel);
    }

    /**
     * @param string $url A RESTful entity URL
     * @return object|null A domain model, or null
     */
    public function resolveLink($url)
    {
        $request = new Request();
        $request->setUri($url);
        $routeMatch = $this->router->match($request);
        if (!$routeMatch)
            return null;

        $mapper = $this->getMapperForRouteMatch($routeMatch);
        if (!$mapper)
            return null;

        return $mapper->loadDomainModel($routeMatch->getParams());
    }

    /**
     * Generate a versioned RESTful entity URL using the given parameters.
     *
     * @param string $mapperName
     * @param array $routeParams
     * @return string
     */
    public function assembleUrl($mapperName, array $routeParams)
    {
        $routeName = array_search($mapperName, $this->getRouteMap());
        if ($routeName === false)
            throw new \RuntimeException('No route is associated with mapper '.$mapperName);

        $routeParams['version'] = strval($this->activeVersion);
        return $this->router->assemble($routeParams, [
            'name' => $routeName,
            'force_canonical' => true,
        ]);
    }

    /**
     * @param string $class
     * @return string|null
     */
    private function getMapperNameForModelClass($class)
    {
        $classMap = $this->getClassMap();
        if (in_array($class, $classMap))
            return $class;

        if (array_key_exists($class, $classMap))
            return $classMap[$class];

        return null;
    }

    /**
     * @param object $object
     * @return string|null
     */
    private function getMapperNameForModel($object)
    {
        if ($class = $this->getMapperNameForModelClass(get_class($object)))
            return $class;

        foreach ($this->getClassMap() as $domainClass => $serviceName)
        {
            if ($object instanceof $domainClass)
                return $serviceName;
        }

        return null;
    }

    /**
     * @return array Mapping of domain model class names to mapper service names
     */
    private function getClassMap()
    {
        return ArrayTool::getArrayAtPath($this->versionConfig,
            $this->getActiveVersion().':models');
    }

    /**
     * @return array Mapping of route names to mapper service names
     */
    private function getRouteMap()
    {
        return ArrayTool::getArrayAtPath($this->versionConfig,
            $this->getActiveVersion().':routes');
    }
}