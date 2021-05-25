<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Controller;

use Codeacious\Filesystem\Directory;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;

/**
 * @method \Laminas\Http\Request getRequest()
 * @method \Laminas\Http\Response getResponse()
 */
class SpecController extends AbstractActionController
{
    /**
     * @var string
     */
    private $specDir;

    /**
     * @var string
     */
    private $specFilename;


    /**
     * @param string $specDir
     * @param string $specFilename
     */
    public function __construct($specDir=self::SPEC_DIR, $specFilename=self::SPEC_FILENAME)
    {
        $this->specDir = $specDir;
        $this->specFilename = $specFilename;
    }

    /**
     * @param MvcEvent $e
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $module = $this->params()->fromRoute('module');
        $version = $this->params()->fromRoute('version');

        $result = null;
        $specDir = new Directory($this->specDir);
        if ($specDir->exists() && !empty($module))
        {
            $moduleDir = $specDir->getChild($module);
            if ($moduleDir && $moduleDir instanceof Directory)
                $result = $this->serveSpec($module, $version);
        }

        if (!$result)
            $result = $this->notFoundAction();

        $e->setResult($result);
        return $result;
    }

    /**
     * @param string $apiModule
     * @param string|null $version
     * @return \Laminas\Http\Response|null
     */
    private function serveSpec($apiModule, $version=null)
    {
        //Enumerate the available versions
        $dir = new Directory($this->specDir.'/'.$apiModule);
        $versions = [];
        foreach ($dir as $item)
        {
            if ($item instanceof Directory && preg_match('/^v[0-9]+$/', $item->getName()))
                $versions[] = intval(substr($item->getName(), 1));
        }
        if (empty($versions))
            return null;

        //Select the appropriate version
        if ($version !== null)
        {
            $version = intval($version);
            if (array_search($version, $versions) === false)
                return null;
        }
        else
        {
            sort($versions);
            $version = array_pop($versions);
        }

        //Find the spec file
        $dir = $dir->getSubdirectory('v'.$version);
        $file = $dir->getFile($this->specFilename);
        if (!$file)
            return null;

        //Serve the file
        $response = $this->getResponse();
        $response->setContent($file->getContents());
        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'application/json')
            ->addHeaderLine('Content-Length', $file->getSize());
        return $response;
    }

    const SPEC_DIR = 'generated/openapi';
    const SPEC_FILENAME = 'openapi.json';
}