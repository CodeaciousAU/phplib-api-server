<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;

/**
 * Event listener which intercepts requests at the end of the routing phase, and modifies the
 * controller namespace based on the requested API version.
 */
class VersionListener extends AbstractListenerAggregate
{
    /**
     * @param EventManagerInterface $events
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
            MvcEvent::EVENT_ROUTE,
            array($this, 'onRoute'),
            -41
        );
    }

    /**
     * @param MvcEvent $e
     * @return void
     */
    public function onRoute(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!($routeMatch instanceof RouteMatch))
            return;

        $version = $routeMatch->getParam('version');
        if (!$version)
            return;

        $controller = $routeMatch->getParam('controller');
        if (!$controller)
            return;

        $pattern = '#' . preg_quote('\V') . '(\d+)' . preg_quote('\\') . '#';
        if (! preg_match($pattern, $controller, $matches))
        {
            //Controller does not have a version subnamespace
            return;
        }

        $replacement = preg_replace($pattern, '\V' . $version . '\\', $controller);
        if ($controller === $replacement)
        {
            return;
        }

        $routeMatch->setParam('controller', $replacement);
    }
}