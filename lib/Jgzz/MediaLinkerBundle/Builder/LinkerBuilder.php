<?php
namespace Jgzz\MediaLinkerBundle\Builder;

use Doctrine\Common\Persistence\ObjectManager;

/**
* Base Linker Builder
*/
abstract class LinkerBuilder
{
	abstract public function buildLinker($name, $hostclass, $linkedclass, ObjectManager $om, $options = array());
	
}