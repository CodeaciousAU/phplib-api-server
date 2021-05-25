<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Problem;

use Codeacious\ApiServer\Controller\RestfulController;
use Laminas\ApiTools\ApiProblem\ApiProblem;

class MethodNotAllowedProblem extends ApiProblem
{
    /**
     * @param string $message
     */
    public function __construct($message='That HTTP method is not applicable to this resource.')
    {
        parent::__construct(RestfulController::HTTP_METHOD_NOT_ALLOWED, $message);
    }
}