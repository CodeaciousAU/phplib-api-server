<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Codeacious\ApiServer\Rest\ModelMapper;
use Codeacious\ApiServer\Rest\ModelMapperManager;
use Codeacious\ApiServer\Rest\ModelService;
use Codeacious\ApiServer\UrlGenerator;
use Codeacious\Mapper\Mapper;
use Codeacious\Security\SecurityService;
use Codeacious\Stdlib\ArrayTool;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;

/**
 * Event listener which creates a Mapper instance and a ModelService, and injects them into the MVC
 * event prior to dispatch.
 */
class InjectMapperListener extends AbstractListenerAggregate
{
    /**
     * @var array Array of mapper configurations keyed by module name
     */
    private $mapperConfigs;

    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * @param array $mapperConfigs
     * @param ContainerInterface $container
     */
    public function __construct(array $mapperConfigs, ContainerInterface $container)
    {
        $this->mapperConfigs = $mapperConfigs;
        $this->container = $container;
    }

    /**
     * @param \Laminas\EventManager\EventManagerInterface $events
     * @param int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority=1)
    {
        /**
         * Request-handling event sequence is bootstrap, route, dispatch, render, finish.
         * @see https://docs.laminas.dev/laminas-mvc/mvc-event/
         */
        $this->listeners[] = $events->getSharedManager()->attach(
            '*',
            MvcEvent::EVENT_DISPATCH,
            array($this, 'onDispatch'),
            10
        );
    }

    /**
     * @param MvcEvent $e
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $router = $e->getRouter();
        if (! $router instanceof RouteStackInterface)
            return;

        $routeMatch = $e->getRouteMatch();
        if (! $routeMatch instanceof RouteMatch)
            return;

        $controllerName = $routeMatch->getParam('controller');
        if (!$controllerName)
            return;

        $version = $routeMatch->getParam('version');
        if ($version === null)
            return;

        $config = $this->findMapperConfig($controllerName);
        if ($config === null || !isset($config['versions'][$version]))
            return;

        $urlGenerator = new UrlGenerator($router, ['version' => $version]);
        $mapper = $this->createMapper($config['versions'][$version], $urlGenerator);

        $modelService = $this->createModelService($config, $version, $router);
        $mapper->setModelFactory($modelService);

        $e->setParam('mapper', $mapper);
        $e->setParam('ModelService', $modelService);
    }

    /**
     * @param string $controllerName
     * @return array|null
     */
    protected function findMapperConfig($controllerName)
    {
        foreach ($this->mapperConfigs as $moduleName => $config)
        {
            if (strpos($controllerName, $moduleName.'\\') === 0)
                return $config;
        }
        return null;
    }

    /**
     * Construct a new Mapper instance using the provided settings.
     *
     * @param array $config
     * @param UrlGenerator $urlGenerator
     * @return Mapper
     */
    protected function createMapper(array $config, UrlGenerator $urlGenerator)
    {
        $mapper = new Mapper($urlGenerator);

        if (isset($config['class_map']))
            $mapper->setClassMap($config['class_map']);

        if (isset($config['route_map']))
            $mapper->setRouteMap($config['route_map']);

        return $mapper;
    }

    /**
     * Construct a new ModelService instance using the provided settings.
     *
     * @param array $config
     * @param int $activeVersion
     * @param RouteStackInterface $router
     * @return ModelService
     */
    protected function createModelService(array $config, $activeVersion, RouteStackInterface $router)
    {
        //Create the plugin manager that will hold the individual ModelMappers
        $modelMapperManager = new ModelMapperManager($this->container,
            ArrayTool::getArrayAtPath($config, 'model_mapper_manager'));

        //Create the ModelService
        $versionConfig = ArrayTool::getArrayAtPath($config, 'versions');
        $modelService = new ModelService($modelMapperManager, $router, $versionConfig);
        $modelService->setActiveVersion($activeVersion);

        //The plugin manager will configure mappers using the correct ModelService
        $modelMapperManager->addInitializer(
            function(ContainerInterface $container, ModelMapper $instance) use ($modelService)
            {
                $instance->setModelService($modelService)
                    ->setSecurityService($container->get(SecurityService::class));
            }
        );

        return $modelService;
    }
}