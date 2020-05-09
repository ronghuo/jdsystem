<?php

namespace app\common\model;

class Agreement extends BaseModel
{
    protected $pk = 'ID';
    public $table = 'AGREEMENTS';

    public function images() {
        return $this->hasMany('AgreementImgs','AGREEMENT_ID');
    }

}
