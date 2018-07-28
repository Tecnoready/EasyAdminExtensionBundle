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

/**
 * Tab
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class Tab {
    const TAB_TITLE = "tab_title";
    const TAB_CONTENT = "tab_content";

    private $id;
    private $name;
    private $icon;
    private $options;

    /**
     *
     * @var TabContent
     */
    private $tabsContent;

    public function __construct(array $options = []) {
        $this->tabsContent = [];
        $this->id = "tab-".uniqid();

        $this->setOptions($options);
    }

    public function setOptions(array $options = []) {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            "active_first" => true,
            "entity" => null,
        ]);
        $this->options = $resolver->resolve($options);

        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getIcon() {
        return $this->icon;
    }

    public function getTabsContent() {
        return $this->tabsContent;
    }
    
    /**
     * @return TabContent
     */
    public function getLastTabContent() {
        return end($this->tabsContent);
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Añade una tab
     * @param \Pandco\Bundle\AppBundle\Model\Core\Tab\TabContent $tabContent
     * @return \Pandco\Bundle\AppBundle\Model\Core\Tab\Tab
     * @throws \RuntimeException
     */
    public function addTabContent(TabContent $tabContent) {
        $id = md5($tabContent->getTitle());
        if (isset($this->tabsContent[$id])) {
            throw new \RuntimeException(sprintf("The tab content name '%s' is already added.", $tabContent->getName()));
        }
        $this->tabsContent[$id] = $tabContent;
        $tabContent->setId($id);
        return $this;
    }

    /**
     * Convierte la tab a un array
     * @return type
     */
    public function toArray() {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "tabsContent" => [],
        ];

        foreach ($this->tabsContent as $tabContent) {
            $data["tabsContent"][] = $tabContent->toArray();
        }
        return $data;
    }
    
    public static function createFromMetadata(array $metadata) {
        $instance = new self();
        
        if(isset($metadata["title"])){
            $instance->setName($metadata["title"]);
        }
        if(isset($metadata["icon"])){
            $instance->setIcon($metadata["icon"]);
        }
        
        return $instance;
    }

}
