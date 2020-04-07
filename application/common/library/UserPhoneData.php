<?php
namespace app\common\library;

use app\common\model\UserPhoneDataAddress,
    app\common\model\UserPhoneDataCalls,
    app\common\model\UserPhoneDataSms;
use Carbon\Carbon;


class UserPhoneData{


    public function saveCalls($uuid,$data){

        $last_row = UserPhoneDataCalls::where('UUID',$uuid)->order('CALL_DATE_STR','desc')->find();

        $inserts = [];
        foreach($data as $da){
            /*$date_str = date('Y-m-d');
            $time_str = '';
            //21:03
            if(strpos($da['callDateStr'],':') !== false){
                $time_str = $da['callDateStr'];
            }//昨天
            elseif(strpos($da['callDateStr'],'昨天') !== false){
                $date_str = Carbon::yesterday()->toDateString();
            }//05-23
            elseif(strpos($da['callDateStr'],'-') !== false){
                $p_count = substr_count($da['callDateStr'],'-');
                if($p_count == 1){
                    $date_str = date('Y').'-'.$da['callDateStr'];
                }elseif($p_count == 2){
                    $date_str = $da['callDateStr'];
                }
            }*/

            if($last_row && strtotime($last_row->CALL_DATE_STR) >= strtotime($da['callDateStr'])){
                continue;
            }
            $inserts[] = [
                'UUID'=>$uuid,
                'CALL_NAME'=>$da['callName'],
                'CALL_NUMBER'=>$da['callNumber'],
                'CALL_TYPE_STR'=>$da['callTypeStr'],
                'CALL_DATE_STR'=>$da['callDateStr'],
                //'CALL_DATE'=>$da['callDateStr'],
                //'CALL_TIME'=>$time_str,
                'CALL_DURATION_STR'=>$da['callDurationStr']
            ];

        }

        if(!empty($inserts)){
            (new UserPhoneDataCalls())->insertAll($inserts);
        }

        return ['err'=>0,'msg'=>'saveCalls','count'=>count($inserts)];
    }


    public function saveSms($uuid,$data){

        $last_row = UserPhoneDataSms::where('UUID',$uuid)->order('SMS_DATE','desc')->find();

        $inserts = [];
        foreach($data as $da){

            if($last_row && strtotime($last_row->SMS_DATE)>=strtotime($da['date'])){
                continue;
            }

            $inserts[] = [
                'UUID'=>$uuid,
                'TYPE'=>$da['type'],
                'ADDRESS'=>$da['address'],
                'PERSON'=>$da['person'],
                'SMS_DATE'=>$da['date'],
                'BODY'=>$da['body']
            ];

        }

        if(!empty($inserts)){
            (new UserPhoneDataSms())->insertAll($inserts);
        }
        return ['err'=>0,'msg'=>'saveSms','count'=>count($inserts)];
    }

    public function saveAddress($uuid,$data){


        foreach($data as $da){
            $da['number'] = trim($da['number']);
            $address = UserPhoneDataAddress::where('UUID',$uuid)
                ->where('NUMBER',$da['number'])->find();

            if($address){
                if($address->NAME != $da['name']){

                    $address->NAME = $da['name'];
                    $address->UPDATE_TIME = Carbon::now()->toDateTimeString();

                    $address->save();
                }

            }else{
                UserPhoneDataAddress::create([
                    'UUID'=>$uuid,
                    'NAME'=>$da['name'],
                    'NUMBER'=>$da['number']
                ]);
            }

        }

        return ['err'=>0,'msg'=>'saveAddress','count'=>count($data)];
    }


}