<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

/**
 * Event listener which injects Cross-Origin Resource Sharing headers into the HTTP response.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class CorsListener extends AbstractListenerAggregate
{
    /**
     * @var array
     */
    private $allowedMethods;


    public function __construct(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @param \Laminas\EventManager\EventManagerInterface $events
     * @param int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        /**
         * Request-handling event sequence is bootstrap, route, dispatch, render, finish.
         * @see https://docs.laminas.dev/laminas-mvc/mvc-event/
         */
        $this->listeners[] = $events->getSharedManager()->attach(
            '*',
            MvcEvent::EVENT_FINISH,
            [$this, 'onFinish'],
            10
        );
    }

    /**
     * @param MvcEvent $event
     * @return void|mixed
     */
    public function onFinish(MvcEvent $event)
    {
        $request = $event->getRequest();
        if (!$request instanceof Request)
            return; //Not a HTTP request

        if (!$request->getHeaders()->get('Origin'))
            return; //Not a CORS request

        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch)
            return;

        $controller = $routeMatch->getParam('controller');
        if (!$controller || !isset($this->allowedMethods[$controller]))
            return;

        $allowedMethods = $this->allowedMethods[$controller];
        if (!in_array($request->getMethod(), $allowedMethods)
            && $request->getMethod() != Request::METHOD_OPTIONS)
        {
            return;
        }

        $response = $event->getResponse(); /* @var $response Response */
        $headers = $response->getHeaders();

        //The URL has been routed to a REST API controller. Allow requests from all origins.
        $headers->addHeaderLine('Access-Control-Allow-Origin', '*')
            ->addHeaderLine('Access-Control-Allow-Headers', 'Authorization');

        if ($request->getMethod() == Request::METHOD_OPTIONS)
        {
            $headers
                ->addHeaderLine('Access-Control-Max-Age', '86400')
                ->addHeaderLine('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        }
    }
}