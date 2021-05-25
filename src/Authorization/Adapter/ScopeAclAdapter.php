<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Authorization\Adapter;

use Codeacious\Acl\Principal\Principal;
use Codeacious\Security\Authentication\Assertion;
use Codeacious\Security\Authorization\Adapter\AdapterInterface;
use Laminas\Mvc\MvcEvent;

class ScopeAclAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $scopeAcl;


    public function __construct(array $scopeAcl)
    {
        $this->scopeAcl = $scopeAcl;
    }

    function isAuthorized(Principal $principal, MvcEvent $event): bool
    {
        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch)
            return true;

        $identity = $event->getParam('AuthenticationIdentity');
        if (! $identity instanceof Assertion)
            return true;

        $routeName = $routeMatch->getMatchedRouteName();
        foreach ($identity->getScopes() as $scope)
        {
            if (!isset($this->scopeAcl[$scope]['routes']))
                continue;
            if (array_search($routeName, $this->scopeAcl[$scope]['routes']) !== false)
                return true;
        }
        return false;
    }
}