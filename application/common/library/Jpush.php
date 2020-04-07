<?php
/**
 * Created by PhpStorm.
 * User: chenxh
 * Date: 2018/6/11
 * Time: 15:00
 */
namespace app\common\library;
use JPush\Client as JPush_SDK;
use think\image\Exception;

class Jpush{
    const PUSH_USER_TAG = 'u';
    const PUSH_MANAGE_TAG = 'm';

    static protected $jpush_client_user = null;
    static protected $jpush_client_manage = null;


    static public function sendToUser($to_userid,$message,$extras=[],$title='新消息'){
        return self::pushMessage(
            self::createUserAlias($to_userid),
            $message,
            $extras,
            $title
        );
    }

    static public function sendToManage($to_userid,$message,$extras=[],$title='新消息'){
        return self::pushMessage(
            self::createManageAlias($to_userid),
            $message,
            $extras,
            $title
        );
    }

    static public function createUserAlias($user_id){
        return self::PUSH_USER_TAG.'_'.$user_id;
    }

    static public function createManageAlias($user_id){
        return self::PUSH_MANAGE_TAG.'_'.$user_id;
    }


    static public function pushMessage($alias,$message,$extras=[],$title='新消息'){
        try {
            $type = strtolower(substr($alias,0,1));

            $apns_production = $type==self::PUSH_USER_TAG ? config('push.user.apns_production'):config('push.manage.apns_production');

            $response = self::initObj($type)->push();

            $response->setPlatform(array('ios', 'android'));
            // 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
            // 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
            // 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求

            if(strpos($alias,'all')!==false){
                $response->addAllAudience();
            }else{
                $response->addAlias($alias);
            }

            //->addTag(array('tag1', 'tag2'))
            // ->addRegistrationId($registration_id)

            $response->setNotificationAlert($title)
                ->iosNotification($message, array(
                    'sound' => 'sound.caf',
                    // 'badge' => '+1',
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'category' => 'jd-jpush',
                    'extras' => $extras,
                ))
                ->androidNotification($message, array(
                    'title' => $title,
                    // 'builder_id' => 2,
                    'extras' => $extras
                ))
                ->message($message, array(
                    'title' => $title,
                    // 'content_type' => 'text',
                    'extras' => $extras
                ))
                ->options(array(
                    // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
                    // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
                    // 这里设置为 100 仅作为示例

                    // 'sendno' => 100,

                    // time_to_live: 表示离线消息保留时长(秒)，
                    // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
                    // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
                    // 这里设置为 1 仅作为示例

                    // 'time_to_live' => 1,

                    // apns_production: 表示APNs是否生产环境，
                    // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

                    'apns_production' => $apns_production,

                    // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                    // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                    // 这里设置为 1 仅作为示例

                    // 'big_push_duration' => 1
                ))
                ->send();
            //print_r($response);

            Mylog::write($response,'push_result');
            return true;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            throw new Exception($e);
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            throw new Exception($e);
        }
        return false;
    }

    static public function delUserAlias($user_id){
        return self::initObj(self::PUSH_USER_TAG)
            ->device()
            ->deleteAlias(self::createUserAlias($user_id));
    }

    static public function delManageAlias($user_id){
        return self::initObj(self::PUSH_MANAGE_TAG)
            ->device()
            ->deleteAlias(self::createManageAlias($user_id));
    }

    static public function initObj($type=self::PUSH_USER_TAG){
        $type = strtolower($type);
        if(!in_array($type,[self::PUSH_USER_TAG,self::PUSH_MANAGE_TAG])){
            $type = self::PUSH_USER_TAG;
        }
        if($type==self::PUSH_USER_TAG){
            $config = config('push.user');
            if(is_null(self::$jpush_client_user)){
                self::$jpush_client_user = new JPush_SDK(
                    $config['AppKey'],
                    $config['Secret'],
                    $config['log_file']);
            }
            return self::$jpush_client_user;
        }

        if($type==self::PUSH_MANAGE_TAG){
            if(is_null(self::$jpush_client_manage)){
                $config = config('push.manage');
                self::$jpush_client_manage = new JPush_SDK(
                    $config['AppKey'],
                    $config['Secret'],
                    $config['log_file']);
            }

            return self::$jpush_client_manage;
        }

    }

}