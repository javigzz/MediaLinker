<?php
namespace Jgzz\MediaLinkerBundle\Candidate;

use Doctrine\ORM\QueryBuilder;
use Jgzz\MediaLinkerBundle\Linker\Linker;

/**
* Fetches entities using a Doctrine Query
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
	 * @todo use for 'options'. override global fetcher options?
	 * 
	 * @param  Linker $linker
	 * @param  integer $host_id
	 * @param  array $options
	 * @return Collection
	 */
	public function getCandidates(Linker $linker, $host_id, $options = array())
	{
		//?? $this->options = array_merge($this->options, $options);

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
			->where($already_linked_qb->expr()->eq('h.id',':host_id'));

		$qb = $repo->createQueryBuilder('linked');
		$qb->select('linked','host')
			->leftJoin('linked.'.$fieldName, 'host')
			->where($qb->expr()->notIn('linked.id', $already_linked_qb->getDQL()))
			->setParameter('host_id',$host_id);

		$this->addCustomFilters($qb);

		return $qb;
	}

	private function addCustomFilters(QueryBuilder $qb)
	{
		if (!array_key_exists('filter', $this->options)) {
			return;
		}

		foreach ($this->options['filter'] as $parameter => $value) {
			$param_key = ':'.$parameter;
			$qb->andWhere($qb->expr()->eq('linked.'.$parameter,$param_key))
				->setParameter($param_key, $value);
		}
	}
}