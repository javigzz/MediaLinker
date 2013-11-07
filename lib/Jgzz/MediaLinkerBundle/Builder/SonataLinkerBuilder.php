<?php
namespace Jgzz\MediaLinkerBundle\Builder;

use Jgzz\MediaLinkerBundle\Linker\SonataLinker;
use Doctrine\Common\Persistence\ObjectManager;

/**
* Builder for relations Sonata managed entities
*/
class SonataLinkerBuilder extends LinkerBuilder
{
	protected $adminPool;

	public function setAdminPool($adminPool)
	{
		$this->adminPool = $adminPool;
	}
	
	/**
	 * Creates a Linker for the given classes. This classes are needed to have an Admin
	 * 
	 * @param  string $hostclass
	 * @param  string $linkedclass
	 * @return Jgzz\MediaLinkerBundle\Linker
	 */
	public function buildLinker($name, $hostclass, $linkedclass, ObjectManager $om, $options = array())
	{
		if (!$hostAdmin = $this->adminPool->getAdminByClass($hostclass)){
			throw new \OutOfBoundsException(sprintf("Host class %s doesn't have a related Admin service", $hostclass));
		}

		if (!$linkedAdmin = $this->adminPool->getAdminByClass($linkedclass)){
			throw new \OutOfBoundsException(sprintf("Linked class %s doesn't have a related Admin service", $linkedclass));
		}

		$linker = new SonataLinker($name, $hostclass, $linkedclass, $om);

		$linker->setAdmins($hostAdmin, $linkedAdmin);

		return $linker;
	}
}