<?php
namespace Jgzz\MediaLinkerBundle;

use Doctrine\Common\Persistence\ObjectManager;
use Sonata\AdminBundle\Admin\Pool;

use Jgzz\MediaLinkerBundle\Builder\BuilderFactory;
use Jgzz\MediaLinkerBundle\Linker\Linker;
use Jgzz\MediaLinkerBundle\Candidate\CandidateFetcherFactory;
use Jgzz\MediaLinkerBundle\Actions\LinkerActionsInterface;

/**
* Central service for the bundle. Ties together building linkers and retrieving candidates
* 
* @todo: link by association name instead of linkedclass (might be serveral associations to the same class)
*/
class LinkerManager
{

	private $objectManager;

	private $linkerConfigs = array();

	private $linkerPool = array();

	private $builderFactory;

	private $candidateFetcherPool;

	function __construct(ObjectManager $objectManager, BuilderFactory $builderFactory, CandidateFetcherFactory $candidateFetcherPool)
	{
		$this->om = $objectManager;

		$this->builderFactory = $builderFactory;

		$this->candidateFetcherPool = $candidateFetcherPool;
	}

	/**
	 * Adds one linker configuration to the pool of linker handlers
	 */
	public function addLinkerConfig($name, $hostclass, $linkedclass, $builderName, $fetcherName, $fetcherOptions, $options = array())
	{
		$options['hostclass'] = $hostclass;
		$options['linkedclass'] = $linkedclass;
		$options['builder'] = $builderName;
		$options['fetcherName'] = $fetcherName;
		$options['fetcherOptions'] = $fetcherOptions;

		$this->linkerConfigs[$name] = $options;
	}

	/**
	 * Gets Linker based on its name
	 * 
	 * @param  string $name
	 * @return Linker
	 */
	public function getLinker($name)
	{
		if(!array_key_exists($name, $this->linkerPool)){
			$this->linkerPool[$name] = $this->buildLinker($name);
		}

		return $this->linkerPool[$name];
	}

	/**
	 * Builds a Linker based on its current configuration
	 * 
	 * @param  string $name
	 * @return Linker
	 */
	protected function buildLinker($name)
	{
		$config = $this->linkerConfigs[$name];

		$builder = $this->builderFactory->get($config['builder']);

		$hostclass = $config['hostclass'];
		$linkedclass = $config['linkedclass'];

		unset($config['hostclass']);
		unset($config['linkedclass']);

		return $builder->buildLinker($name, $hostclass, $linkedclass, $this->om, $config);
	}

	public function getCandidateFetcher(Linker $linker)
	{
		$config =  $this->linkerConfigs[$linker->getName()];

		return $this->candidateFetcherPool->get($linker, $config);
	}

	public function getLinkerRowTemplate(Linker $linker)
	{
		$config = $this->getLinkerConfig($linker);

		return $config['row_template'];
	}

	public function getLinkerConfig(Linker $linker)
	{
		return $this->linkerConfigs[$linker->getName()];
	}


	public function getLinkerActionExtension(Linker $linker)
	{
		$config = $this->getLinkerConfig($linker);

        $extension_class = $config['action_extension_class'];

        if(!$extension_class){
        	return null;
        }

        // todo: factory or dic (inject other services)
        $extension = new $extension_class;

        if(!($extension instanceof LinkerActionsInterface)){
        	throw new \Exception(sprintf("Class %s should implement LinkerActionsInterface"));
        }

        return $extension;
	}

}