<?php

namespace app\common\model;

class TroubleshootingTemplateField extends BaseModel
{

    protected $pk = 'ID';

    public $table = 'troubleshoot_template_field';

    const WIDGET_TEXT = 'TEXT';
    const WIDGET_TEXTAREA = 'TEXTAREA';
    const WIDGET_IMAGE = 'IMAGE';
    const WIDGET_VIDEO = 'VIDEO';
    const WIDGET_AUDIO = 'AUDIO';
    const WIDGET_MULTI_MEDIA = [
        self::WIDGET_IMAGE,
        self::WIDGET_VIDEO,
        self::WIDGET_AUDIO
    ];

    public function template(){
        return $this->belongsTo('TroubleshootingTemplate','TEMPLATE_ID','ID');
    }

}