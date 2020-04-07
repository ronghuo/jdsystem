<?php
namespace app\common\library;


use app\common\model\Upareatable,
    app\common\model\Subareas,
    app\common\model\NbAuthDept;

class JqueryCateData{

    public static function createHHLevelAreasJson(){

        $save_file = './static/plugin/cate/levelareas.json';

        $all = Subareas::field('CODE12 as ID,NAME,PID')->where('ACTIVE', 1)->all();
        $trees = create_level_tree($all->toArray());
        $output = self::eachTrees($trees[0]['SUB']);


        file_put_contents($save_file,json_encode($output));
        return 'ok';
    }

    public static function createAreasJson(){

        $save_file = './static/plugin/cate/areas-all.json';

        //$all = Areas::all();
        $all = Upareatable::field('UPAREAID as ID, NAME, PID')
            ->where('FLAG', 0)
            ->where('UPAREAID', '<>', '010000')->all();

        $trees = create_level_tree($all->toArray());
        //print_r($trees);
        $output = self::eachTrees($trees);

        file_put_contents($save_file,json_encode($output));
        return 'ok';
    }

    public static function createAreaSubJson(){
        $save_file = './static/plugin/cate/areasubs.json';

        $all = Subareas::field('CODE12 as ID,NAME,PID')->where('ACTIVE', 1)->all();

        $trees = create_level_tree($all->toArray());
        //print_r($trees);
        $output = self::eachTrees($trees[0]['SUB']);
//        echo json_encode($output);

        file_put_contents($save_file,json_encode($output));
        return 'ok';
    }

    public static function createDmmcJson(){
        $save_file = './static/plugin/cate/dmmcs-43.json';
//        $dmmcs = Dmmcs::field(['ID','DM','PDM','DMMC as NAME'])->select();
//        $trees = create_level_tree($dmmcs->toArray(),0,'DM','PDM');

        $all = NbAuthDept::field('ID, PARENTDEPTID as PID, DEPTCODE, DEPTNAME as NAME')
            ->where('FLAG', 0)
            ->all();
//        $last = NbAuthDept::find(10470);
        $trees = create_level_tree($all,10040);
//        $trees[] = [
//            'ID'=>$last['ID'],
//            'PID'=>$last['PARENTDEPTID'],
//            'DEPTCODE'=>$last['DEPTCODE'],
//            'NAME'=>$last['DEPTNAME']
//        ];

        $output = self::eachTrees($trees);
//        echo json_encode($output);

        file_put_contents($save_file,json_encode($output));
        return 'ok';
    }


    protected static function eachTrees($trees){
        $output = [];
        foreach($trees as $lv1){
            $output[$lv1['ID']] = [
                "n"=>$lv1['NAME']
            ];

            if(empty($lv1['SUB'])){
                continue;
            }
            $output[$lv1['ID']]['c'] = [];


            foreach($lv1['SUB'] as $lv2){

                $output[$lv1['ID']]['c'][$lv2['ID']] = [
                    "n"=>$lv2['NAME']
                ];

                if(empty($lv2['SUB'])){
                    continue;
                }

                $output[$lv1['ID']]['c'][$lv2['ID']]['c'] = [];
                foreach($lv2['SUB'] as $lv3){

                    $output[$lv1['ID']]['c'][$lv2['ID']]['c'][$lv3['ID']] = [
                        "n"=>$lv3['NAME']
                    ];

                    if(empty($lv3['SUB'])){
                        continue;
                    }
                    $output[$lv1['ID']]['c'][$lv2['ID']]['c'][$lv3['ID']]['c'] = [];
                    foreach($lv3['SUB'] as $lv4){

                        $output[$lv1['ID']]['c'][$lv2['ID']]['c'][$lv3['ID']]['c'][$lv4['ID']] = [
                            "n"=>$lv4['NAME']
                        ];

                        if(empty($lv4['SUB'])){
                            continue;
                        }
                    }
                }

            }
        }

        return $output;
    }
}