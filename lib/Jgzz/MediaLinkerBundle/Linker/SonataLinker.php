<?php
namespace Jgzz\MediaLinkerBundle\Linker;

use Sonata\AdminBundle\Admin\Admin;

/**
* Linker for connecting two entities managed by their 
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