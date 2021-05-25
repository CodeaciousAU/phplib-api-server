<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller\Plugin;

use Codeacious\Model\ValidationError;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Iterates through a set of validation errors, prepending each with the specified context string.
 * Returns a new set of validation errors.
 */
class ValidationErrorsWithContext extends AbstractPlugin
{
    /**
     * @param ValidationError[] $validationErrors
     * @param string $context
     * @return ValidationError[]
     */
    public function __invoke(array $validationErrors, $context)
    {
        $results = [];
        foreach ($validationErrors as $error)
        {
            $newContext = $context;
            if ($error->getContext())
                $newContext .= ':'.$error->getContext();
            $results[] = new ValidationError($error->getType(), $error->getMessage(), $newContext);
        }
        return $results;
    }
}