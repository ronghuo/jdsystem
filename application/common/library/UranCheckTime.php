<?php
namespace app\common\library;

use Carbon\Carbon;

class UranCheckTime{

    const TYPE_213_VALUE = 213;
    const TYPE_218_VALUE = 218;

    const TYPE_213_TOTAL = 22;
    const TYPE_218_TOTAL = 12;

    public static function getNextCheckTime($type, $type218, $count, $first, $last){


        $now = Carbon::now();
        $lastCheckTime = Carbon::parse($last);
        //最近一次尿检与现在相差月数
        $diffMonthsFromNow = $now->diffInMonths($last);
        //最近一次尿检与第一次尿检相差月数
        $diffMonthsFromFirst = $lastCheckTime->diffInMonths($first);

//        $return = [
//            '$first'=>$first,
//            '$last'=>$last,
//            '$diffMonthsFromNow'=>$diffMonthsFromNow,
//            '$diffMonthsFromFirst'=>$diffMonthsFromFirst
//        ];

        $return = [];

        //第一年12次每个月一次，第二年6次每两个月一次，第三年4次每三个月一次，共不低于22次。
        if($type == self::TYPE_213_VALUE){

            if($count < 12){
                $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 1);
                $return['rate']  = 1;
            }elseif($count >=12 && $count < 18){
                //一年之内
                if($diffMonthsFromFirst < 12){
                    $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 1);
                    $return['rate']  = 1;
                }
                else{
                    $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 2);
                    $return['rate']  = 2;
                }

            }else{
                //一年之内
                if($diffMonthsFromFirst < 12){
                    $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 1);
                    $return['rate']  = 1;
                }
                //2年之内
                elseif($diffMonthsFromFirst >= 12 && $diffMonthsFromFirst < 24){
                    $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 2);
                    $return['rate']  = 2;
                }else{
                    $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 3);
                    $return['rate']  = 3;
                }

            }
            return $return;
        }

        if($type == self::TYPE_218_VALUE){
            //三年的人员第一年6次每两个月一次，第二年4次每三个月一次，第三年每半年一次，共不低于12次，
            if($type218 == 1){
                if($count < 6){
                    $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 2);
                    $return['rate']  = 2;
                }elseif($count >=6 && $count < 10){
                    //一年之内
                    if($diffMonthsFromFirst < 12){
                        $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 2);
                        $return['rate']  = 2;
                    }else{
                        $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 3);
                        $return['rate']  = 3;
                    }

                }else{
                    //一年之内
                    if($diffMonthsFromFirst < 12){
                        $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 2);
                        $return['rate']  = 2;
                    }
                    //2年之内
                    elseif($diffMonthsFromFirst >= 12 && $diffMonthsFromFirst < 24){
                        $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 3);
                        $return['rate']  = 3;
                    }else{
                        $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 6);
                        $return['rate']  = 6;
                    }
                }

                return $return;
            }
            if($type218 == 2){
                //决定两年的每两个月一次，共不低于12次。
                $return = self::checkTime($lastCheckTime, $diffMonthsFromNow, 2);
                $return['rate']  = 2;

                return $return;
            }
        }

        return [
            'next'=>$now->addMonth()->firstOfMonth()->toDateString(),
            'is_completed'=>0,
            'rate'=>1
        ];
    }


    protected static function checkTime(Carbon $lastCheckTime, $diffMonthsFromNow, $m){
        //$now = Carbon::now();
        //已过期
        if($diffMonthsFromNow > $m){
            return [
                'next'=>$lastCheckTime->addMonths($diffMonthsFromNow + 1)->toDateString(),
                'is_completed'=>0
            ];

        }else{
            $check = $lastCheckTime->addMonths($m);
            //已过期
            if($check->isPast()){
                return [
                    'next'=>$lastCheckTime->addMonth()->toDateString(),
                    'is_completed'=>0
                ];

            }
            return [
                'next'=>$check->toDateString(),
                'is_completed'=>1
            ];
        }

    }

}