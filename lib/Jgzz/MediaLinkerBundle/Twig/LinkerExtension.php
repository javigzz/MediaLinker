<?php
namespace Jgzz\MediaLinkerBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Jgzz\MediaLinkerBundle\LinkerManager;
use Jgzz\MediaLinkerBundle\Linker\Linker;

class LinkerExtension extends \Twig_Extension {

	private $mediaLinkerController;

	private $linkerManager;

	public function __construct(Controller $mediaLinkerController, LinkerManager $linkerManager) {

		$this->mediaLinkerController = $mediaLinkerController;

		$this->linkerManager = $linkerManager;
	}

	public function getName() {
		return 'jgzz_medialinker';
	}

	public function getFunctions() {
		return array(
			'jzlinker_render_panel' => new \Twig_Function_Method($this, 'renderRelatedMediaPanel'),
			'jzlinker_render_panel_form' => new \Twig_Function_Method($this, 'renderFormPanel'),
			'jzlinker_render_panel_candidates' => new \Twig_Function_Method($this, 'renderCandidatesMediaPanel'),
			'jzlinker_row_template' => new \Twig_Function_Method($this, 'linkedRowTemplate'),
			'jzlinker_form_theme' => new \Twig_Function_Method($this, 'getLinkerFormTheme'),
			'jzlinker_extension_config' => new \Twig_Function_Method($this, 'getExtensionConfig'),
			'jzlinker_linker_manager' => new \Twig_Function_Method($this, 'getLinkerManager'),
		);
	}

	public function linkedRowTemplate($linker)
	{
		return $this->linkerManager->getLinkerRowTemplate($linker);
	}
	
	
	/**
	 * Render media panel for host entity
	 * 
	 * @param  string $value
	 * @return string
	 */
	public function renderRelatedMediaPanel($linkername, $host_id, $parameters = array())
	{
		return $this->mediaLinkerController->renderPanel($linkername, $host_id, false, $parameters);
	}

	/**
	 * Render media panel for candidates to be linked to an entity
	 * 
	 * @param  string $value
	 * @return string
	 */
	public function renderCandidatesMediaPanel($linkername, $host_id, $parameters = array())
	{
		return $this->mediaLinkerController->renderCandidatesPanel($linkername, $host_id, $parameters);
	}

	
	/**
	 * Render form linked entity
	 * 
	 * @param  string $value
	 * @return string
	 */
	public function renderFormPanel($linkername, $host_id, $parameters = array())
	{
		return $this->mediaLinkerController->renderFormPanel($linkername, $host_id, $parameters);
	}

	public function getLinkerManager()
	{
		return $this->linkerManager;
	}

	/**
	 * Custom actions configuration. Set for the linker
	 * 
	 * @param  Linker $linker [description]
	 * @return array
	 */
	public function getExtensionConfig(Linker $linker)
	{
		$extension = $this->linkerManager->getLinkerActionExtension($linker);

        if(!$extension){
        	return array();
        }

        return $extension->getConfig();
	}

    /**
     * @param Linker $linker
     * @return string
     */
    public function getLinkerFormTheme(Linker $linker)
    {
        $config = $this->linkerManager->getLinkerConfig($linker);

        return $config['form_theme'];
    }
}
