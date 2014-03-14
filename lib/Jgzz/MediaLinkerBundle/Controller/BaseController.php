<?php
namespace Jgzz\MediaLinkerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{

	/**
	 * Urls for panel retrieving
	 * 
	 * @param  string $linkername
	 * @param  integer $host_id
	 * @return array
	 */
    public function generateHostPanelRoutes($linkername, $host_id)
    {
        $params = array('linkername'=>$linkername, 'host_id'=>$host_id);

        return array(   
            'panel_currents' => $this->generateUrl('jgzzmedialinker_panel_current', $params),
            'panel_form' => $this->generateUrl('jgzzmedialinker_panel_form', $params),
            'panel_candidates' => $this->generateUrl('jgzzmedialinker_panel_candidates', $params),
            );
    }

    // see Sonata\AdminBundle\Controller\CRUDController
    public function renderJson($data, $status = 200, $headers = array())
    {
        // fake content-type so browser does not show the download popup when this
        // response is rendered through an iframe (used by the jquery.form.js plugin)
        //  => don't know yet if it is the best solution
        if ($this->get('request')->get('_xml_http_request')
            && strpos($this->get('request')->headers->get('Content-Type'), 'multipart/form-data') === 0) {
            $headers['Content-Type'] = 'text/plain';
        } else {
            $headers['Content-Type'] = 'application/json';
        }

        return new Response(json_encode($data), $status, $headers);
    } 
}