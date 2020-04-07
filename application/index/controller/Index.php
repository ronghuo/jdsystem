<?php
namespace app\index\controller;
use \Carbon\Carbon;
class Index
{

    public function index(){
        return '';
    }
    public function hello($name = 'ThinkPHP5')
    {
        return '';
    }

    public function tttx(){
        phpinfo();
    }

    public function t7(){

        return getCurHttpHost();
    }

    public function t6(){
//        $data = [
//            'subject'=>'禹友仁来信了',
//            'body'=>'亲爱的，你好啊',
//            'to'=>[
//                'q245974451@qq.com'
//            ]
//        ];
        $messages = [
            'msg'=>'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'DMM_ID\' in \'where clause\'',
            'fileline'=>'/Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/db/Connection.php=687',
            'tracel'=>'#0 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/db/Connection.php(844): think\db\Connection->query(\'SELECT `ID`,`UC...\', Array, false, false)
#1 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/db/Query.php(3131): think\db\Connection->find(Object(think\db\Query))
#2 /Users/chenxh/Documents/www/jdsystem/application/common/model/UserManagers.php(17): think\db\Query->find()
#3 /Users/chenxh/Documents/www/jdsystem/application/api1/controller/manage/Test.php(40): app\common\model\UserManagers->createNewUCode(536)
#4 [internal function]: app\api1\controller\manage\Test->addMuser()
#5 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/Container.php(395): ReflectionMethod->invokeArgs(Object(app\api1\controller\manage\Test), Array)
#6 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/route/dispatch/Module.php(132): think\Container->invokeReflectMethod(Object(app\api1\controller\manage\Test), Object(ReflectionMethod), Array)
#7 [internal function]: think\route\dispatch\Module->think\route\dispatch\{closure}(Object(think\Request), Object(Closure), NULL)
#8 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/Middleware.php(185): call_user_func_array(Object(Closure), Array)
#9 [internal function]: think\Middleware->think\{closure}(Object(think\Request))
#10 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/Middleware.php(130): call_user_func(Object(Closure), Object(think\Request))
#11 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/route/dispatch/Module.php(137): think\Middleware->dispatch(Object(think\Request), \'controller\')
#12 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/route/Dispatch.php(168): think\route\dispatch\Module->exec()
#13 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/App.php(432): think\route\Dispatch->run()
#14 [internal function]: think\App->think\{closure}(Object(think\Request), Object(Closure), NULL)
#15 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/Middleware.php(185): call_user_func_array(Object(Closure), Array)
#16 [internal function]: think\Middleware->think\{closure}(Object(think\Request))
#17 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/Middleware.php(130): call_user_func(Object(Closure), Object(think\Request))
#18 /Users/chenxh/Documents/www/jdsystem/thinkphp/library/think/App.php(435): think\Middleware->dispatch(Object(think\Request))
#19 /Users/chenxh/Documents/www/jdsystem/public/index.php(21): think\App->run()
#20 {main}',
            'server'=>$_SERVER,
            'post'=>$_POST
        ];
//        \app\common\library\EmailHelper::send($data);
        \app\common\library\EmailHelper::sendErrorMessage($messages);


        return 'send';
    }

