<?php

namespace app\htsystem\controller;

use app\common\library\Uploads;
use app\common\model\Subareas;
use app\common\model\TroubleshootingAssignment;
use app\common\model\TroubleshootingPersonExtension;
use app\common\validate\TroubleshootPersonVer;
use app\common\validate\TroubleshootTemplateFieldVer;
use Carbon\Carbon;
use think\Request;
use app\common\model\TroubleshootingTemplateField as TroubleshootingTemplateFieldModel;
use app\common\model\TroubleshootingTemplate as TroubleshootingTemplateModel;
use app\common\model\TroubleshootingPerson as TroubleshootingPersonModel;

class TroubleshootingPerson extends Common
{

    const WIDGET_LIST = [
        'TEXT' => '文本框',
        'TEXTAREA' => '文本域',
        'IMAGE' => '图片',
        'AUDIO' => '语音',
        'VIDEO' => '视频'
    ];
    const PERSON_INFO_NAME = '姓名';
    const PERSON_INFO_ID_CODE = '公民身份证号';
    const PERSON_INFO_DOMICILE_ADDRESS = '户籍地址';
    const PERSON_INFO_LIST = [
        self::PERSON_INFO_ID_CODE,
        self::PERSON_INFO_NAME,
        self::PERSON_INFO_DOMICILE_ADDRESS
    ];

    public function index(Request $request) {
        $templateId = $request->param('TEMPLATE_ID', '', 'int');
        $executeStatus = $request->param('EXECUTE_STATUS', '', 'trim');
        $keywords = $request->param('keywords', '', 'trim');
        $is_so = !empty($templateId) || !empty($executeStatus) || !empty($keywords);
        $rows = $this->loadData($request, false, false);

        $templateList = $this->getTemplateList();
        $this->assign('templateList', $templateList);
        $this->assign('executeStatusList', TroubleshootingPersonModel::EXECUTE_STATUS_LIST);
        $this->assign('list', $rows);
        $this->assign('page', $rows->render());
        $this->assign('total', $rows->total());
        $this->assign('is_so', $is_so);
        $this->assign('keywords', $keywords);
        $this->assign('templateId', $templateId);
        $this->assign('executeStatus', $executeStatus);
        $js = $this->loadJsCss(array('troubleshooting_person_index'), 'js', 'admin');
        $this->assign('footjs', $js);
        return $this->fetch();
    }

    public function export(Request $request) {
        $data = $this->loadData($request, true, true);
        $headers = [
            '所属模板',
            '被排查人员姓名',
            '被排查人员身份证号码',
            '被排查人员户籍地',
            '排查状态',
            '排查专干姓名',
            '排查时间'
        ];
        $data4Excel = [];
        if (!empty($data)) {
            $i = 0;
            foreach ($data as $item) {
                $data4Excel[$i++] = [
                    $item['TEMPLATE_NAME'],
                    $item['NAME'],
                    $item['ID_CODE'] . "\t",
                    $item['DOMICILE_PLACE'],
                    $item['EXECUTE_STATUS'],
                    $item['EXECUTOR_NAME'],
                    $item['EXECUTE_TIME']
                ];
            }
            $templateId = $request->param('TEMPLATE_ID', '', 'int');
            if (!empty($templateId)) {
                $fields = TroubleshootingTemplateFieldModel::where('EFFECTIVE', EFFECTIVE)
                    ->where('TEMPLATE_ID', $templateId)
                    ->field('ID, CODE, NAME, WIDGET')
                    ->order('SORT ASC')
                    ->select();
                foreach ($fields as $field) {
                    $headers[] = $field->NAME;
                    $i = 0;
                    foreach ($data as $item) {
                        if (in_array($field->WIDGET, TroubleshootingTemplateFieldModel::WIDGET_MULTI_MEDIA)) {
                            $data4Excel[$i++][] = empty($item[$field->CODE]) ? '无' : '有';
                        } else {
                            $data4Excel[$i++][] = $item[$field->CODE] . "\t";
                        }
                    }
                }
            }
        }
        exportExcel($headers, $data4Excel, "Sheet1", "安保排查人员清单");
    }

