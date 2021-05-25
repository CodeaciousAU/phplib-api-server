<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Rest;

use Codeacious\Acl\Exception\InvalidStateException;
use Codeacious\ApiServer\Exception\DataValidationException;
use Codeacious\Model\ValidationError;
use Codeacious\Model\Validator\KeyValueValidator;
use Codeacious\Security\SecurityService;
use Codeacious\Security\User;

/**
 * A component which converts between serializable REST models and domain model objects.
 */
abstract class ModelMapper
{
    /**
     * @var ModelService
     */
    protected $modelService;

    /**
     * @var SecurityService
     */
    protected $securityService;
    

    /**
     * @param ModelService $modelService
     * @return $this
     */
    public function setModelService($modelService)
    {
        $this->modelService = $modelService;
        return $this;
    }

    /**
     * @param SecurityService $securityService
     * @return $this
     */
    public function setSecurityService($securityService)
    {
        $this->securityService = $securityService;
        return $this;
    }

    /**
     * @param object $sourceModel A domain model
     * @return object A serializable REST representation of the domain model
     */
    public abstract function createRestModel($sourceModel);

    /**
     * @param array $routeParams Parameters identifying the parent of the new model
     * @param array $restModel User-supplied properties
     * @return object A new domain model
     * @throws DataValidationException
     */
    public abstract function createDomainModel(array $routeParams, $restModel);

    /**
     * @param object $domainModel An existing domain model
     * @param array $restModel User-supplied set of properties to change on the domain model
     * @return void
     * @throws DataValidationException
     */
    public abstract function updateDomainModel($domainModel, $restModel);

    /**
     * @param array $routeParams The parameters required to uniquely identify a model instance
     * @param bool $allowDeleted Whether soft-deleted objects may be returned
     * @return object|null The associated domain model, or null if not found
     */
    public abstract function loadDomainModel(array $routeParams, $allowDeleted=false);

    /**
     * @return User|null
     */
    protected function currentUser()
    {
        return $this->securityService->getCurrentUser();
    }

    /**
     * {@see \Codeacious\Acl\Principal\SecurityPrincipalInterface}
     *
     * @param string $permission One of the \Application\Acl::PERMISSION_ constants
     * @param \Laminas\Permissions\Acl\Resource\ResourceInterface|string $resource
     * @return boolean
     */
    protected function hasPermission($permission, $resource = null)
    {
        try
        {
            return $this->securityService->getCurrentPrincipal()
                ->hasPermission($permission, $resource);
        }
        catch (InvalidStateException $e)
        {
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array $data
     * @param array $existingData
     * @param array $readOnlyKeys
     * @param array $readWriteKeys
     * @return array The data with any read-only keys removed
     * @throws DataValidationException
     */
    protected function validateAndFilterKeys(array $data, array $existingData, array $readOnlyKeys,
                                             array $readWriteKeys)
    {
        $errors = [];
        if (!empty($readOnlyKeys))
        {
            foreach ($readOnlyKeys as $key)
            {
                if (!array_key_exists($key, $data))
                    continue;
                if ((!array_key_exists($key, $existingData) && $data[$key] !== null)
                    || (array_key_exists($key, $existingData) && $data[$key] !== $existingData[$key]))
                {
                    $errors[] = ValidationError::conflicting('This property can\'t be modified',
                        $key);
                }
                unset($data[$key]);
            }
        }

        $errors = array_merge($errors, KeyValueValidator::getErrors($data, $readWriteKeys));
        if (!empty($errors))
            throw new DataValidationException($errors);

        return $data;
    }

    /**
     * @param array $data
     * @param array $validLinkKeys Keys are link names, values are domain model classes
     * @param array $validMultiLinkKeys Keys are link names, values are domain model classes
     * @return array Keys are link names, values are domain model objects (or null)
     * @throws DataValidationException
     */
    protected function resolveRestfulLinks(array $data, array $validLinkKeys,
                                           array $validMultiLinkKeys = [])
    {
        if (!array_key_exists('_links', $data))
            return [];

        $links = $data['_links'];
        if (!is_array($links))
        {
            throw new DataValidationException([
                ValidationError::invalid('Incorrect type (expected object)', '_links')
            ]);
        }

        $errors = [];
        $models = [];
        foreach ($links as $key => $val)
        {
            if (array_key_exists($key, $validLinkKeys))
            {
                if ($val === null || (is_array($val) && empty($val)))
                {
                    $models[$key] = null;
                    continue;
                }
                if (is_array($val) && array_key_exists(0, $val) && count($val) == 1)
                    $val = $val[0]; //Treat it as an array of links and take the first one.
                if (!is_array($val) || !array_key_exists('href', $val))
                {
                    $errors[] = ValidationError::invalid('Link must be null, or must contain a href',
                        '_links:'.$key);
                    continue;
                }
                $url = $val['href'];
                if ($url === null)
                {
                    $models[$key] = null;
                    continue;
                }
                $model = $this->modelService->resolveLink($url);
                if (!$model || !($model instanceof $validLinkKeys[$key]))
                {
                    $errors[] = ValidationError::invalid('Invalid '.$key.' link URL',
                        '_links:'.$key.':href');
                    continue;
                }
                $models[$key] = $model;
            }
            else if (array_key_exists($key, $validMultiLinkKeys))
            {
                if (!is_array($val)
                    || !empty(array_filter($val, fn ($key) => !is_int($key), ARRAY_FILTER_USE_KEY)))
                {
                    $errors[] = ValidationError::invalid('Expected an array of links',
                        '_links:'.$key);
                    continue;
                }
                $models[$key] = [];
                foreach ($val as $index => $link)
                {
                    if (!is_array($link)
                        || !array_key_exists('href', $link)
                        || !is_string($link['href']))
                    {
                        $errors[] = ValidationError::invalid('Link must be null, or must contain a '
                            .'href', '_links:'.$key.':'.$index);
                        continue;
                    }
                    $url = $link['href'];
                    $model = $this->modelService->resolveLink($url);
                    if (!$model || !($model instanceof $validMultiLinkKeys[$key]))
                    {
                        $errors[] = ValidationError::invalid('Invalid '.$key.' link URL',
                            '_links:'.$key.':'.$index.':href');
                        continue;
                    }
                    $models[$key][] = $model;
                }
            }
            else if ($key !== 'self')
            {
                $errors[] = ValidationError::unrecognized('Unrecognized property', '_links:'.$key);
            }
        }

        if (!empty($errors))
            throw new DataValidationException($errors);

        return $models;
    }
}