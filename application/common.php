<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 执行操作所使用的终端
 */
define('TERMINAL_APP', 'APP');  // 手机
define('TERMINAL_WEB', 'WEB');  // 后台

/**
 * 地区代码
 */
define('MINISTRY_ID', '010000');            // 部
define('DEFAULT_PROVINCE_ID', '430000');    // 湖南省
define('DEFAULT_CITY_ID', '431200');        // 怀化市
define('TOP_MANAGE_DEPT_ID', '10074');      // 怀化市禁毒委员会

/**
 * 权限级别
 */
define('POWER_LEVEL_CITY', 1);         // 市级
define('POWER_LEVEL_COUNTY', 2);       // 县(区)级
define('POWER_LEVEL_STREET', 3);       // 乡镇(街道)级
define('POWER_LEVEL_COMMUNITY', 4);    // 村(社区)级

/**
 * 康复人员状态
 */
define('STATUS_COMMUNITY_DETOXIFICATION', '社区戒毒中');
define('STATUS_COMMUNITY_RECOVERING', '社区康复中');

/**
 * 尿检相关属性
 */
define('URINE_CHECK_YEARS', 3);                         // 尿检年限
define('URINE_CHECK_RATE_DETOXIFICATION', [12, 6, 4]);  // 社区戒毒尿检频率[第一年：12次，第二年：6次，第三年：4次]
define('URINE_CHECK_RATE_RECOVERING', [6, 4, 2]);       // 社区康复尿检频率[第一年：6次，第二年：4次，第三年：2次]
define('URINE_CHECK_FINISHED', '完成次数');
define('URINE_CHECK_MISSING', '缺失次数');
define('URINE_CHECK_CORRECT', '正常人数');
define('URINE_CHECK_INCORRECT', '异常人数');

/**
 * 是否有效
 */
define('EFFECTIVE', 'Y');
define('INEFFECTIVE', 'N');


/**
 * 创建无限分类树
 * @staticvar array $tree 保存树结构的数组
 * @param type $data 节点数组
 * @param type $pid 父节点，即从该节点往下找
 */
function create_tree($data, $pid = 0, $idkey = 'ID', $pidkey = 'PID')
{
    static $tree = array();

    foreach ($data as $key => $val) {

        if ($val[$pidkey] == $pid) {
            $tree[] = $val;
            unset($data[$key]);
            create_tree($data, $val[$idkey], $idkey, $pidkey);
        }
    }
    return $tree;
}

/**
 * 创建有层次的分类树
 * @param type $data 节点数组
 * @param type $pid 父节点，即从该节点往下找
 * @return type
 */
function create_level_tree($data, $pid = 0, $idkey = 'ID', $pidkey = 'PID')
{
    $tree = array();
    $i = 0;
    foreach ($data as $key => $val) {

        if ($val[$pidkey] == $pid) {
            $tree[$i] = $val;
            unset($data[$key]);
            $tmp = create_level_tree($data, $val[$idkey], $idkey, $pidkey);
            if (!empty($tmp)) {
                $tree[$i]['SUB'] = $tmp;
            }
            unset($tmp);
            $i++;
        }
    }
    return $tree;
}