    private function loadData(Request $request, $toArray = false, $forExport = false) {
        $templateId = $request->param('TEMPLATE_ID', '', 'int');
        $executeStatus = $request->param('EXECUTE_STATUS', '', 'trim');
        $keywords = $request->param('keywords', '', 'trim');
        $queryFields = [
            'A.ID',
            'A.NAME',
            'A.ID_CODE',
            'A.DOMICILE_PROVINCE_NAME',
            'A.DOMICILE_CITY_NAME',
            'A.DOMICILE_COUNTY_NAME',
            'A.DOMICILE_STREET_NAME',
            'A.DOMICILE_COMMUNITY_NAME',
            'A.DOMICILE_ADDRESS',
            'A.EXECUTOR_MOBILE',
            'A.EXECUTOR_NAME',
            'A.EXECUTE_TIME',
            'A.EXECUTE_STATUS',
            'A.REMARK',
            'B.NAME TEMPLATE_NAME'
        ];
        $assignmentSql = TroubleshootingAssignment::order('CREATE_TIME DESC')->buildSql();
        $assignmentSql = db()->table("$assignmentSql A")->group('PERSON_ID')->buildSql();
        $query = TroubleshootingPersonModel::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->leftJoin("$assignmentSql C", 'A.ID = C.PERSON_ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->where(function ($query) {
                $where = $this->getManageWhere();
                if (!empty($where)) {
                    foreach ($where as $fd => $wh) {
                        $query->where("C.$fd", $wh);
                    }
                }
            });
        if ($forExport) {
            $fields = TroubleshootingTemplateFieldModel::where('EFFECTIVE', EFFECTIVE)
                ->where('TEMPLATE_ID', $templateId)
                ->field('ID, CODE')
                ->order('SORT ASC')
                ->select();
            $extensionSql = "select PERSON_ID,";
            foreach ($fields as $field) {
                $fieldId = $field->ID;
                $fieldCode = $field->CODE;
                array_push($queryFields, "D.$fieldCode");
                $extensionSql .= "MAX(case FIELD_ID when $fieldId then FIELD_VALUE else null end) $fieldCode,";
            }
            $extensionSql = substr($extensionSql, 0, -1);
            $extensionSql .= " from troubleshoot_person_extension group by PERSON_ID";
            $query->leftJoin("($extensionSql) D", "A.ID = D.PERSON_ID");
        }

