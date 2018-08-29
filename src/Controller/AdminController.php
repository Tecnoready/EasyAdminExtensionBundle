<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab;
use AlterPHP\EasyAdminExtensionBundle\Model\Tab\TabContent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AdminController extends BaseAdminController
{
    protected function embeddedListAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $this->dispatch(EasyAdminEvents::POST_LIST, array('paginator' => $paginator));

        return $this->render('@EasyAdminExtension/default/embedded_list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'masterRequest' => $this->get('request_stack')->getMasterRequest(),
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessDeniedException
     */
    protected function isActionAllowed($actionName)
    {
        // autocomplete and embeddedList action are mapped to list action for access permissions
        if (in_array($actionName, ['autocomplete', 'embeddedList'])) {
            $actionName = 'list';
        }

        $this->get('alterphp.easyadmin_extension.admin_authorization_checker')->checksUserAccess(
            $this->entity, $actionName
        );

        return parent::isActionAllowed($actionName);
    }
    
    /**
     * The method that is executed when the user performs a 'show' action on an entity.
     *
     * @return Response
     */
    protected function showAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_SHOW);

        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $fields = $this->entity['show']['fields'];
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);
        $tab = null;
        $currentTab = null;
        if($this->request->getSession()->has(\AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab::NAME_CURRENT_TAB)){
            $currentTab = $this->request->getSession()->get(\AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab::NAME_CURRENT_TAB);
        }
        foreach ($fields as $field => $metadata) {
            if($metadata["type"] == Tab::TAB_TITLE){
                $tab = Tab::createFromMetadata($metadata);
                unset($fields[$field]);
            }else if($metadata["type"] == Tab::TAB_CONTENT){
                $routeParameters = isset($metadata["route_parameters"]) ? $metadata["route_parameters"] : [];
                $routeParameters["id"]  = $id;
                $routeParameters["entity"]  = $this->request->query->get('entity');
                $routeParameters["action"]  = $this->request->query->get('action');
                $metadata["route_parameters"] = $routeParameters;
                $tabContent = TabContent::createFromMetadata($metadata);
                if($tab === null){
                    $tab = Tab::createFromMetadata();
                }
                $tab->addTabContent($tabContent);
                unset($fields[$field]);
            }else if($tab !== null && isset($metadata["property"])){
                $tabContent = $tab->getLastTabContent();
                $tabContent->addField($field,$metadata);
                unset($fields[$field]);
            }else if($tab !== null && !isset($metadata["property"])){
                $tabContent = $tab->getLastTabContent();
                $tabContent->addField($field,$metadata);
                unset($fields[$field]);
            }
        }
        if($tab !== null){
            $tab->resolveCurrentTab($currentTab);
        }
        $this->dispatch(EasyAdminEvents::POST_SHOW, array(
            'deleteForm' => $deleteForm,
            'fields' => $fields,
            'entity' => $entity,
        ));

        $parameters = array(
            'entity' => $entity,
            'fields' => $fields,
            'delete_form' => $deleteForm->createView(),
            'tab' => $tab,
        );

        return $this->executeDynamicMethod('render<EntityName>Template', array('show', $this->entity['templates']['show'], $parameters));
    }
    
    /**
     * @Route("/tab", name="easyadmin_tab")
     */
    public function tabAction(\Symfony\Component\HttpFoundation\Request $request) {
        if($request->query->has(\AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab::NAME_CURRENT_TAB)){
            $request->getSession()->set(\AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab::NAME_CURRENT_TAB,$request->query->get(\AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab::NAME_CURRENT_TAB));
        }
        return new \Symfony\Component\HttpFoundation\JsonResponse();
    }
}
