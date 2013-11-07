<?php
namespace Jgzz\MediaLinkerBundle\Candidate;

use Jgzz\MediaLinkerBundle\Linker\Linker;

interface CandidateFetcherInterface {

	/**
	 * Fetches entities
	 * 
	 * @param  Linker $linker
	 * @param  integer $host_id
	 * @param  array  $options
	 * @return mixed
	 */
	public function getCandidates(Linker $linker, $host_id, $options = array());

	/**
	 * Set default options for fetcher
	 * 
	 * @return null
	 */
	public function setDefaultOptions(array $options);

	public function getOptions();
}