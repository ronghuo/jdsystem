<?php

namespace app\common\model;

class TroubleshootingPersonExtension extends BaseModel
{

    protected $pk = 'ID';

    public $table = 'troubleshoot_person_extension';

    public function relatedField(){
        return $this->belongsTo('TroubleshootingTemplateField','FIELD_ID','ID');
    }

}