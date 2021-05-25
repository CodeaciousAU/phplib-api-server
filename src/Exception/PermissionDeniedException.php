<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Exception;

use Codeacious\ApiServer\Controller\RestfulController;
use Laminas\ApiTools\ApiProblem\Exception\ProblemExceptionInterface;

/**
 * Captures the same information as a PermissionDeniedProblem, except in a throwable format.
 */
class PermissionDeniedException extends \DomainException implements ProblemExceptionInterface
{
    /**
     * @param string $message
     */
    public function __construct($message='You do not have permission to make this request.')
    {
        parent::__construct($message, RestfulController::HTTP_FORBIDDEN);
    }

    /**
     * @return null|array|\Traversable
     */
    public function getAdditionalDetails()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return null;
    }
}