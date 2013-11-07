<?php
namespace Jgzz\MediaLinkerBundle\Linker;

use Sonata\AdminBundle\Admin\Admin;
use Doctrine\Common\Collections\ArrayCollection;

/**
* Linker coupled with Sonata Admin classes for entity mappings ...
*/
class SonataMediaLinker extends SonataLinker
{

    protected $context;

    protected $provider;

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Overrides for filtering by context and provider
     */
    public function getAllLinkedEntities($hostEntity)
    {
        $all = parent::getAllLinkedEntities($hostEntity);

        $context = $this->context;
        $provider = $this->provider;

        $filtered = array_filter($all->toArray(), function($i) use ($context, $provider){
            return ($context == $i->getContext() && $provider == $i->getProviderName());
        });

        return new ArrayCollection($filtered);
    }
}