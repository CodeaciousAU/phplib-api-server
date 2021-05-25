<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer;

use Laminas\Router\RouteStackInterface;

/**
 * Glue between the Mapper library and the URL routing system.
 */
class UrlGenerator implements \Codeacious\Mapper\UrlGenerator
{
    /**
     * @var RouteStackInterface
     */
    private $router;

    /**
     * @var array
     */
    private $extraRouteParams;


    /**
     * @param RouteStackInterface $router
     * @param array $extraRouteParams
     */
    public function __construct(RouteStackInterface $router, $extraRouteParams = [])
    {
        $this->router = $router;
        $this->extraRouteParams = $extraRouteParams;
    }

    /**
     * Get the URL to a named route.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        $parameters += $this->extraRouteParams;
        return $this->router->assemble($parameters, [
            'name' => $name,
            'force_canonical' => $absolute
        ]);
    }
}