<?php
namespace Jgzz\MediaLinkerBundle\Candidate;

use Jgzz\MediaLinkerBundle\Linker\Linker;
use Jgzz\MediaLinkerBundle\CandidateFetcher\CandidateFetcherInterface;

class CandidateFetcherFactory {

	private $fetcherPool = array();

	/**
	 * Returns candidate fetcher suited for $linker
	 * 
	 * @param  Linker $linker
	 * @return CandidateFetcherInterface
	 */
	public function get(Linker $linker, $config)
	{
		$linkername = $linker->getName();

		if(array_key_exists($linkername, $this->fetcherPool)){
			return $this->fetcherPool[$linkername];
		}
		
		$fetcherName = $config['fetcherName'];

		if($fetcherName == 'sonatamedia'){

			$fetcher = new \Jgzz\MediaLinkerBundle\Candidate\SonataMediaCandidateFetcher();

		} else if($fetcherName == 'doctrine'){

			$fetcher = new \Jgzz\MediaLinkerBundle\Candidate\DoctrineCandidateFetcher();

		} else {

			throw new \Exception("not implemented candidate fetcher ". $fetcherName);
		}

		$fetcherOptions = $config['fetcherOptions'];

		if(!empty($fetcherOptions)){
			$fetcher->setDefaultOptions($fetcherOptions);
		}

		$this->fetcherPool[$linkername] = $fetcher;

		return $fetcher;
	}	
}