<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use JavierEguiluz\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Twig\TwigFunction;
use Twig_Environment;

/**
 * Extension
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class HelperExtension extends AbstractExtension
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    public function __construct(ConfigManager $configManager) {
        $this->configManager = $configManager;
    }

        public function getFunctions()
    {
        return array(
            new TwigFunction('easyadmin_render_show_field', array($this, 'renderShowField'), array('is_safe' => array('html'), 'needs_environment' => true)),
            new TwigFunction('easyadmin_render_show_object', array($this, 'renderShowObject'), array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }
    
    public function renderShowField(Twig_Environment $twig, $item,$property) {
        $className = get_class($item);
        if(class_exists("Doctrine\Common\Util\ClassUtils")){
            $className = \Doctrine\Common\Util\ClassUtils::getRealClass($className);
        }
        $extension = $twig->getExtension(\EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension::class);
        $fieldMetadata = $this->configManager->getEntityConfigByClass($className);
        $action = "show";
        $meta = $fieldMetadata[$action]["fields"][$property];
        return $extension->renderEntityField($twig,$action,$fieldMetadata["name"],$item,$meta);
    }
    
    public function renderShowObject(Twig_Environment $twig,$item,$label = null) {
        return $twig->render("EasyAdminExtensionBundle:includes:_actions.html.twig",[
            "item" => $item,
            "label" => $label,
        ]);
    }
}
