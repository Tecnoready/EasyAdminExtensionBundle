<?php

/*
 * This file is part of the Witty Growth C.A. - J406095737 package.
 * 
 * (c) www.mpandco.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlterPHP\EasyAdminExtensionBundle\Model\Tab;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\Container;

/**
 * Contenido de tab
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class TabContent {
    
    private $id;
    
    /**
     * Tipo de contenido de tab
     * @var Tab::TAB_* 
     */
    private $type;
    private $url;
    private $name;
    private $order;
    private $options;
    private $active = false;
    private $title;
    
    /**
     * Metadata of properties
     * @var array
     */
    private $fields;

    public function __construct(array $options = []) {
        $this->setOptions($options);
        $this->fields = [];
    }
    
    /**
     * Opciones de la tab
     * @param array $options
     * @return \Pandco\Bundle\AppBundle\Model\Core\Tab\TabContent
     */
    public function setOptions(array $options = []) {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            "add_content_div" => true,
            "url" => null,
        ]);
//        $resolver->setRequired(["url"]);
        $this->options = $resolver->resolve($options);
        
        return $this;
    }
    
    /**
     * Busca una opcion
     * @param type $name
     * @return type
     */
    public function getOption($name) {
        return $this->options[$name];
    }
    
    public function getUrl() {
        return $this->url;
    }

    public function getName() {
        return $this->name;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }
    
    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function getActive() {
        return $this->active;
    }
    
    public function setActive($active) {
        $this->active = $active;
        return $this;
    }
    
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function getFields() {
        return $this->fields;
    }

    public function addField($field,array $metadata) {
        $this->fields[$field] = $metadata;
        return $this;
    }

    /**
     * Representacion de la tab en arary
     * @return array
     */
    public function toArray() {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "active" => $this->active,
            "options" => $this->options,
            "title" => $this->title,
        ];
        return $data;
    }
    
    public static function createFromMetadata(array $metadata,Container $container) {
        $instance = new self();
        
        $instance->setName($metadata["title"]);
        $instance->setType($metadata["type"]);
        
        if(isset($metadata["route"])){
            $routeParameters = isset($metadata["route_parameters"]) ? $metadata["route"] : [];
            $instance
                    ->setUrl($container
                            ->get("router")
                            ->generate($metadata["route"], $routeParameters,\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH));
        }
        
        return $instance;
    }
}
