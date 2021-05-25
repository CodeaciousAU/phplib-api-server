<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */
namespace Codeacious\ApiServer\Problem;

use Codeacious\ApiServer\Controller\RestfulController;
use Codeacious\Model\ValidationError;
use Laminas\ApiTools\ApiProblem\ApiProblem;

/**
 * Container for a set of field validation errors. Allows the problem to be returned in a REST
 * response, in a structured format.
 */
class DataValidationProblem extends ApiProblem
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
        
        //We aim to be consistent with the problem responses produced by
        //\Laminas\ApiTools\ContentValidation\ContentValidationListener
        parent::__construct(RestfulController::HTTP_UNPROCESSABLE_ENTITY, 'Failed Validation', null,
            null, $this->getErrorDetails());
    }
    
    /**
     * @return ValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * @return array
     */
    public function getErrorDetails()
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
}
