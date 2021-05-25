<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Codeacious\ApiServer\Problem\DataValidationProblem;
use Codeacious\ApiServer\View\HalJsonModel;
use Codeacious\Model\ValidationError;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Nocarrier\Hal;

/**
 * Event listener which makes it possible for API controllers to return several convenient types of
 * object directly from an action method.
 *
 * The listener intercepts these results and converts them to ViewModel or Response objects as
 * expected by the rest of the MVC framework.
 */
class TransformResultListener extends AbstractListenerAggregate
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
            MvcEvent::EVENT_DISPATCH,
            array($this, 'onPostDispatch'),
            -10
        );
    }

    /**
     * @param MvcEvent $e
     * @return mixed
     */
    public function onPostDispatch(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Hal)
            $result = new HalJsonModel($result);
        else if ($result instanceof ApiProblem)
            $result = new ApiProblemResponse($result);
        else if ($result instanceof ValidationError)
            $result = new ApiProblemResponse(new DataValidationProblem([$result]));

        $e->setResult($result);
        return $result;
    }
}