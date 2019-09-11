<?php
namespace x51\yii2\modules\metafields\events;

use \yii\base\Event;
use \x51\yii2\modules\metafields\Module;

class AfterSaveEvent extends Event
{
    public $module;
    public $tid;
    public $arData;
    public $success;

    public function __construct(Module $module, $tid, array $arData, $success = true) {
        $this->module = $module;
        $this->tid = $tid;
        $this->arData = $arData;
        $this->success = $success;
    }
} // end class
