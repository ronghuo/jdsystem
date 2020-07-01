<?php

namespace app\common\model;

class TroubleshootingTemplateField extends BaseModel
{

    protected $pk = 'ID';

    public $table = 'troubleshoot_template_field';

    public function template(){
        return $this->belongsTo('TroubleshootingTemplate','TEMPLATE_ID','ID');
    }

}