<?php
namespace Jgzz\MediaLinkerBundle\Linker;

use Doctrine\Common\Persistence\ObjectManager;
use Jgzz\MediaLinkerBundle\Candidate\CandidateFetcherInterface;

/**
 * Holds the mapping info to link two entities. Performs linkages among entites
 */
class Linker 
{
	const SIDE_HOST = 'host';

	const SIDE_LINKED = 'linked';

    protected $name;

	protected $hostClass;

	protected $linkedClass;

    protected $hostMapping;

    protected $linkedMapping;

    protected $om;

    public function __construct($name, $hostClass, $linkedClass, ObjectManager $om)
    {
        $this->name = $name;
        
        $this->hostClass = $hostClass;

        $this->linkedClass = $linkedClass;

        $this->om = $om;
    }

    /**
     * Sets the relation between the two entities
     * 
     * @param  object $linkedEntity
     * @param  object $hostEntity
     */
    public function linkToHost($linkedEntity, $hostEntity)
    {
        $mapping_from_linked = $this->getLinkedMapping();

        // guess setter and sets
        
        // set host for linked entity
        $host_setter_prefix = ($mapping_from_linked['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) 
            ? 'add_'
            : 'set_';

        $host_setter = $this->guesserFactory(self::SIDE_LINKED, $host_setter_prefix);
        call_user_func_array(array($linkedEntity, $host_setter), array($hostEntity));

        // if many to many, host class must also have a setter for linked entity
        if ($mapping_from_linked['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
            call_user_func_array(array($hostEntity, $this->guessSetter(self::SIDE_HOST)), array($linkedEntity));
        }
    }

    /**
     * Unlinks the linked entity and the host
     * 
     * @param  object $linkedEntity
     * @param  object $hostEntity
     */
    public function unlinkFromHost($linkedEntity, $hostEntity)
    {
    	// guess setter and sets
        call_user_func_array(array($linkedEntity, $this->guessRemover(self::SIDE_LINKED)), array($hostEntity));

    	$mapping_from_linked = $this->getLinkedMapping();

    	// if many to many, host class must also have a remover for linked entity
    	if ($mapping_from_linked['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
    		call_user_func_array(array($hostEntity, $this->guessRemover(self::SIDE_HOST)), array($linkedEntity));
    	}
    }

    public function getName()
    {
        return $this->name;
    }

    public function getHostClass()
    {
        return $this->hostClass;
    }

    public function getLinkedClass()
    {
        return $this->linkedClass;
    }

    /**
     * Collection of entities linked to the host
     * 
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLinkedEntities($hostEntity)
    {
        return $this->getAllLinkedEntities($hostEntity);
    }

    protected function getAllLinkedEntities($hostEntity)
    {
        $getter = $this->guesserFactory(Linker::SIDE_HOST, 'get_');
        return call_user_func(array($hostEntity, $getter));
    }

    /**
     *
     * 
     * @param  [type] $linkedEntity [description]
     * @return mixed
     */
    public function getHostEntities($linkedEntity)
    {
        $getter = $this->guesserFactory(Linker::SIDE_LINKED, 'get_');
    	return call_user_func(array($linkedEntity, $getter));
    }

    public function findHostById($id)
    {
    	return $this->hostRepository()->findOneBy(array('id'=>$id));
    }

    public function findLinkedById($id)
    {
    	return $this->linkedRepository()->findOneBy(array('id'=>$id));
    }

    /**
     * Mapping info of the relation as seen from the host entity
     * 
     * @return array
     */
    public function getHostMapping()
    {
    	if(!isset($this->hostMapping)){
    		$this->hostMapping = $this->getMapping($this->hostClass, $this->linkedClass);
    	} 

    	return $this->hostMapping;
    }

    /**
     * Mapping info of the relation as seen from the linked entity
     * 
     * @return array
     */
    public function getLinkedMapping()
    {
    	if(!isset($this->linkedMapping)){
    		$this->linkedMapping = $this->getMapping($this->linkedClass, $this->hostClass);
    	} 

    	return $this->linkedMapping;
    }

    /**
     * Gets mapping meta information among two classes
     * 
     * @param  [type]        $class       [description]
     * @param  [type]        $classTarget [description]
     * @param  ObjectManager $em          [description]
     * @return [type]                     [description]
     */
    private function getMapping($class, $classTarget)
    {
    	$metadata = $this->om->getMetadataFactory()->getMetadataFor($class);

    	if (!$mappings = $metadata->getAssociationsByTargetClass($classTarget)){
    		throw new \OutOfBoundsException(sprintf("Class %s has not association mapping to %s", $class, $classTarget));
    	}

    	return reset($mappings);
    }


    /**
     * Guess the setter method depending on the side of the relation
     * 
     * @param  string $side
     * @return string
     */
    private function guessSetter($side)
    {
        return $this->guesserFactory($side, 'add_');
    }

    /**
     * Guess the remover method depending on the side of the relation
     * 
     * @param  string $side
     * @return string
     */
    private function guessRemover($side)
    {
        return $this->guesserFactory($side, 'remove_');
    }

    private function guesserFactory($side, $prefix)
    {
        $map = $side == self::SIDE_HOST ? $this->getHostMapping() : $map = $this->getLinkedMapping();
                
        return $this->to_camel_case($prefix.$map['fieldName']);
    }    


    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function hostRepository()
    {
        return $this->om->getRepository($this->hostClass);
    }
    
    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function linkedRepository()
    {
        return $this->om->getRepository($this->linkedClass);
    }

    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * 
     * @param    string   $str                     String in underscore format
     * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
     * @return   string
     */
    private function to_camel_case($str, $capitalise_first_char = false) {
        if($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        return preg_replace_callback('/_([a-z])/', function($c){ return strtoupper($c[1]); }, $str);
    }

}