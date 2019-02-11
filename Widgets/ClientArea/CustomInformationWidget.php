<?php

namespace Modules\Addons\CustomModule\Widgets\ClientArea;

use Modules\Provisioning\ServerProvisioningModule\Widgets\ClientArea\CABaseWidget;

/**
 * Class CustomInformationWidget
 * @package Modules\Addons\CustomModule\Widgets\ClientArea
 */
class CustomInformationWidget extends CABaseWidget
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Widget Name';
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return 'custom-widget.clientArea';
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return 'Description';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'newBox';
    }

    /**
     * @return int
     */
    public function width()
    {
        return 12;
    }

    /**
     * @return int
     */
    public function height()
    {
        return 4;
    }

    /**
     * @return mixed
     */
    public function render()
    {
        return view('CustomModule::widgets.clientarea.widget1', [
            'device' => $this->server,
        ]);
    }
}