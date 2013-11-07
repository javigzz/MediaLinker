<?php
namespace Jgzz\MediaLinkerBundle\Linker;

use Sonata\AdminBundle\Admin\Admin;

/**
* Linker coupled with Sonata Admin classes for entity mappings ...
*/
class SonataLinker extends Linker
{
	
	protected $hostAdmin;

	protected $linkedAdmin;

    public function setAdmins(Admin $hostAdmin, Admin $linkedAdmin)
    {
    	$this->hostAdmin = $hostAdmin;

    	$this->linkedAdmin = $linkedAdmin;
    }

    public function getHostAdmin()
    {
    	return $this->hostAdmin;
    }
    
    public function getLinkedAdmin()
    {
    	return $this->linkedAdmin;
    }

}