<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Rest;

use Codeacious\Mapper\ModelUriProvider;

/**
 * Base class for serializable REST models.
 */
abstract class Model implements ModelUriProvider
{
    /**
     * @var string
     */
    private $uri;


    /**
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Export the model as a publishable array.
     *
     * @return array
     */
    public abstract function getArrayCopy();
}