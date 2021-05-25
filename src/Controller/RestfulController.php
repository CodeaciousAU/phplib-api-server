<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller;

use Codeacious\Model\ValidationError;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\Mvc\MvcEvent;
use Nocarrier\Hal;

/**
 * @method \Laminas\Http\Request getRequest()
 * @method \Laminas\Http\Response getResponse()
 * @method \Codeacious\ApiServer\Rest\ModelService restModel()
 * @method \Nocarrier\Hal toHal(array|object $value)
 * @method \Codeacious\ApiServer\HalCollectionBuilder halCollectionBuilder(string $name, int $totalItems)
 * @method ValidationError[] validationErrorsWithContext(ValidationError[] $errors, string $context)
 * @method mixed bodyParam(string $param, mixed $default=null)
 * @method array bodyParams()
 */
abstract class RestfulController extends AbstractRestfulController
{
    /**
     * {@inheritdoc}
     */
    public function onDispatch(MvcEvent $e)
    {
        $result = parent::onDispatch($e);

        if ($e->getRouteMatch()->getParam('action') == 'create' && $result instanceof Hal)
        {
            $this->getResponse()
                ->setStatusCode(self::HTTP_CREATED)
                ->getHeaders()->addHeaderLine('Location: '.$result->getUri());
        }

        return $result;
    }

    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;

    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_CONFLICT = 409;
    const HTTP_UNPROCESSABLE_ENTITY = 422;

    const HTTP_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_SERVICE_UNAVAILABLE = 503;
}