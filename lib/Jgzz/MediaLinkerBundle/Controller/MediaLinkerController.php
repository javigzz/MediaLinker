<?php
namespace Jgzz\MediaLinkerBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\MediaBundle\Model\Media;
use Sonata\AdminBundle\Admin\Admin;;
use Jgzz\MediaLinkerBundle\Linker\Linker;
use Jgzz\MediaLinkerBundle\Linker\SonataLinker;
use Jgzz\MediaLinkerBundle\Candidate\CandidateFetcherInterface;

/**
 * Actions regarding the relation between a 'host' entity and a its 'related' ones
 * This controller works for entities linked through a SonataLinker since it user their 'Admins' 
 * in several ways
 * 
 * @todo: decouple from 'Admins'
 */
class MediaLinkerController extends BaseController
{
    /**
     * Handles creation of related entity by an ajax Request.
     * Render resulting panel to a variable, along with metadata on result
     *
     * @param  string $linkername
     * @param  integer $host_id
     * @return Response     json format
     */
    public function createAction($linkername, $host_id)
    {
        list($panel, $meta) = $this->renderFormPanelAndMetaInfo($linkername, $host_id);

        $result = $meta['created'] ? 'ok' : 'error';
        
        $params = array(
            'result' => $result, 
            'action' => 'create', 
            'id' => $host_id, 
            'panel'=> $panel,
            'urls' => $this->generateHostPanelRoutes($linkername, $host_id),
            );

        $form = $meta['form'];

        if($form->isBound() && !$form->isValid()){
            $params['error'] = $form->getErrorsAsString();
        }

        return $this->renderJson($params);
    }

    /**
     * Removes linked media by id and linkername
     *
     * @param $linkername
     * @param $id
     * @param $host_id
     * @return Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction($linkername, $id, $host_id)
    {
        $linkedclass = $this->getLinker($linkername)->getLinkedClass();

        /** @var Admin $admin */
        $admin = $this->get('sonata.admin.pool')->getAdminByClass($linkedclass);

