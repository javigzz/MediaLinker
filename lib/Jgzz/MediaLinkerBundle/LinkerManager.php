<?php
namespace Jgzz\MediaLinkerBundle;

use Doctrine\Common\Persistence\ObjectManager;
use Sonata\AdminBundle\Admin\Pool;

use Jgzz\MediaLinkerBundle\Linker\Linker;
use Jgzz\MediaLinkerBundle\Candidate\CandidateFetcherInterface;
use Jgzz\MediaLinkerBundle\Actions\LinkerActionsInterface;

/**
* 
* TODO: link by association name instead of linkedclass
*/
class LinkerManager
{

	private $objectManager;

	private $adminPool;

	private $linkerConfigs = array();

	private $linkerPool = array();

	private $fetcherPool = array();

	function __construct(ObjectManager $objectManager, Pool $adminPool)
	{
		$this->om = $objectManager;

		// TODO: llevarlo a injección en sonatalinker directamente
		// linkerManager no debe tener noción de adminPool
		$this->adminPool = $adminPool;
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

		// builder may be different for one Linker
		$builder = $this->getBuilder($config['builder']);

		$hostclass = $config['hostclass'];
		$linkedclass = $config['linkedclass'];

		unset($config['hostclass']);
		unset($config['linkedclass']);

		return $builder->buildLinker($name, $hostclass, $linkedclass, $this->om, $config);
	}

	/**
	 * Gets builder by service name
	 * TODO: builder factory ... inject builder service
	 * 
	 * @param  [type] $builderName [description]
	 * @return [type]              [description]
	 */
	protected function getBuilder($builderName)
	{
		if($builderName == 'sonata.builder'){
			$builder = new \Jgzz\MediaLinkerBundle\Builder\SonataLinkerBuilder();
			$builder->setAdminPool($this->adminPool);
		} elseif ($builderName == 'sonatamedia.builder'){
			$builder = new \Jgzz\MediaLinkerBundle\Builder\SonataMediaLinkerBuilder();
			$builder->setAdminPool($this->adminPool);
		} else {
			throw new \Exception("not implemented builder ". $builderName, 1);
		}

		return $builder;
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

	/**
	 * Candidate fetcher suited for $linker
	 * 
	 * @param  Linker $linker
	 * @return CandidateFetcherInterface
	 */
	public function getLinkerCandidateFetcher(Linker $linker)
	{
		$linkername = $linker->getName();

		if(array_key_exists($linkername, $this->fetcherPool)){
			return $this->fetcherPool[$linkername];
		}
		
		$config = $this->linkerConfigs[$linkername];

		$fetcherName = $config['fetcherName'];

		// TODO: fetcher factory...
		// tag custom fetchers a linkermanager
		
		if($fetcherName == 'doctrine'){

			$fetcher = new \Jgzz\MediaLinkerBundle\Candidate\DoctrineCandidateFetcher();

		} else if($fetcherName == 'sonatamedia'){

			$fetcher = new \Jgzz\MediaLinkerBundle\Candidate\SonataMediaCandidateFetcher();

		} else {
			throw new \Exception("not implemented candidate fetcher ". $fetcherName, 1);
			
		}

		$fetcherOptions = $config['fetcherOptions'];

		if(!empty($fetcherOptions)){
			$fetcher->setDefaultOptions($fetcherOptions);
		}

		$this->fetcherPool[$linkername] = $fetcher;

		return $fetcher;
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