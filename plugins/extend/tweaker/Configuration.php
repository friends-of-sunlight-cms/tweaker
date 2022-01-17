<?php

namespace SunlightExtend\Tweaker;

use Sunlight\Plugin\Action\ConfigAction;
use Sunlight\Util\ConfigurationFile;
use Sunlight\Util\Form;

class Configuration extends ConfigAction
{
    protected function getFields(): array
    {
        $config = $this->plugin->getConfig();

        return [
            'page_show_backlinks' => [
                'label' => _lang('tweaker.config.page_show_backlinks'),
                'input' => '<input type="checkbox" name="config[page_show_backlinks]" value="1"' . Form::activateCheckbox($config->offsetGet('page_show_backlinks')) . '>',
                'type' => 'checkbox'
            ],
        ];
    }

}