function build_order_no($prefix = 'U', $date = '')
{

    /* 选择一个随机的方案 */
    mt_srand((double)microtime() * 1000000);
    $date = $date ? $date : date('ymd');
    return $prefix . $date . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function sms_code($length = 6)
{
    $pool = '0123456789';

    return \think\helper\Str::substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

function getCurHttpHost(){
    $scheme = request()->scheme();
    $host = request()->host(true);
    $port = request()->port();

    $port = $port != '80' ? ':'.$port : '';

    return $scheme.'://'.$host.$port;
}

function build_http_img_url($v, $field = '')
{
    $host = getCurHttpHost();
    if ($field) {
        if ($v[$field] && strpos($v[$field], 'http') === false) {
            $v[$field] = $host . ltrim($v[$field], '.');
        }
        return $v;
    }

    if (strpos($v, 'http') !== false) {
        return $v;
    }
    if ($v) {
        return $host . ltrim($v, '.');
    }

    return '';
}

function parse_img_path($path)
{
    if (strpos($path, '-') === false) {
        return [
            'src_path' => $path,
            'sizes' => []
        ];
    }

    $ext = pathinfo($path, PATHINFO_EXTENSION);
    //$path_without_ext = str_replace('.'.$ext, '', $path);
    preg_match_all("/-([1-9][0-9]*)+/", $path, $matchs);
//    if()

    //print_r($matchs);

    if (empty($matchs)) {
        return [
            'src_path' => $path,
            'sizes' => []
        ];
    }

    $src_path = str_replace($matchs[0], '', $path);

    return [
        'src_path' => $src_path,
        'sizes' => $matchs[1]
    ];

}

function build_thumb_img_path($src_path, $w = 0, $h = 0)
{
    if (!$w && !$h) {
        return $src_path;
    }
    $ext = pathinfo($src_path, PATHINFO_EXTENSION);
    $path_without_ext = str_replace('.' . $ext, '', $src_path);
    $sizes = [
        $w, $h ?: $w
    ];
    $addon = '-' . implode('-', $sizes);

    return $path_without_ext . $addon . '.' . $ext;
}

function tostring($data)
{
    if (empty($data)) {
        return $data;
    }
    if (is_string($data)) {
        return $data;
    }
    foreach ($data as $k => $da) {
        if (is_array($da)) {
            $data[$k] = tostring($da);
        } else if (is_object($da)) {
            $data[$k] = tostring($da);
        } else if (!is_string($da)) {
            $data[$k] = strval($da);
        }

    }
    return $data;
}

//计算文件大小
function get_data_size($size)
{
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++)
        $size /= 1024;
    return round($size, 2) . $units[$i];
}

function create_pwd($pwd, $stat)
{
    return md5(md5($pwd) . $stat);
}

/**
 * 将二维数组转为一维数组
 */
function array2to1($arr, $key = '', $str = false)
{
    $data = array();
    if (empty($arr)) {
        return false;
    }
    foreach ($arr as $val) {
        if ($key) {
            $data[] = trim($val[$key]);
        } else {
            $data[] = $val;
        }
    }
    if ($str) {
        return implode(',', array_unique($data));
    } else {
        return array_unique($data);
    }
}

function create_kv($data, $k, $v)
{

    $return = [];
    foreach ($data as $va) {
        $text = '';
        if (is_array($v)) {
            $tmp = [];
            foreach ($v as $txt) {
                $tmp[] = $va[$txt];
            }
            $text = implode('-', $tmp);
        } else {
            $text = $va[$v];
        }
        $return[$va[$k]] = $text;
    }
    return $return;
}

// 应用公共文件
function get_ref($base64 = false)
{
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return '';
    }
    return $base64 ? base64_encode($_SERVER['HTTP_REFERER']) : $_SERVER['HTTP_REFERER'];
}

/**
 * 获取当前的完整url
 */
function get_full_url($base64 = false)
{
    $url = getCurHttpHost() . $_SERVER['REQUEST_URI'];
    return $base64 ? base64_encode($url) : $url;
}

function get_host()
{
    return getCurHttpHost();
//    return 'http://' . $_SERVER['HTTP_HOST'];
}

function fillArrayToLen($arr, $len, $fillvalue = 0)
{
    for ($i = 0; $i < $len; $i++) {
        if (!isset($arr[$i])) {
            $arr[$i] = $fillvalue;
        }
    }

    return $arr;
}

//驼峰命名转下划线命名
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

//下划线命名到驼峰命名
function toCamelCase($str)
{
    $array = explode('_', $str);
    $result = $array[0];
    $len = count($array);
    if ($len > 1) {
        for ($i = 1; $i < $len; $i++) {
            $result .= ucfirst($array[$i]);
        }
    }
    return $result;
}

/**
 * 计算密码强度值
 * @param $pwd  密码
 * @return int  强度值
 */
function calcPwdStrength($pwd) {
    $score = 0;
    if (preg_match("/[0-9]+/", $pwd)) {
        $score ++;
    }
    if (preg_match("/[0-9]{3,}/", $pwd)) {
        $score ++;
    }
    if (preg_match("/[a-z]+/", $pwd)) {
        $score ++;
    }
    if (preg_match("/[a-z]{3,}/", $pwd)) {
        $score ++;
    }
    if (preg_match("/[A-Z]+/", $pwd)) {
        $score ++;
    }
    if(preg_match("/[A-Z]{3,}/", $pwd)) {
        $score ++;
    }
    if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $pwd)) {
        $score += 2;
    }
    if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]{3,}/", $pwd)) {
        $score ++ ;
    }
    if (strlen($pwd) >= 10) {
        $score ++;
    }
    return $score;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 判断Ueditor编辑器中的文本是否包含图片(标签)
 * @param $text
 * @return bool|int
 */
function isImgInUeditor($text) {
    if (empty($text)) {
        return false;
    }
    return preg_match('<img.+"/>', $text);
}

/**
 * 如果字符串为Empty则返回Null
 * @param $var
 * @return null
 */
function ifEmptyThenNull($var) {
    if (gettype($var) !== 'string') {
        return $var;
    }
    if ($var == '') {
        return null;
    }
    return $var;
}

