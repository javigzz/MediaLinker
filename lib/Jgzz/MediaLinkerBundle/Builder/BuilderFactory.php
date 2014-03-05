<?php
namespace Jgzz\MediaLinkerBundle\Builder;

use Symfony\Component\DependencyInjection\Container;

/**
 * Factory for LinkerBuilders.
 * Basically a wrapper to the DIC
 */
class BuilderFactory {

	/**
	 * Dependency Inyection Container
	 * 
	 * @var Container
	 */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Returns a LinkerBuilder by its serviceName
	 * 
	 * @param  string $builderName		Name of the builder. May be a service name implementing
	 * @return Jgzz\MediaLinkerBundle\Builder
	 */
	public function get($builderName)
	{
		if (in_array($builderName, array('sonata.builder', 'sonatamedia.builder'))) {
			$serviceName = 'jgzz.medialinker.' . $builderName;
		}

		return $this->container->get($serviceName);
	}

}