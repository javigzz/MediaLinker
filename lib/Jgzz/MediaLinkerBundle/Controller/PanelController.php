<?php
namespace Jgzz\MediaLinkerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for panel rendering and metainfo
 *
 * @todo move panel rendering endpoints here
 */
class PanelController extends BaseController
{
	/**
	 * Render json Response with panel urls
	 * 
	 * @param  string $linker  
	 * @param  integer $host_id 
	 * @return Response          
	 */
	public function panelUrlsAction($linker, $host_id)
	{
		$urls = $this->generateHostPanelRoutes($linker, $host_id);

		return $this->renderJson($urls);
	}
}