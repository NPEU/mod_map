<?php

namespace NPEU\Module\Map\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_map
 *
 * @since  4.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     */
    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];


        /*$data['stuff'] = $this->getHelperFactory()
            ->getHelper('MapHelper')
            ->getStuff($params, $this->getApplication());*/

        /*$data['twig'] = $this->getHelperFactory()
            ->getHelper('MapHelper')
            ->getTwig($params, $this->getApplication());*/

        $data['manual_markers'] = $this->getHelperFactory()
            ->getHelper('MapHelper')
            ->getManualMarkers($params, $this->getApplication());

        $data['remote_markers'] = $this->getHelperFactory()
            ->getHelper('MapHelper')
            ->getRemoteMarkers($params, $this->getApplication());

        #$this->getHelperFactory()->getHelper('MapHelper')->loadAssets($params);

        return $data;
    }
}