        $query->field($queryFields);
        if (!empty($templateId)) {
            $query->where('B.ID', $templateId);
        }
        if (!empty($executeStatus)) {
            $query->where('A.EXECUTE_STATUS', $executeStatus);
        }
        if (!empty($keywords)) {
            $fields = ['A.NAME', 'A.ID_CODE', 'A.REMARK'];
            $query->whereLike(implode('|', $fields), "%$keywords%");
        }
        if (!$forExport) {
            $rows = $query->paginate(self::PAGE_SIZE, false, [
                'query' => request()->param()
            ]);
        } else {
            $rows = $query->select();
        }
        $rows->each(function($item) {
            $item->DOMICILE_PLACE = $item->DOMICILE_PROVINCE_NAME . $item->DOMICILE_CITY_NAME . $item->DOMICILE_COUNTY_NAME
                . $item->DOMICILE_STREET_NAME . $item->DOMICILE_COMMUNITY_NAME . $item->DOMICILE_ADDRESS;
            $item->EXECUTE_STATUS = in_array($item->EXECUTE_STATUS, array_keys(TroubleshootingPersonModel::EXECUTE_STATUS_LIST)) ? TroubleshootingPersonModel::EXECUTE_STATUS_LIST[$item->EXECUTE_STATUS] : '未知';
            return $item;
        });
        if ($toArray) {
            $rows = $rows->toArray();
        }
        return $rows;
    }

    public function read(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error('非法操作');
        }
        $queryFields = [
            'A.ID',
            'A.NAME',
            'A.ID_CODE',
            'A.DOMICILE_PROVINCE_NAME',
            'A.DOMICILE_CITY_NAME',
            'A.DOMICILE_COUNTY_NAME',
            'A.DOMICILE_STREET_NAME',
            'A.DOMICILE_COMMUNITY_NAME',
            'A.DOMICILE_ADDRESS',
            'A.EXECUTOR_MOBILE',
            'A.EXECUTOR_NAME',
            'A.EXECUTE_TIME',
            'A.EXECUTE_STATUS',
            'A.CREATE_USER_NAME',
            'A.CREATE_TIME',
            'A.UPDATE_USER_NAME',
            'A.UPDATE_TIME',
            'A.REMARK',
            'B.NAME TEMPLATE_NAME'
        ];
        $info = TroubleshootingPersonModel::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->find($id);
        if (empty($info)) {
            $this->error("排查人员信息已删除或不存在");
        }
        $queryFields = [
            'A.ID',
            'A.FIELD_ID',
            'B.NAME FIELD_NAME',
            'B.WIDGET FIELD_WIDGET',
            'A.FIELD_VALUE'
        ];
        $extension = TroubleshootingPersonExtension::alias('A')
            ->leftJoin('troubleshoot_template_field B', 'A.FIELD_ID = B.ID')
            ->where('A.PERSON_ID', $id)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->select();
        $info->extension = $extension;
        foreach ($info->extension as $item) {
            if (empty($item->FIELD_VALUE)) {
                continue;
            }
            if (in_array($item->FIELD_WIDGET, TroubleshootingTemplateFieldModel::WIDGET_MULTI_MEDIA)) {
                $item->IS_MULTI_MEDIA = true;
                $value4View = [];
                $values = json_decode($item->FIELD_VALUE);
                foreach ($values as $value) {
                    $value4View[] = build_http_img_url($value->URL);
                }
                $item->FIELD_VALUE = $value4View;
            }
        }

        $info->DOMICILE_PLACE = $info->DOMICILE_PROVINCE_NAME . $info->DOMICILE_CITY_NAME . $info->DOMICILE_COUNTY_NAME
            . $info->DOMICILE_STREET_NAME . $info->DOMICILE_COMMUNITY_NAME . $info->DOMICILE_ADDRESS;
        $info->EXECUTE_STATUS = in_array($info->EXECUTE_STATUS, array_keys(TroubleshootingPersonModel::EXECUTE_STATUS_LIST)) ? TroubleshootingPersonModel::EXECUTE_STATUS_LIST[$info->EXECUTE_STATUS] : '未知';

        $this->assign('info', $info);
        return $this->fetch();
    }

    public function create(Request $request) {
        if ($request->isPost()) {
            $data = [
                'TEMPLATE_ID' => $request->param('TEMPLATE_ID'),
                'CODE' => $request->param('CODE'),
                'NAME' => $request->param('NAME'),
                'WIDGET' => $request->param('WIDGET'),
                'NULLABLE' => $request->param('NULLABLE'),
                'SORT' => $request->param('SORT'),
                'DESC' => $request->param('DESC'),
                'CREATE_USER_ID' => session('user_id'),
                'CREATE_USER_NAME' => session('name'),
                'CREATE_TIME' => Carbon::now(),
                'UPDATE_USER_ID' => session('user_id'),
                'UPDATE_USER_NAME' => session('name'),
                'UPDATE_TIME' => Carbon::now(),
                'EFFECTIVE' => EFFECTIVE
            ];
            $ver = new TroubleshootTemplateFieldVer();
            if (!$ver->scene('create')->check($data)) {
                $this->error($ver->getError());
            }
            $field = TroubleshootingTemplateFieldModel::where('EFFECTIVE', EFFECTIVE)
                ->where('TEMPLATE_ID', $data['TEMPLATE_ID'])
                ->where(function ($query) use ($data) {
                    $query->where('CODE', $data['CODE'])->whereOr('NAME', $data['NAME']);
                })->find();
            if (!empty($field)) {
                if ($field->CODE == $data['CODE']) {
                    $this->error('字段代码不能重复');
                }
                elseif ($field->NAME == $data['NAME']) {
                    $this->error('字段名称不能重复');
                }
            }
            TroubleshootingTemplateFieldModel::create($data);
            return $this->success('安保排查模板字段新增成功', url('TroubleshootingTemplateField/index'));
        }
        $templateList = $this->getTemplateList();
        $js = $this->loadJsCss(array('troubleshooting_template_field_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('templateList', $templateList);
        $this->assign('widgetList', self::WIDGET_LIST);
        return $this->fetch();
    }

    public function modify(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error('非法操作');
        }
        $info = TroubleshootingPersonModel::find($id);
        if (empty($info)) {
            $this->error("排查人员已删除或不存在");
        }
        if ($request->isPost()) {
            $data = [
                'NAME' => $request->param('NAME'),
                'ID_CODE' => $request->param('ID_CODE'),
                'DOMICILE_COUNTY_CODE' => $request->param('DOMICILE_COUNTY_CODE'),
                'DOMICILE_STREET_CODE' => $request->param('DOMICILE_STREET_CODE'),
                'DOMICILE_COMMUNITY_CODE' => $request->param('DOMICILE_COMMUNITY_CODE'),
                'DOMICILE_ADDRESS' => $request->param('DOMICILE_ADDRESS'),
                'REMARK' => $request->param('REMARK'),
                'UPDATE_USER_ID' => session('user_id'),
                'UPDATE_USER_NAME' => session('name'),
                'UPDATE_TIME' => Carbon::now(),
                'UPDATE_TERMINAL' => TERMINAL_WEB
            ];
            $ver = new TroubleshootPersonVer();
            if (!$ver->scene('web.modify')->check($data)) {
                $this->error($ver->getError());
            }
            $personCount = TroubleshootingPersonModel::where('EFFECTIVE', EFFECTIVE)
                ->where('ID_CODE', $data['ID_CODE'])
                ->where('ID', '<>', $id)
                ->count();
            if ($personCount > 0) {
                $this->error("重复的被排除人员身份证号码");
            }
            $areaNames = Subareas::where('CODE12', 'in', [$data['DOMICILE_COUNTY_CODE'], $data['DOMICILE_STREET_CODE'], $data['DOMICILE_COMMUNITY_CODE']])
                ->order('CODE12 ASC')
                ->column('NAME');
            $data['DOMICILE_COUNTY_NAME'] = count($areaNames) >= 1 ? $areaNames[0] : '';
            $data['DOMICILE_STREET_NAME'] = count($areaNames) >= 2 ? $areaNames[1] : '';
            $data['DOMICILE_COMMUNITY_NAME'] = count($areaNames) >= 3 ? $areaNames[2] : '';
            $info->save($data);
            return $this->success('安保排查人员信息修改成功', url('TroubleshootingPerson/index'));
        }
        $js = $this->loadJsCss(array('p:cate/jquery.cate', 'troubleshooting_person_create'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        return $this->fetch('create');
    }

    private function getTemplateList() {
        return create_kv(TroubleshootingTemplateModel::where('EFFECTIVE', EFFECTIVE)->all(), 'ID', 'NAME');
    }

    public function delete(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error("非法操作");
        }
        $info = TroubleshootingPersonModel::find($id);
        if (empty($info)) {
            $this->error("排查人员信息已删除或不存在");
        }
        $info->EFFECTIVE = INEFFECTIVE;
        $info->save();
        $this->success('安保排查人员信息删除成功');
    }

    public function assigns(Request $request){
        $id = $request->param('ID');
        if (empty($id)) {
            $this->error("非法操作");
        }
        $info = TroubleshootingPersonModel::find($id);
        if (!$info) {
            $this->error('排查人员已删除或不存在');
        }
        $powerLevel = $this->getPowerLevel();
        if ($request->isPost()) {
            $countyCode = $request->param('COUNTY_CODE', '', 'trim');
            $streetCode = $request->param('STREET_CODE', '', 'trim');
            $communityCode = $request->param('COMMUNITY_CODE', '', 'trim');

            $areaNames = Subareas::where('CODE12', 'in', [$countyCode, $streetCode, $communityCode])
                ->order('CODE12 ASC')
                ->column('NAME');
            $countyName = count($areaNames) >= 1 ? $areaNames[0] : '';
            $streetName = count($areaNames) >= 2 ? $areaNames[1] : '';
            $communityName = count($areaNames) >= 3 ? $areaNames[2] : '';

            $reason = $request->param('REASON', '', 'trim');
            $action = $request->param('ACTION', TroubleshootingAssignment::ACTION_ASSIGN, 'trim');
            $assignment = new TroubleshootingAssignment();
            $assignment->PERSON_ID = $id;
            $assignment->COUNTY_CODE = $countyCode;
            $assignment->COUNTY_NAME = $countyName;
            $assignment->STREET_CODE = $streetCode;
            $assignment->STREET_NAME = $streetName;
            $assignment->COMMUNITY_CODE = $communityCode;
            $assignment->COMMUNITY_NAME = $communityName;
            $assignment->REASON = $reason;
            $assignment->ACTION = $action;
            $assignment->CREATE_USER_ID = session('user_id');
            $assignment->CREATE_USER_NAME = session('name');
            $assignment->CREATE_TIME = Carbon::now();
            $assignment->save();
            $this->jsalert("排查人员指派成功",3);
        }
        $assignment = TroubleshootingAssignment::where(['PERSON_ID' => $id])->order("CREATE_TIME DESC")->limit(1)->select();
        if ($assignment->isEmpty()) {
            $assignment = new TroubleshootingAssignment();
            $assignment->COUNTY_CODE = $info->DOMICILE_COUNTY_CODE;
            $assignment->STREET_CODE = $info->DOMICILE_STREET_CODE;
            $assignment->COMMUNITY_CODE = $info->DOMICILE_COMMUNITY_CODE;
        } else {
            $assignment = $assignment[0];
        }
        $info->DOMICILE_PLACE = $info->DOMICILE_PROVINCE_NAME . $info->DOMICILE_CITY_NAME . $info->DOMICILE_COUNTY_NAME
            . $info->DOMICILE_STREET_NAME . $info->DOMICILE_COMMUNITY_NAME . $info->DOMICILE_ADDRESS;
        $js = $this->loadJsCss(array('p:cate/jquery.cate','troubleshooting_person_assign'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('info', $info);
        $this->assign('assignment', $assignment);
        $this->assign('powerLevel', $powerLevel);
        return $this->fetch('assign');
    }

    public function import(Request $request) {
        if ($request->isPost()) {
            $templateId = $request->param('TEMPLATE_ID');
            $personListFile = (new Uploads())->excel($request, '', "PERSON_LIST");
            if (empty($personListFile)) {
                $this->error("非法操作");
            }
            $fileName = $personListFile['save_files'][0];
            $data = importExcel(self::PERSON_INFO_LIST, $fileName);
            if ($data) {
                $subareas = Subareas::field(['CODE12', 'NAME', 'PID'])->select()->toArray();
                foreach ($data as $item) {
                    $item['ID_CODE'] = $item[self::PERSON_INFO_ID_CODE];
                    $item['NAME'] = $item[self::PERSON_INFO_NAME];
                    if (empty($item['ID_CODE']) || empty($item['NAME'])) {
                        continue;
                    }
                    $item['TEMPLATE_ID'] = $templateId;
                    $item['DOMICILE_PROVINCE_CODE'] = DEFAULT_PROVINCE_ID .'000000';
                    $item['DOMICILE_PROVINCE_NAME'] = DEFAULT_PROVINCE_NAME;
                    $item['DOMICILE_CITY_CODE'] = DEFAULT_CITY_ID . '000000';
                    $item['DOMICILE_CITY_NAME'] = DEFAULT_CITY_NAME;
                    $domicileAddress = $item[self::PERSON_INFO_DOMICILE_ADDRESS];
                    $myBusiness = false;
                    if (!empty($domicileAddress)) {
                        if (is_numeric(mb_strpos($domicileAddress, DEFAULT_PROVINCE_NAME))) {
                            $domicileAddress = str_replace(DEFAULT_PROVINCE_NAME, '', $domicileAddress);
                            $this->splitAndSetDomicileAddress($domicileAddress, $item, $subareas);
                            $myBusiness = true;
                        }
                        if (is_numeric(mb_strpos($domicileAddress, DEFAULT_CITY_NAME))) {
                            $domicileAddress = str_replace(DEFAULT_CITY_NAME, '', $domicileAddress);
                            $this->splitAndSetDomicileAddress($domicileAddress, $item, $subareas);
                            $myBusiness = true;
                        }
                        if (!$myBusiness) {
                            $item['DOMICILE_PROVINCE_CODE'] = '';
                            $item['DOMICILE_PROVINCE_NAME'] = '';
                            $item['DOMICILE_CITY_CODE'] = '';
                            $item['DOMICILE_CITY_NAME'] = '';
                        }
                    }
                    unset($item[self::PERSON_INFO_ID_CODE]);
                    unset($item[self::PERSON_INFO_NAME]);
                    unset($item[self::PERSON_INFO_DOMICILE_ADDRESS]);
                    $this->addPerson($item);
                }
            }
            $this->jsalert("排查人员清单导入成功",3);
        }
        $templateList = $this->getTemplateList();
        $js = $this->loadJsCss(array('troubleshooting_person_import'), 'js', 'admin');
        $this->assign('footjs', $js);
        $this->assign('templateList', $templateList);
        return $this->fetch();
    }

    private function splitAndSetDomicileAddress($domicileAddress, &$item, $subareas, $areaTags = [
        'COUNTY' => [
            '管理区',
            '区',
            '自治县',
            '县',
            '市'
        ],
        'STREET' => [
            '街道',
            '乡',
            '镇',
            '服务中心'
        ],
        'COMMUNITY' => [
            '居委会',
            '村委会',
            '村',
            '社区'
        ]
    ]) {
        foreach ($areaTags as $areaType => $areaTag) {
            if ('COUNTY' == $areaType) {
                $pid = $item['DOMICILE_CITY_CODE'];
            }
            elseif ('STREET' == $areaType) {
                $pid = $item['DOMICILE_COUNTY_CODE'];
            }
            elseif ('COMMUNITY' == $areaType) {
                $pid = $item['DOMICILE_STREET_CODE'];
            }
            $areaCode = $areaName = '';
            foreach ($areaTag as $tag) {
                $tagIndex = mb_strpos($domicileAddress, $tag, 0);
                if (!$tagIndex) {
                    continue;
                }
                $areaName = mb_substr($domicileAddress, 0, $tagIndex + 1);
                $isDone = false;
                foreach ($subareas as $subarea) {
                    if ($subarea['NAME'] != $areaName) {
                        continue;
                    }
                    if ($pid != $subarea['PID']) {
                        continue;
                    }
                    $areaCode = $subarea['CODE12'];
                    $isDone = true;
                    break;
                }
                if ($isDone) {
                    $domicileAddress = mb_substr($domicileAddress, $tagIndex + 1);
                    break;
                }
                $areaName = '';
            }
            if ('COUNTY' == $areaType) {
                $item['DOMICILE_COUNTY_CODE'] = $areaCode;
                $item['DOMICILE_COUNTY_NAME'] = $areaName;
            }
            elseif ('STREET' == $areaType) {
                $item['DOMICILE_STREET_CODE'] = $areaCode;
                $item['DOMICILE_STREET_NAME'] = $areaName;
            }
            elseif ('COMMUNITY' == $areaType) {
                $item['DOMICILE_COMMUNITY_CODE'] = $areaCode;
                $item['DOMICILE_COMMUNITY_NAME'] = $areaName;
            }
        }
        $item['DOMICILE_ADDRESS'] = $domicileAddress;
    }

    private function addPerson($data) {
        $templateCount = TroubleshootingTemplateModel::where(['EFFECTIVE' => EFFECTIVE, 'ID' => $data['TEMPLATE_ID']])->count();
        if ($templateCount == 0) {
            return $this->fail("模板不存在或已删除");
        }
        $personCount = TroubleshootingPersonModel::where(['EFFECTIVE' => EFFECTIVE, 'ID_CODE' => $data['ID_CODE']])->count();
        if ($personCount > 0) {
            return $this->error("重复的身份证号码");
        }
        $ver = new TroubleshootPersonVer();
        if (!$ver->scene('create')->check($data)) {
            return $this->error($ver->getError());
        }
        $person = new TroubleshootingPersonModel();
        $data = array_merge($data, [
            'CREATE_USER_ID' => session('user_id'),
            'CREATE_USER_NAME' => session('name'),
            'CREATE_TIME' => Carbon::now(),
            'CREATE_TERMINAL' => TERMINAL_WEB,
            'UPDATE_USER_ID' => session('user_id'),
            'UPDATE_USER_NAME' => session('name'),
            'UPDATE_TIME' => Carbon::now(),
            'UPDATE_TERMINAL' => TERMINAL_WEB
        ]);
        $person->save($data);
        if (empty($person->ID)) {
            return false;
        }
        $assignment = new TroubleshootingAssignment();
        $assignment->PERSON_ID = $person->ID;
        $assignment->COUNTY_CODE = $data['DOMICILE_COUNTY_CODE'];
        $assignment->COUNTY_NAME = $data['DOMICILE_COUNTY_NAME'];
        $assignment->STREET_CODE = $data['DOMICILE_STREET_CODE'];
        $assignment->STREET_NAME = $data['DOMICILE_STREET_NAME'];
        $assignment->COMMUNITY_CODE = $data['DOMICILE_COMMUNITY_CODE'];
        $assignment->COMMUNITY_NAME = $data['DOMICILE_COMMUNITY_NAME'];
        $assignment->REASON = "系统根据户籍地信息自动指派";
        $assignment->ACTION = TroubleshootingAssignment::ACTION_ASSIGN;
        $assignment->CREATE_USER_ID = session('user_id');
        $assignment->CREATE_USER_NAME = session('name');
        $assignment->CREATE_TIME = Carbon::now();
        $assignment->save();
        return true;
    }

    protected function getManageWhere() {
        $where = [];
        $superadmin = session('superadmin');
        if ($superadmin) {
            return $where;
        }
        $power_level = session('power_level');
        $admin = session('info');
        // 市级
        if ($power_level == self::POWER_LEVEL_CITY) {
            return $where;
        }
        // 县级
        elseif ($power_level == self::POWER_LEVEL_COUNTY) {
            $where['COUNTY_CODE'] = $admin['POWER_COUNTY_ID_12'];
        }
        // 乡级
        elseif ($power_level == self::POWER_LEVEL_STREET) {
            $where['STREET_CODE'] = $admin['POWER_STREET_ID'];
        }
        // 村级
        elseif ($power_level == self::POWER_LEVEL_COMMUNITY) {
            $where['COMMUNITY_CODE'] = $admin['POWER_COMMUNITY_ID'];
        }
        return $where;
    }

}
