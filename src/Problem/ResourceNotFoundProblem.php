<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Problem;

use Codeacious\ApiServer\Controller\RestfulController;
use Laminas\ApiTools\ApiProblem\ApiProblem;

class ResourceNotFoundProblem extends ApiProblem
{
    /**
     * @param string $message
     */
    public function __construct($message='The requested resource could not be found.')
    {
        parent::__construct(RestfulController::HTTP_NOT_FOUND, $message);
    }
}