<?php
namespace Jgzz\MediaLinkerBundle\Linker;

use Sonata\AdminBundle\Admin\Admin;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\MediaBundle\Model\Media;

/**
* Linker coupled with Sonata Media linked entities
*/
class SonataMediaLinker extends SonataLinker
{
    /**
     * @var string
     */
    protected $context;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Overrides for filtering by context and provider
     *
     * @param mixed $hostEntity
     * @return ArrayCollection|mixed
     */
    public function getAllLinkedEntities($hostEntity)
    {
        $all = parent::getAllLinkedEntities($hostEntity);

        $context = $this->context;
        $provider = $this->provider;

        $filtered = array_filter($all->toArray(), function($i) use ($context, $provider){
            /** @var Media $i */
            return ($context == $i->getContext() && $provider == $i->getProviderName());
        });

        // order by name ascending
        usort($filtered, function ($a, $b) {
            /** @var Media $a */
            /** @var Media $b */
            return strtolower($b->getName()) < strtolower($a->getName());
        });

        return new ArrayCollection($filtered);
    }
}