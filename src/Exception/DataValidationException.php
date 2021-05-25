<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Exception;

use Codeacious\ApiServer\Controller\RestfulController;
use Codeacious\Model\ValidationError;
use Laminas\ApiTools\ApiProblem\Exception\ProblemExceptionInterface;

/**
 * Captures the same information as a DataValidationProblem, except in a throwable format.
 */
class DataValidationException extends \DomainException implements ProblemExceptionInterface
{
    /**
     * @var ValidationError[]
     */
    protected $errors;


    /**
     * @param ValidationError[] $errors
     */
    public function __construct(array $errors=array())
    {
        $this->errors = $errors;
        parent::__construct('Failed Validation', RestfulController::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return ValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return null|array|\Traversable
     */
    public function getAdditionalDetails()
    {
        $result = array('validation_messages' => array());
        foreach ($this->errors as $error)
        {
            $context = $error->getContext();
            if (empty($context))
                $context = 'global';

            if (!isset($result['validation_messages'][$context]))
                $result['validation_messages'][$context] = array();

            $result['validation_messages'][$context][$error->getType()] = $error->getMessage();
        }
        return $result;
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