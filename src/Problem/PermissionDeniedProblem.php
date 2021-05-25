<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Problem;

use Codeacious\ApiServer\Controller\RestfulController;
use Laminas\ApiTools\ApiProblem\ApiProblem;

class PermissionDeniedProblem extends ApiProblem
{
    /**
     * @param string $message
     */
    public function __construct($message='You do not have permission to make this request.')
    {
        parent::__construct(RestfulController::HTTP_FORBIDDEN, $message);
    }
}