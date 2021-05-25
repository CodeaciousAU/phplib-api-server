<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Codeacious\ApiServer\Problem\MethodNotAllowedProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

/**
 * Event listener which enforces the allowedMethods whitelist, and responds to OPTIONS requests.
 */
class AllowedMethodsListener extends AbstractListenerAggregate
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
            MvcEvent::EVENT_ROUTE,
            [$this, 'onRoute'],
            -100
        );
    }

    /**
     * @param MvcEvent $event
     * @return void|mixed
     */
    public function onRoute(MvcEvent $event)
    {
        $request = $event->getRequest();
        if (!$request instanceof Request)
            return; //Not a HTTP request

        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch)
            return;

        $controller = $routeMatch->getParam('controller');
        if (!$controller || !isset($this->allowedMethods[$controller]))
            return;

        $allowedMethods = $this->allowedMethods[$controller];
        $response = $event->getResponse(); /* @var $response Response */

        if ($request->getMethod() == Request::METHOD_OPTIONS)
        {
            $response->getHeaders()->addHeaderLine('Allow', implode(', ', $allowedMethods));
            return $response;
        }
        else if (!in_array($request->getMethod(), $allowedMethods))
        {
            $response->setStatusCode(Response::STATUS_CODE_405);
            $response->getHeaders()->addHeaderLine('Allow', implode(', ', $allowedMethods));
            $result = new ApiProblemResponse(new MethodNotAllowedProblem());
            $event->setResult($result);
            return $result;
        }
    }
}