<?php
namespace Jgzz\MediaLinkerBundle\Candidate;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Jgzz\MediaLinkerBundle\Linker\Linker;

/**
* Helps fetching entities elegible to be linked by a certain host entity
*/
class DoctrineCandidateFetcher implements CandidateFetcherInterface
{

	protected $options = array();

	public function setDefaultOptions(array $options)
	{
		$this->options = $options;
	}

	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Get candidates
	 * 
	 * @param  Linker $linker
	 * @param  integer $host_id
	 * @param  array $options
	 * @return Collection
	 */
	public function getCandidates(Linker $linker, $host_id, $options = array())
	{
		// TODO: use for 'options'. override global fetcher options or query specific?
		
		// $this->options = array_merge($this->options, $options);

		$qb = $this->createQueryBuilder($linker, $host_id);

		$result = $qb->getQuery()->getResult();

		return $result;
	}

	/**
	 * Query builder to fetch every entity of the 'linked' side that is not already linked
	 * to the host entity.
	 * 
	 * @param  Linker $linker
	 * @param  integer $host_id
	 * @param  integer $linked_id
	 * @return QueryBuilder
	 */
	protected function createQueryBuilder(Linker $linker, $host_id)
	{
		$repo = $linker->linkedRepository();


		$mapping = $linker->getLinkedMapping();

		$fieldName = $mapping['fieldName'];

		$already_linked_qb = $repo->createQueryBuilder('l');

		$already_linked_qb->select('l')
			->innerJoin('l.'.$fieldName, 'h')
			->where($already_linked_qb->expr()->eq('h.id',':host_id'))
			;

		$qb = $repo->createQueryBuilder('linked');
		$qb->select('linked','host')
			->leftJoin('linked.'.$fieldName, 'host')
			->where($qb->expr()->notIn('linked.id', $already_linked_qb->getDQL()))
			->setParameter('host_id',$host_id);
			;

		return $qb;
	}

	// protected function removeAlreadyLinkedToHost(Linker $linker, $result, $host_id)
	// {
	// 	$filteredRes = array();

	// 	foreach ($result as $result) {
	// 		$hosts_ids = $this->hostsIdsForLinkedEntity($linker, $result);
	// 		if (!in_array($host_id, $hosts_ids)) {
	// 			$filteredRes[] = $result;
	// 		}
	// 	}

	// 	return $filteredRes;
	// }

	// /**
	//  * @param  mixed $linkedEntity
	//  * @return array
	//  */
	// protected function hostsIdsForLinkedEntity(Linker $linker, $linkedEntity)
	// {
	// 	$hosts = $linker->getHostEntities($linkedEntity);

	// 	if(is_array($hosts) or ($hosts instanceof \Doctrine\Common\Collections\Collection)){
	// 		$hosts_ids = array();
	// 		foreach ($hosts as $host) {
	// 			$hosts_ids[] = $host->getId();
	// 		}
	// 	} else {
	// 		$hosts_ids = array($hosts->getId());
	// 	}

	// 	return $hosts_ids;
	// }
}