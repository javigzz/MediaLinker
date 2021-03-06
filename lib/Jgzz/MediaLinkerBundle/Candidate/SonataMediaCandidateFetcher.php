<?php
namespace Jgzz\MediaLinkerBundle\Candidate;

use Jgzz\MediaLinkerBundle\Linker\Linker;

/**
* Fetches entities implementing Sonata\MediaBundle\Model\MediaInterface
*/
class SonataMediaCandidateFetcher extends DoctrineCandidateFetcher {

	/**
	 * Qb filtering by context and provider
	 *
	 * {@inheritdoc}
	 */
	protected function createQueryBuilder(Linker $linker, $host_id)
	{
		$qb = parent::createQueryBuilder($linker, $host_id);

		$options = $this->options;

		$context = array_key_exists('context', $options) ? $options['context'] : null;

		if(array_key_exists('provider', $options)){
			$qb->andWhere($qb->expr()->eq('linked.providerName',':provider'))
				->setParameter('provider',$options['provider']);
		}

		if(array_key_exists('context', $options)){
			$qb->andWhere($qb->expr()->eq('linked.context',':context'))
				->setParameter('context',$options['context']);
		}

        $qb->orderBy('linked.name', 'ASC');

		return $qb;
	}
}