<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\Http\Header;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * @method \Laminas\Http\Request getRequest()
 * @method \Laminas\Http\Response getResponse()
 * @method \Laminas\Authentication\Result|null authResult()
 */
class ErrorController extends AbstractActionController
{
    /**
     * @return mixed
     */
    public function unauthenticatedAction()
    {
        $authResult = $this->authResult();
        $messages = [];
        if ($authResult && !$authResult->isValid())
            $messages = $authResult->getMessages();

        if ($this->getResponse()->isOk())
        {
            $statusCode = 401;
            $this->getResponse()->getHeaders()
                ->addHeader(new Header\WWWAuthenticate('Bearer realm="Service"'));
        }
        else
            $statusCode = $this->getResponse()->getStatusCode();

        return new ApiProblem(
            $statusCode,
            empty($messages) ? 'Not authorized' : implode(PHP_EOL, $messages)
        );
    }

    /**
     * @return mixed
     */
    public function insufficientScopeAction()
    {
        $statusCode = 401;
        if (!$this->getResponse()->isOk())
            $statusCode = $this->getResponse()->getStatusCode();

        return new ApiProblem(
            $statusCode,
            'Access token scope is insufficient for this request'
        );
    }
}