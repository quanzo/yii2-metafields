<?php
namespace x51\yii2\modules\metafields\events;

use \yii\base\Event;
use \x51\yii2\modules\metafields\Module;

class BeforeSaveEvent extends Event
{
    public $module;
    public $tid;
    public $arData;    
    public $isValid = true;

    public function __construct(Module $module, $tid, array $arData) {
        $this->module = $module;
        $this->tid = $tid;
        $this->arData = $arData;
    }
} // end class