function exportExcel($headerRows, $list, $title='Sheet1', $fileName='demo')
{
    if (empty($headerRows) || empty($list)) {
        return '列名或者内容不能为空';
    }

    //实例化PHPExcel类
    $PHPExcel = new PHPExcel();
    //获得当前sheet对象
    $sheet = $PHPExcel->getActiveSheet();
    //定义sheet名称
    $sheet->setTitle($title);

    //excel的列 这么多够用了吧？不够自个加 AA AB AC ……
    $letter = [
        'A','B','C','D','E','F','G','H','I','J','K','L','M',
        'N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
    ];

    $row = 1;
    // 存在多个标题行
    if (is_array($headerRows[0])) {
        $sheet->mergeCells('A1:A3');
        foreach ($headerRows as $headerRow) {
            $column = 0;
            if (!empty($headerRow['offset'])) {
                $column += $headerRow['offset'];
                unset($headerRow['offset']);
            }
            foreach ($headerRow as $setting) {
                $x = $letter[$column];
                $y = $row;
                if (!empty($setting['mergeX'])) {
                    $mergeX = $letter[$column + $setting['mergeX']];
                    $column += $setting['mergeX'];
                } else {
                    $mergeX = $x;
                }
                if (!empty($setting['mergeY'])) {
                    $mergeY = $y + $setting['mergeY'];
                } else {
                    $mergeY = $y;
                }
                $mergeTo = "$mergeX$mergeY";
                $mergeFrom = "$x$y";
                if ($mergeTo != $mergeFrom) {
                    $sheet->mergeCells("$mergeFrom:$mergeTo");
                }
                $sheet->getStyle($mergeFrom)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                if (!empty($setting['alignY'])) {
                    $sheet->getStyle($mergeFrom)->getAlignment()->setVertical($setting['alignY']);
                }
                $sheet->setCellValue($mergeFrom, $setting['name']);
                $column++;
            }
            $row++;
        }
    }
    else {
        if (count($list[0]) != count($headerRows)) {
            return '列名跟数据的列不一致';
        }
        for ($i = 0; $i < count($list[0]); $i++) {
            $sheet->setCellValue("$letter[$i]1","$headerRows[$i]");
        }
        $row++;
    }

    foreach ($list as $key => $val) {
        //array_values 把一维数组的键转为0 1 2 3 ..
        foreach (array_values($val) as $key2 => $val2) {
            $sheet->setCellValue($letter[$key2].($key + $row),$val2);
        }
    }
    //生成2007版本的xlsx
    $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$fileName.'.xlsx');
    header('Cache-Control: max-age=0');
    $PHPWriter->save("php://output");
}

/**
 * 从UEditor编辑器中截取图片路径信息
 * @param $content  UEditor编辑器中HTML文本内容
 * @param $images   待填充的图片路径数组
 * @param string $startNeedle   截取起始标识
 * @param array $endNeedles     截取结束标识
 * @return mixed
 */
function getImagesFromUEditor($content, &$images, $startNeedle = 'src="', $endNeedles = ['.jpg', '.png', '.bmp', '.gif']) {
    $start = strpos($content, $startNeedle, 0);
    if (!$start) {
        return;
    }
    $getStart = $start + strlen($startNeedle);
    foreach ($endNeedles as $endNeedle) {
        $end = strpos($content, $endNeedle, $getStart);
        if ($end >= 0) {
            $endNeedleLen = strlen($endNeedle);
            break;
        }
    }
    if (!$end) {
        return;
    }
    $getEnd = $end + $endNeedleLen;
    $len = $getEnd - $getStart;
    array_push($images, substr($content, $getStart, $len));
    $content = substr($content, $getEnd, strlen($content) - $getEnd);
    getImagesFromUEditor($content, $images);
}

/**
 * 数字转换为中文
 * @param  integer  $num  目标数字
 * @return string   汉化数字
 */
function number2chinese($num) {
    if (is_int($num) && $num < 100) {
        $char = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $unit = ['', '十', '百', '千', '万'];
        $return = '';
        if ($num < 10) {
            $return = $char[$num];
        } elseif ($num%10 == 0) {
            $firstNum = substr($num, 0, 1);
            if ($num != 10) $return .= $char[$firstNum];
            $return .= $unit[strlen($num) - 1];
        } elseif ($num < 20) {
            $return = $unit[substr($num, 0, -1)]. $char[substr($num, -1)];
        } else {
            $numData = str_split($num);
            $numLength = count($numData) - 1;
            foreach ($numData as $k => $v) {
                if ($k == $numLength) continue;
                $return .= $char[$v];
                if ($v != 0) $return .= $unit[$numLength - $k];
            }
            $return .= $char[substr($num, -1)];
        }
        return $return;
    }
}

/**
 * 将6位地区代码转换成12位
 * @param $code
 * @return bool|string
 */
function convertCodeTo12Chars($code) {
    if (empty($code)) {
        return false;
    }
    return $code . "000000";
}