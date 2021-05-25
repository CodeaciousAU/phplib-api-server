<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Listener;

use Codeacious\ApiServer\Problem\WrappedProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\ResponseSender\SendResponseEvent;

/**
 * Event listener which ensures that exceptions returned via the ApiProblem mechanism do not expose
 * their internal details to the user unless view_manager:display_exceptions is enabled.
 */
class ExceptionObfuscationListener extends AbstractListenerAggregate
{
    /**
     * @var bool
     */
    private $displayExceptions;


    public function __construct(bool $displayExceptions)
    {
        $this->displayExceptions = $displayExceptions;
    }

    /**
     * @param EventManagerInterface $events
     * @param int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority=1)
    {
        $this->listeners[] = $events->getSharedManager()->attach(
            '*',
            SendResponseEvent::EVENT_SEND_RESPONSE,
            array($this, 'onSendResponse'),
            0
        );
    }

    /**
     * @param SendResponseEvent $e
     * @return void
     */
    public function onSendResponse(SendResponseEvent $e)
    {
        $response = $e->getResponse();
        if ($response instanceof ApiProblemResponse)
        {
            $problem = $response->getApiProblem();
            $e->setResponse(
                new ApiProblemResponse(new WrappedProblem($problem))
            );
        }
    }
}