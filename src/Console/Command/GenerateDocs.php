<?php

namespace Codeacious\ApiServer\Console\Command;

use Codeacious\ApiServer\Controller\SpecController;
use Codeacious\Filesystem\Directory;
use OpenApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate OpenAPI specs from PHP annotations. Once generated, these spec files can be served with
 * SpecController.
 */
class GenerateDocs extends Command
{
    /**
     * @var string[]
     */
    protected $moduleNames;

    /**
     * @var string
     */
    protected $outputDir;


    /**
     * @param string[] $moduleNames
     * @param string $outputDir
     */
    public function __construct(array $moduleNames, $outputDir=SpecController::SPEC_DIR)
    {
        parent::__construct('generate-docs');
        $this->moduleNames = $moduleNames;
        $this->outputDir = $outputDir;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Generate REST API documentation in OpenAPI format');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Generate an OpenAPI document for each versioned API namespace in the project
        foreach ($this->moduleNames as $moduleName)
        {
            foreach (new Directory('module/'.$moduleName.'/src') as $item)
            {
                if ($item instanceof Directory && preg_match('/^V[0-9]+$/', $item->getName()))
                {
                    $version = intval(substr($item->getName(), 1));
                    $output->writeln('Generating docs for '.$item->getPath());
                    $docs = OpenApi\scan($item->getPath());

                    $targetFile = $this->outputPathForApiDocs($moduleName, $version);
                    if (!file_exists(dirname($targetFile)))
                    {
                        if (!mkdir(dirname($targetFile), 0755, true))
                            throw new \RuntimeException('Failed to create output directory');
                    }
                    $docs->saveAs($targetFile);
                    $output->writeln('<fg=green>  => '.$targetFile.'</>');
                }
            }
        }
        return 0;
    }

    /**
     * @param string $moduleName
     * @param int $version
     * @return string
     */
    private function outputPathForApiDocs($moduleName, $version)
    {
        return $this->outputDir.'/'.$moduleName.'/v'.$version.'/openapi.json';
    }
}