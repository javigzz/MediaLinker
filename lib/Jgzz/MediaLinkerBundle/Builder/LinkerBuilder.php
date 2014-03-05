<?php
namespace Jgzz\MediaLinkerBundle\Builder;

use Doctrine\Common\Persistence\ObjectManager;

/**
* Base LinkerBuilder.
*
* A LinkerBuilder is an object that creates and sets up a Linker.
*/
abstract class LinkerBuilder
{
	abstract public function buildLinker($name, $hostclass, $linkedclass, ObjectManager $om, $options = array());
	
}