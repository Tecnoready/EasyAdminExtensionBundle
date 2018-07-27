<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use AlterPHP\EasyAdminExtensionBundle\Model\Tab\Tab;
use AlterPHP\EasyAdminExtensionBundle\Model\Tab\TabContent;

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
        foreach ($fields as $field => $metadata) {
            if($metadata["type"] == Tab::TAB_TITLE){
                $tab = Tab::createFromMetadata($metadata);
                unset($fields[$field]);
            }else if($metadata["type"] == Tab::TAB_CONTENT){
                $tabContent = TabContent::createFromMetadata($metadata,$this->container);
                if($tab === null){
                    $tab = Tab::createFromMetadata();
                }
                $tab->addTabContent($tabContent);
                unset($fields[$field]);
            }else if($tab !== null && isset($metadata["property"])){
                $tabContent = $tab->getLastTabContent();
                $tabContent->addField($field,$metadata);
                unset($fields[$field]);
            }
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
}