    public function t5(){

        $type = 218;
        $type218 = 2;

        $count = 18;

        $first = '2019-06-12';
        $last = '2019-10-27';

        $now = Carbon::now();
        $lastCheckTime = Carbon::parse($last);
        $diffMonthsFromNow = $now->diffInMonths($last);
        $diffMonthsFromFirst = $lastCheckTime->diffInMonths($first);

        $return = [
            '$first'=>$first,
            '$last'=>$last,
            '$diffMonthsFromNow'=>$diffMonthsFromNow,
            '$diffMonthsFromFirst'=>$diffMonthsFromFirst
            ];

        //第一年12次每个月一次，第二年6次每两个月一次，第三年4次每三个月一次，共不低于22次。
        if($type == 213){

            if($count < 12){
                $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 1);
            }elseif($count >=12 && $count < 18){
                if($diffMonthsFromNow < 12){
                    $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 1);
                }else{
                    $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 2);
                }

            }else{

                if($diffMonthsFromNow < 12){
                    $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 1);
                }elseif($diffMonthsFromNow >= 12 && $diffMonthsFromNow < 24){
                    $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 2);
                }else{
                    $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 3);
                }

            }

        }

        if($type == 218){
            //三年的人员第一年6次每两个月一次，第二年4次每三个月一次，第三年每半年一次，共不低于12次，
            if($type218 == 1){
                if($count < 6){
                    $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 2);
                }elseif($count >=6 && $count < 10){

                    if($diffMonthsFromNow < 12){
                        $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 2);
                    }else{
                        $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 3);
                    }

                }else{
                    if($diffMonthsFromNow < 12){
                        $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 2);
                    }elseif($diffMonthsFromNow >= 12 && $diffMonthsFromNow < 24){
                        $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 3);
                    }else{
                        $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 6);
                    }
                }
            }else{
                //决定两年的每两个月一次，共不低于12次。
                $return['next'] = $this->getNextCheckTime($lastCheckTime, $diffMonthsFromNow, 2);
            }
        }



        return json_encode($return);
    }

    protected function getNextCheckTime(Carbon $lastCheckTime, $diffMonthsFromNow, $m){
        //$now = Carbon::now();
        //已过期
        if($diffMonthsFromNow > $m){
            return $lastCheckTime->addMonths($diffMonthsFromNow + 1)->toDateString();
        }else{
            $check = $lastCheckTime->addMonths($m);
            //已过期
            if($check->isPast()){
                return $lastCheckTime->addMonth()->toDateString();
            }

            return $check->toDateString();
        }

    }

    public function t3(){

        $getHourMinuteString = function(Carbon $time){
            return ($time->hour>=10 ? $time->hour : '0'.$time->hour)
                .':'
                .($time->minute>=10 ? $time->minute : '0'.$time->minute) ;
        };

        $begin_date = Carbon::now()->addDay();
        $end_date = Carbon::now()->addDays(10);
        $dates = [];


        while($begin_date->timestamp < $end_date->timestamp){
            $start = '11:00';
            $end = '20:00';

            $start = Carbon::parse($start);
            $end = Carbon::parse($end);

            $list = [
                $getHourMinuteString($start)
            ];

            while($start->timestamp < $end->timestamp){
                $start = $start->addMinutes(30);
                $list[] = $getHourMinuteString($start);
            }

            $dates[] = [
                'date'=>$begin_date->toDateString(),
                'times'=>[
                    'start'=>$list[0],
                    'end'=>$getHourMinuteString($end),
                    'list'=>$list
                ]
            ];

            $begin_date = $begin_date->addDay();
        }



//        $list[] = [
//            $start->timestamp,
//            $end->timestamp
//        ];

        return json_encode($dates);
    }

    public function t2(){
//        $dept_id = 5004;
        $dept_id = 2968;

        $um = new \app\common\model\UserManagers();
        $code = $um->createNewUCode($dept_id);

        return $code;
    }

    public function t33(){

        $parse = parse_img_path('uploads/apply/20190321/06ef16bc18b9c4d2b478772fe515d86e-600-600.jpeg');

        $path = build_thumb_img_path('uploads/apply/20190321/06ef16bc18b9c4d2b478772fe515d86e.jpeg', 600);

        print_r( [
            $parse,
            $path
        ]);
    }

    public function t22(){
        $userids = [1,2,4,5,6,7,8,9,33,22,11];
        $completeds = [1,2,3,4,5,56];

        return (int) (count($completeds) * 100 / count($userids));
    }

    public function tq(){
//        \think\Queue::push(
//            '\app\common\job\Jpush',
//            [
//                'type'=>'u',
//                'user_id'=>1,
//                'message'=>'测试一条推送',
//                'metas'=>['url'=>'https://m.baidu.com'],
//            ]);
        \think\Queue::later(2,'\app\common\job\Jpush',
            [
                'type'=>'u',
                'user_id'=>1,
                'message'=>'测试一条推送',
                'metas'=>['url'=>'https://m.baidu.com'],
            ]);
        //\think\Queue::push('\app\common\job\Test',['aa'=>'bbb']);
    }

    public function importStxx(){

//        $file = fopen("./doc/common_stxx.csv","r");
        $file = fopen("./doc/stxx2.csv","r");
        $pids = [];
        $ids = [];
        $insert1 = [];
        $insert2 = [];
        while(! feof($file))
        {
            $line = fgetcsv($file);
            //print_r($line);
            $id = trim($line[0]);
            if(!$id){
                continue;
            }else{
                if(!in_array($line[2],$pids)){
                    $insert1[] = [
                        'ID'=>$line[2],
                        'NAME'=>$line[3],
                        'PID'=>0,
                        'PROVINCE_ID'=>43,
                        'CITY_ID'=>4312,
                        'COUNTY_ID'=>$line[0]
                    ];

                    $pids[] = $line[2];
                }

                if(!in_array($line[4],$ids)){
                    $insert2[] = [
                        'ID'=>$line[4],
                        'NAME'=>$line[5],
                        'PID'=>$line[2],
                        'PROVINCE_ID'=>43,
                        'CITY_ID'=>4312,
                        'COUNTY_ID'=>$line[0]
                    ];

                    $ids[] = $line[4];
                }

            }

        }

        fclose($file);

        //print_r($insert2);
        $sarea = new \app\common\model\AreasSubs();

        $sarea->insertAll($insert1);
        $sarea->insertAll($insert2);

    }

    public function addDmmcs(){

        return false;
        $file = fopen("./doc/dmmcs.csv","r");
        $insert = [];
        while(! feof($file))
        {
            $line = fgetcsv($file);
            //print_r($line);
            $id = trim($line[0]);
            if(!$id){
                continue;
            }else{
                $insert[] = [
                    'ID'=>$id,
                    'DM'=>$line[1],
                    'DMMC'=>$line[2],
                    'PDM'=>$line[3],
                    'DMJB'=>$line[4],
                    'DMLX'=>$line[5],
                    'ORDERID'=>$line[6],
                ];
            }

        }

        fclose($file);

        print_r($insert);
        $dmmcs = new \app\common\model\Dmmcs();
        $dmmcs->insertAll($insert);
    }

    public function levelDmmcs(){
        $all = \app\common\model\Dmmcs::all();

        $tree = create_level_tree($all,0,'DM','PDM');

        print_r($tree);

    }

    public function addNation(){
        $str = '汉族,蒙古族,回族,藏族,维吾尔族,苗族,彝族,壮族,布依族,朝鲜族,满族,侗族,瑶族,白族,土家族,哈尼族,哈萨克族,傣族,黎族,僳僳族,佤族,畲族,高山族,拉祜族,水族,东乡族,纳西族,景颇族,柯尔克孜族,土族,达斡尔族,仫佬族,羌族,布朗族,撒拉族,毛南族,仡佬族,锡伯族,阿昌族,普米族,塔吉克族,怒族,乌孜别克族,俄罗斯族,鄂温克族,德昂族,保安族,裕固族,京族,塔塔尔族,独龙族,鄂伦春族,赫哲族,门巴族,珞巴族,基诺族';

        $arr = explode(',',$str);
        $inserts = [];
        foreach($arr as $k=>$v){
            $inserts[] = [
                'NAME'=>$v,
                'ORDERID'=>$k
            ];
        }
        print_r($inserts);
        (new \app\common\model\Nations())->insertAll($inserts);
    }


    public function times(){
        $data = [
            'start_datetime'=>'2019-05-09 20:00:00',
            'end_datetime'=>'2019-05-09 23:00:00',
            'early_bird_deadline'=>'2019-05-8 20:00:00',
        ];


        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        $early_bird = Carbon::parse($data['early_bird_deadline']);
        //Wednesday 20 March 2019 8:00pm - 11:00pm
        print_r([
            $start->format('l d F Y g:ia'),
            $end->format('g:ia'),
            $early_bird->isPast()
        ]);


    }

    public function heart(){

        return json([
            'success'=>true,
            'time'=>time(),
            'date_time'=>Carbon::now()->toDateTimeString()
        ]);
    }
}