        $object = $admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException('Access denied');
        }

        try {
            // if object is Media, $admin must take care of removing files ...
            $admin->delete($object);

            if ($object instanceof Media) {
                // warning: smell like should split in specific controller
                $this->deleteEntityAssets($object);
            }

        } catch (\Exception $e) {
            return $this->renderJson(array(
                'result'=>'error', 
                'error'=>'Error when trying to remove file from database: '.$e->getMessage(),
                'urls'=>$this->generateHostPanelRoutes($linkername, $host_id),
                ));
        }

        return $this->renderJson(array(
            'result'=>'ok', 
            'action'=>'delete', 
            'id'=>$id, 
            'class'=>$linkedclass,
            'urls'=>$this->generateHostPanelRoutes($linkername, $host_id),
            ));
    }

    /**
     * Unlink two entities
     * 
     * @param  integer $host_id
     * @param  string $hostclass
     * @param  integer $linked_id
     * @param  string $linkedclass
     * @return Response     json format
     */
    public function unlinkAction($linkername, $host_id, $linked_id)
    {
        return $this->extensionAction($linkername, 'unlink', $host_id, $linked_id);
    }

    /**
     * Links two entities
     * 
     * @param  string $linkername]
     * @param  integer $host_id
     * @param  integer $linked_id
     * @return Response     json format
     */
    public function linkAction($linkername, $host_id, $linked_id)
    {
        return $this->extensionAction($linkername, 'link', $host_id, $linked_id);
    }

    /**
     * Returns HTML panel of currently related entities
     * 
     * @param  string $linkername
     * @param  integer $host_id
     * @return string
     */
    public function currentAction($linkername, $host_id)
    {
        return new Response($this->renderPanel($linkername, $host_id));
    }


    /**
     * Returns HTML panel of candidates to be linked by a host entity
     * 
     * @param  string $linkername
     * @param  integer $host_id
     * @return string
     */
    public function candidatesAction($linkername, $host_id)
    {
        return new Response($this->renderCandidatesPanel($linkername, $host_id));
    }

    /**
     * Render current related entities panel
     * 
     * @param  [type] $linkername
     * @param  [type] $host_id   
     * @return [type]            
     */
    public function renderPanel($linkername, $host_id)
    {
        $linker = $this->getLinker($linkername);
        
        $hostEntity = $linker->findHostById($host_id);

        if(!$hostEntity){
            throw new \Exception("No host entity found with id ".$host_id);
        }
        
        return $this->container->get('templating')
            ->render('JgzzMediaLinkerBundle:CRUD:panel_current.html.twig',
            array(
                'linkedEntities' => $linker->getLinkedEntities($hostEntity),
                'hostEntity' => $hostEntity,
                'linker' => $linker));
    }

    public function renderFormPanel($linkername, $host_id, $options = array())
    {
        list($panel, $meta) = $this->renderFormPanelAndMetaInfo($linkername, $host_id);

        return $panel;
    }

    /**
     * Render and process form
     *
     * @param  [type] $linkername
     * @param  [type] $host_id   
     * @param  array  $options   
     * @return [type]            
     */
    public function renderFormPanelAndMetaInfo($linkername, $host_id, $options = array())
    {
        $linker = $this->getLinker($linkername);
        
        $request = $this->get('request');

        if (false === $linker->getLinkedAdmin()->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $linkedEntity = $this->newLinkedInstance($linker);

        $hostEntity = $linker->findHostById($host_id);

        if(!$hostEntity){
            throw new \Exception("No host entity found with id ".$host_id);
        }

        $linkedAdmin = $this->getAdmin(Linker::SIDE_LINKED, $linker);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $linkedAdmin->getForm();

        $form->setData($linkedEntity);

        $created = false;

        if ($request->getMethod() == 'POST') {

            $form->bind($request);

            if($form->isValid()){

                $linker->linkToHost($linkedEntity, $hostEntity);

                $linkedAdmin->create($linkedEntity);
                // $linkedAdmin->create($form->getData());

                $created = true;

            } else {
                $error = $form -> getErrors() ? $form -> getErrorsAsString() : null;
            }
        }

        $parameters = $this->resolveTemplateActionParameters($linker);

        $templateParams = array(
            'error' => isset($error) ? $error : null,
            'created' => $created,
            'linkername' => $linkername,
            'hostEntity' => $hostEntity,
            'linker' => $linker,
            'form' => $form->createView(),
            'action_parameters' => $parameters,
            // 'success_msg' => isset($success_msg) ? $success_msg : false,
            );

        // render to string
        $rendered = $this->container->get('templating')
        ->render('JgzzMediaLinkerBundle:CRUD:panel_form.html.twig', $templateParams);

        return array($rendered, array('form'=>$form, 'created'=>$created));        
    }

    /**
     * Renders control panel of candidates to be linked to an entity
     * 
     * @param  string $linkername
     * @param  integer $host_id   
     * @return string             
     */
    public function renderCandidatesPanel($linkername, $host_id, $options = array())
    {
        $linker = $this->getLinker($linkername);
        
        $hostclass = $linker->getHostClass();

        $hostEntity = $this->getAdmin(Linker::SIDE_HOST, $linker)->getObject($host_id);

        $fetcher = $this->getCandidateFetcher($linker);

        $candidates = $fetcher->getCandidates($linker, $host_id, $options);

        return $this->container->get('templating')->render(
            'JgzzMediaLinkerBundle:CRUD:panel_candidates.html.twig', 
            array('linker'=>$linker,'candidates'=>$candidates, 'hostEntity'=>$hostEntity, 'options'=>$options)
            );
    }

    public function doLinkOrUnlik(Linker $linker, $hostEntity, $linkedEntity, $action)
    {
        // special cases 'link' and 'unlink'
        if($action == 'link'){
            $linker->linkToHost($linkedEntity, $hostEntity);
        } else if($action == 'unlink') {
            $linker->unlinkFromHost($linkedEntity, $hostEntity);
        }

        $em = $this->get('doctrine')->getEntityManager();

        $em->persist($hostEntity);

        $em->persist($linkedEntity);

        $em->flush();
    }

    /**
     * Manages actions regarding an existent host and linked entity
     * Control is given to this controller or custom (extension) controllers set by de Linker
     * 
     * @param  string $linkername
     * @param  string $action    
     * @param  integer $host_id   
     * @param  integer $linked_id 
     * @return Response
     */
    public function extensionAction($linkername, $action, $host_id, $linked_id)
    {
        $linker = $this->getLinker($linkername);
        
        $hostclass = $linker->getHostClass();

        $linkedclass = $linker->getLinkedClass();

        $hostEntity = $this->getAdmin(Linker::SIDE_HOST, $linker)->getObject($host_id);

        if (!$hostEntity) {
            throw new NotFoundHttpException(sprintf('unable to find host entity with id : %s', $host_id));
        }

        $linkedEntity = $this->getAdmin(Linker::SIDE_LINKED, $linker)->getObject($linked_id);

        if (!$linkedEntity) {
            throw new NotFoundHttpException(sprintf('unable to find linked entity with id : %s', $linked_id));
        }

        // resolve callable
        // todo: take link and unlink to a LinkerActionsInterface class
        if(in_array($action, array('link','unlink'))){

            $action_callable = array($this,'doLinkOrUnlik');

        } else {
            $extension = $this->get('jgzz.medialinker.linkermanager')->getLinkerActionExtension($linker);

            if(!$extension){
                throw new \Exception("No controller to handle this custom action: ".$action);
            }

            $action_callable = array($extension, 'manageAction');
        }

        $response_template = array(
            'action'    =>$action, 
            'host_id'   =>$host_id, 
            'id'        =>$linked_id, 
            'urls'      =>$this->generateHostPanelRoutes($linkername, $host_id),
            );

        
        try {

            // TODO: interfaz...
            $output = call_user_func_array($action_callable, array($linker, $hostEntity, $linkedEntity, $action));
            // todo:add to json response
            // $output 

        } catch (Exception $e) {

            return $this->renderJson(array_merge($response_template, array(
                'result'    =>'error', 
                'error'     => $e->getMessage(),
                )));
        }
       
        return $this->renderJson(array_merge($response_template, array(
            'result'    =>'ok', 
            )));
    }



    /**
     * New Linked entity instance. Performs needed initializations
     * 
     * @param  Linker $linker
     * @return mixed
     */
    public function newLinkedInstance(SonataLinker $linker)
    {
        $linkedEntity = $linker->getLinkedAdmin()->getNewInstance();

        $linker->getLinkedAdmin()->setSubject($linkedEntity);

        // inject context and provider in media entity. asumes entity is a Media
        // TODO: move specific logic
        $fetcher = $this->getCandidateFetcher($linker);

        if ($fetcher instanceof \Jgzz\MediaLinkerBundle\Candidate\SonataMediaCandidateFetcher) {

            $fetcher_options = $fetcher->getOptions();

            if(array_key_exists('provider', $fetcher_options)){
                $linkedEntity->setProviderName($fetcher_options['provider']);
            }
            if(array_key_exists('context', $fetcher_options)){
                $linkedEntity->setContext($fetcher_options['context']);
            }
        }

        return $linkedEntity;
    }

    /**
     * Delete assets related to the removed Media object
     * 
     * @param  Object $object
     */
    protected function deleteEntityAssets(Media $object)
    {
        $provider = $this->get($object->getProviderName());

        // see: BaseProvider::preRemove
        $path = $provider->getReferenceImage($object);

        if ($provider->getFilesystem()->has($path)) {
            $provider->getFilesystem()->delete($path);
        }
    }

    /**
     * Parameters that must appear in the action url for creating new 
     * related entities
     * 
     * eg: media context & provider
     * 
     * @param  Linker $linker
     * @return array
     */
    public function resolveTemplateActionParameters(Linker $linker)
    {
        $params = array();

        $fetcher = $this->getCandidateFetcher($linker);

        // TODO: move specific logic somewhere else..
        if($fetcher instanceof \Jgzz\MediaLinkerBundle\Candidate\SonataMediaCandidateFetcher){
            $options = $fetcher->getOptions();

            if(array_key_exists('provider', $options)){
                $params['provider'] = $options['provider'];
            }
            if(array_key_exists('context', $options)){
                $params['context'] = $options['context'];
            }
        }

        return $params;
    }

    /**
     * @param $name
     * @return Linker
     */
    protected function getLinker($name)
    {
        return $this->getLinkerManager()->getLinker($name);
    }

    /**
     * @param Linker $linker
     * @return CandidateFetcherInterface
     */
    protected function getCandidateFetcher(Linker $linker)
    {
        return $this->getLinkerManager()->getCandidateFetcher($linker);
    }

    /**
     * @return \Jgzz\MediaLinkerBundle\LinkerManager
     */
    protected function getLinkerManager()
    {
        return $this->get('jgzz.medialinker.linkermanager');
    }


    /**
     * Gets the Admin service for a SonataLiner with the injected current Request
     * and other initializations
     * see SonataAdminBundle/Controller/CRUDController
     *
     * @todo: avoid more than one initialization of Admin class
     * 
     * @param  string $side
     * @param  Linker $linker
     * @return \Sonata\AdminBundle\Admin\Admin
     */
    protected function getAdmin($side, SonataLinker $linker)
    {
        /** @var Admin $admin */
        $admin = $side == Linker::SIDE_HOST ? $linker->getHostAdmin() : $linker->getLinkedAdmin();

        $request = $this->get('request');

        // @see Sonata\AdminBundle\Controller\CRUDController::configure
        // 
        // sets the same uniqid as in request, otherwise a new one would be created and no data would be found
        // on its name (in BindRequestListener)
        if ($uniqid = $request->get('uniqid')) {
            $admin->setUniqid($uniqid);
        }

        $admin->setRequest($request);

        return $admin;
    }
}
