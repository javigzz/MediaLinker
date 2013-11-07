<?php
namespace Jgzz\MediaLinkerBundle\Actions;

use Jgzz\MediaLinkerBundle\Linker\Linker;

/**
* Interface for controlling actions regarding already existent
* host and linked entities
*/
interface LinkerActionsInterface
{
	/**
	 * @return array
	 */
	public function getActions();

	/**
	 * @return array
	 */
	public function getConfig();
	
	/**
	 * @return string
	 */
	public function getCaption($actionname);

	public function manageAction(Linker $linker, $hostEntity, $linkedEntity, $action);
}