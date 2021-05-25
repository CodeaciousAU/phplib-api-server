<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\View;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Response;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\ViewEvent;

class HalJsonStrategy extends AbstractListenerAggregate
{
    /**
     * @var HalJsonRenderer
     */
    private $renderer;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->renderer = new HalJsonRenderer();
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'selectRenderer'],
            200);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'injectResponse'],
            200);
    }

    /**
     * @param ViewEvent $e
     * @return RendererInterface
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        if (! $model instanceof HalJsonModel)
            return null;

        return $this->renderer;
    }

    /**
     * @param ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer)
            return;

        $result = $e->getResult();
        if (!is_string($result))
            return;

        /* @var Response $response */
        $response = $e->getResponse();

        $response->setContent($result);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('content-type', 'application/hal+json');
    }
}