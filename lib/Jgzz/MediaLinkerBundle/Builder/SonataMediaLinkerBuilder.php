<?php
namespace Jgzz\MediaLinkerBundle\Builder;

use Jgzz\MediaLinkerBundle\Linker\SonataMediaLinker;
use Doctrine\Common\Persistence\ObjectManager;

/**
* Builder for relations Sonata managed entities
*/
class SonataMediaLinkerBuilder extends SonataLinkerBuilder
{

    /**
     * Creates a Linker for the given classes. This classes are needed to have an Admin
     *
     * @param string $name
     * @param string $hostclass
     * @param string $linkedclass
     * @param ObjectManager $om
     * @param array $options
     * @return \Jgzz\MediaLinkerBundle\Linker\SonataMediaLinker
     * @throws \OutOfBoundsException
     */
    public function buildLinker($name, $hostclass, $linkedclass, ObjectManager $om, $options = array())
	{
		if (!$hostAdmin = $this->adminPool->getAdminByClass($hostclass)){
			throw new \OutOfBoundsException(sprintf("Host class %s doesn't have a related Admin service", $hostclass));
		}

		if (!$linkedAdmin = $this->adminPool->getAdminByClass($linkedclass)){
			throw new \OutOfBoundsException(sprintf("Linked class %s doesn't have a related Admin service", $linkedclass));
		}

		$linker = new SonataMediaLinker($name, $hostclass, $linkedclass, $om);

		$linker->setAdmins($hostAdmin, $linkedAdmin);

		$linker->setContext($options['fetcherOptions']['context']);
		$linker->setProvider($options['fetcherOptions']['provider']);

		return $linker;
	}
}