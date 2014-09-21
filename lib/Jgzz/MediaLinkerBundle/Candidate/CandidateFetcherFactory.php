<?php
namespace Jgzz\MediaLinkerBundle\Candidate;

use Jgzz\MediaLinkerBundle\Linker\Linker;
use Jgzz\MediaLinkerBundle\Candidate\CandidateFetcherInterface;
use Jgzz\MediaLinkerBundle\Candidate\SonataMediaCandidateFetcher;
use Jgzz\MediaLinkerBundle\Candidate\DoctrineCandidateFetcher;

class CandidateFetcherFactory {

	private $fetcherPool = array();

    /**
     * Returns candidate fetcher suited for $linker
     *
     * @param Linker $linker
     * @param $config
     * @return DoctrineCandidateFetcher|SonataMediaCandidateFetcher
     */
    public function get(Linker $linker, $config)
	{
		$linkername = $linker->getName();

		if(array_key_exists($linkername, $this->fetcherPool)){
			return $this->fetcherPool[$linkername];
		}
		
		$fetcherName = $config['fetcherName'];

		if($fetcherName == 'sonatamedia'){

			$fetcher = new SonataMediaCandidateFetcher();

		} else if($fetcherName == 'doctrine'){

			$fetcher = new DoctrineCandidateFetcher();

		} else {

            $fetcher = new $fetcherName();
		}

		$fetcherOptions = $config['fetcherOptions'];

		if(!empty($fetcherOptions)){
			$fetcher->setDefaultOptions($fetcherOptions);
		}

		$this->fetcherPool[$linkername] = $fetcher;

		return $fetcher;
	}	
}