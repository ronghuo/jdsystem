<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/21
 */
namespace app\api1\controller\manage;

use app\api1\controller\Common;
use app\common\model\Subareas;
use app\common\model\TroubleshootingAssignment;
use app\common\model\TroubleshootingPerson;
use app\common\model\TroubleshootingPersonExtension;
use app\common\model\TroubleshootingTemplate;
use app\common\model\TroubleshootingTemplateField;
use app\common\model\Upareatable;
use app\common\validate\TroubleshootPersonVer;
use Carbon\Carbon;
use think\Request;

class Troubleshooting extends Common {

    public function getTemplateList() {
        $rows = TroubleshootingTemplate::where('EFFECTIVE', EFFECTIVE)
            ->field('ID, NAME, REMARK')->all();
        $this->ok('Success', [
            'list' => $rows
        ]);
    }

    public function getTemplateFieldList(Request $request) {
        $templateId = $request->param('TEMPLATE_ID');
        if (empty($templateId)) {
            $this->fail("参数错误");
        }
        $rows = TroubleshootingTemplateField::where('EFFECTIVE', EFFECTIVE)
            ->where('TEMPLATE_ID', $templateId)
            ->field('ID, CODE, NAME, WIDGET, SORT, NULLABLE, DESC')
            ->order('SORT ASC')
            ->select();
        $this->ok('Success', [
            'list' => $rows
        ]);
    }

    public function getPersonList(Request $request) {
        $templateId = $request->param('TEMPLATE_ID', 0, 'int');
        $executeStatus = $request->param('EXECUTE_STATUS', '', 'trim');
        $assignCountyCode = $request->param('DOMICILE_COUNTY_CODE', '', 'trim');
        $assignStreetCode = $request->param('DOMICILE_STREET_CODE', '', 'trim');
        $assignCommunityCode = $request->param('DOMICILE_COMMUNITY_CODE', '', 'trim');
        $queryFields = [
            'A.ID',
            'A.NAME',
            'A.EXECUTE_STATUS',
            'B.NAME TEMPLATE_NAME'
        ];
        $assignmentSql = TroubleshootingAssignment::order('CREATE_TIME DESC')->buildSql();
        $assignmentSql = db()->table("$assignmentSql A")->group('PERSON_ID')->buildSql();
        $query = TroubleshootingPerson::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->leftJoin("$assignmentSql C", 'A.ID = C.PERSON_ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where(function ($query) use ($request) {
                $this->contactWithArea($request->User->ID, $query, 'C.COUNTY_CODE', 'C.STREET_CODE', 'C.COMMUNITY_CODE');
            })
            ->field($queryFields)
            ->order('A.EXECUTE_STATUS', 'ASC');
        if (!empty($templateId)) {
            $query->where('B.ID', $templateId);
        }
        if (!empty($executeStatus)) {
            $query->where('A.EXECUTE_STATUS', $executeStatus);
        }
        if (!empty($assignCountyCode)) {
            $query->where('C.COUNTY_CODE', $assignCountyCode);
        }
        if (!empty($assignStreetCode)) {
            $query->where('C.STREET_CODE', $assignStreetCode);
        }
        if (!empty($assignCommunityCode)) {
            $query->where('C.COMMUNITY_CODE', $assignCommunityCode);
        }
        $rows = $query->select()->each(function($item) {
            $item->EXECUTE_STATUS = in_array($item->EXECUTE_STATUS, array_keys(TroubleshootingPerson::EXECUTE_STATUS_LIST)) ? TroubleshootingPerson::EXECUTE_STATUS_LIST[$item->EXECUTE_STATUS] : '未知';
            return $item;
        });
        $this->ok('Success', [
            'list' => $rows
        ]);
    }

    public function modifyPerson(Request $request) {
        $id = $request->param('ID', 0, 'int');
        $person = TroubleshootingPerson::where(['EFFECTIVE' => EFFECTIVE, 'ID' => $id])->find();
        if (empty($person)) {
            return $this->fail("人员信息不存在或已删除");
        }
        $templateId = $person->TEMPLATE_ID;
        $fields = TroubleshootingTemplateField::where(['EFFECTIVE' => EFFECTIVE, 'TEMPLATE_ID' => $templateId])->all();
        foreach ($fields as $field) {
            $code = $field->CODE;
            $name = $field->NAME;
            $widget = $field->WIDGET;
            $isImage = $widget == TroubleshootingTemplateField::WIDGET_IMAGE;
            $isAudio = $widget == TroubleshootingTemplateField::WIDGET_AUDIO;
            $isVideo = $widget == TroubleshootingTemplateField::WIDGET_VIDEO;
            if ($isImage) {
                $result = $this->uploadImages($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['images'])) {
                    $value = $this->getMultiMediaValue($result['fileNames'], $result['images']);
                } else {
                    $value = '';
                }
            }
            elseif ($isAudio) {
                $result = $this->uploadAudios($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['audios'])) {
                    $value = $this->getMultiMediaValue($result['fileNames'], $result['audios']);
                } else {
                    $value = '';
                }
            }
            elseif ($isVideo) {
                $result = $this->uploadVideos($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['videos'])) {
                    $value = $this->getMultiMediaValue($result['fileNames'], $result['videos']);
                } else {
                    $value = '';
                }
            }
            else {
                $value = $request->param($code);
            }
            // 检验人员扩展信息
            $extension = TroubleshootingPersonExtension::where(['PERSON_ID' => $id, 'FIELD_ID' => $field->ID])->find();
            $isMultiMedia = in_array($widget, TroubleshootingTemplateField::WIDGET_MULTI_MEDIA);
            $isValid = true;
            if ($field->NULLABLE == WHETHER_NO) {
                if (!$isMultiMedia  && empty($value)) {
                    $isValid = false;
                }
                elseif ($isMultiMedia && empty($value)) {
                    if (empty($extension) || empty($extension->FIELD_VALUE)) {
                        $isValid = false;
                    }
                }
            }
            if (!$isValid) {
                return $this->fail("缺少$name");
            }

            // 新增或修改人员扩展信息
            if (empty($extension)) {
                $extension = new TroubleshootingPersonExtension();
                $extension->TEMPLATE_ID = $templateId;
                $extension->PERSON_ID = $person->ID;
                $extension->FIELD_ID = $field->ID;
                $extension->CREATE_TIME = Carbon::now();
                $extension->FIELD_VALUE = $value;
            } else {
                if (!$isMultiMedia) {
                    $extension->FIELD_VALUE = $value;
                }
                elseif ($isMultiMedia && empty($extension->FIELD_VALUE)) {
                    $extension->FIELD_VALUE = $value;
                }
                elseif ($isMultiMedia && !empty($value) && !empty($extension->FIELD_VALUE)) {
                    if ($isImage) {
                        $newValue = array_merge(json_decode($extension->FIELD_VALUE), json_decode($value));
                        $extension->FIELD_VALUE = json_encode($newValue);
                    } else {
                        $extension->FIELD_VALUE = $value;
                    }
                }
            }
            $extension->UPDATE_TIME = Carbon::now();
            $extension->save();
        }
        $user = $request->User;
        $data = [
            'EXECUTE_STATUS' => TroubleshootingPerson::EXECUTE_STATUS_HANDLED,
            'EXECUTE_TIME' => $request->param('EXECUTE_TIME', null),
            'EXECUTOR_NAME' => $request->param('EXECUTOR_NAME', '', 'trim'),
            'EXECUTOR_MOBILE' => $request->param('EXECUTOR_MOBILE', '', 'trim'),
            'UPDATE_USER_ID' => $user->ID,
            'UPDATE_USER_NAME' => $user->NAME,
            'UPDATE_TIME' => Carbon::now(),
            'UPDATE_TERMINAL' => TERMINAL_APP
        ];
        $person->save($data);
        $this->ok('排查人员信息修改成功');
    }

    private function getMultiMediaValue($fileNames, $filePaths) {
        $value = [];
        for ($i = 0; $i < count($fileNames); $i++) {
            $fileName = $fileNames[$i];
            $fileName = substr($fileName, 0, strpos($fileName, "."));
            $value[] = [
                'ID' => $fileName,
                'URL' => $filePaths[$i]
            ];
        }
        return json_encode($value);
    }

    public function addPerson(Request $request) {
        $templateId = $request->param('TEMPLATE_ID', 0, 'int');
        $name = $request->param('NAME', '', 'trim');
        $idCode = $request->param('ID_CODE', '', 'trim');
        $domicileProvinceCode = $request->param('DOMICILE_PROVINCE_CODE', DEFAULT_PROVINCE_ID, 'trim');
        $domicileCityCode = $request->param('DOMICILE_CITY_CODE', DEFAULT_CITY_ID, 'trim');
        $domicileCountyCode = $request->param('DOMICILE_COUNTY_CODE', '', 'trim');
        $domicileStreetCode = $request->param('DOMICILE_STREET_CODE', '', 'trim');
        $domicileCommunityCode = $request->param('DOMICILE_COMMUNITY_CODE', '', 'trim');
        $domicileAddress = $request->param('DOMICILE_ADDRESS', '', 'trim');
        $effective = $request->param('EFFECTIVE', EFFECTIVE, 'trim');
        $remark = $request->param('REMARK', '', 'trim');
        $data = [
            'TEMPLATE_ID' => $templateId,
            'NAME' => $name,
            'ID_CODE' => $idCode,
            'DOMICILE_PROVINCE_CODE' => $domicileProvinceCode,
            'DOMICILE_CITY_CODE' => $domicileCityCode,
            'DOMICILE_COUNTY_CODE' => $domicileCountyCode,
            'DOMICILE_STREET_CODE' => $domicileStreetCode,
            'DOMICILE_COMMUNITY_CODE' => $domicileCommunityCode,
            'DOMICILE_ADDRESS' => $domicileAddress,
            'EXECUTE_STATUS' => TroubleshootingPerson::EXECUTE_STATUS_UNHANDLED,
            'EFFECTIVE' => $effective,
            'REMARK' => $remark
        ];
        $templateCount = TroubleshootingTemplate::where(['EFFECTIVE' => EFFECTIVE, 'ID' => $templateId])->count();
        if ($templateCount == 0) {
            return $this->fail("模板不存在或已删除");
        }
        $personCount = TroubleshootingPerson::where(['EFFECTIVE' => EFFECTIVE, 'ID_CODE' => $idCode])->count();
        if ($personCount > 0) {
            return $this->fail("重复的身份证号码");
        }
        $ver = new TroubleshootPersonVer();
        if (!$ver->scene('create')->check($data)) {
            return $this->fail($ver->getError());
        }
        $domicileUpAreas = Upareatable::where('UPAREAID','in', [$domicileProvinceCode, $domicileCityCode])->order('UPAREAID','asc')->select()->column('NAME');
        $domicileSubAreas = Subareas::where('CODE12','in', [$domicileCountyCode, $domicileStreetCode, $domicileCommunityCode])->order('CODE12','asc')->select()->column('NAME');
        $domicileProvinceName = $domicileUpAreas[0];
        $domicileCityName = $domicileUpAreas[1];
        $domicileCountyName = $domicileSubAreas[0];
        $domicileStreetName = $domicileSubAreas[1];
        $domicileCommunityName = $domicileSubAreas[2];
        $data = array_merge($data, [
            'DOMICILE_PROVINCE_NAME' => $domicileProvinceName,
            'DOMICILE_CITY_NAME' => $domicileCityName,
            'DOMICILE_COUNTY_NAME' => $domicileCountyName,
            'DOMICILE_STREET_NAME' => $domicileStreetName,
            'DOMICILE_COMMUNITY_NAME' => $domicileCommunityName
        ]);
        $fields = TroubleshootingTemplateField::where(['EFFECTIVE' => EFFECTIVE, 'TEMPLATE_ID' => $templateId])->all();
        $extensions = [];
        foreach ($fields as $field) {
            $code = $field->CODE;
            $name = $field->NAME;
            $widget = $field->WIDGET;
            if ($widget == TroubleshootingTemplateField::WIDGET_TEXT) {
                $value = $request->param($code);
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_TEXTAREA) {
                $value = $request->param($code);
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_IMAGE) {
                $result = $this->uploadImages($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['images'])) {
                    $value = implode(",", $result['images']);
                } else {
                    $value = '';
                }
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_AUDIO) {
                $result = $this->uploadAudios($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['audios'])) {
                    $value = implode(",", $result['audios']);
                } else {
                    $value = '';
                }
            }
            elseif ($widget == TroubleshootingTemplateField::WIDGET_VIDEO) {
                $result = $this->uploadVideos($request, ['troubleshooting/'], $code);
                if ($result && !empty($result['videos'])) {
                    $value = implode(",", $result['videos']);
                } else {
                    $value = '';
                }
            }
            else {
                $value = $request->param($code);
            }
            if ($field->NULLABLE == WHETHER_NO && empty($value)) {
                return $this->fail("缺少$name");
            }
            $extension = new TroubleshootingPersonExtension();
            $extension->TEMPLATE_ID = $templateId;
            $extension->FIELD_ID = $field->ID;
            $extension->FIELD_VALUE = $value;
            $extension->CREATE_TIME = Carbon::now();
            $extension->UPDATE_TIME = Carbon::now();
            array_push($extensions, $extension);
        }
        $user = $request->User;
        $person = new TroubleshootingPerson();
        $data = array_merge($data, [
            'CREATE_USER_ID' => $user->ID,
            'CREATE_USER_NAME' => $user->NAME,
            'CREATE_TIME' => Carbon::now(),
            'CREATE_TERMINAL' => TERMINAL_APP,
            'UPDATE_USER_ID' => $user->ID,
            'UPDATE_USER_NAME' => $user->NAME,
            'UPDATE_TIME' => Carbon::now(),
            'UPDATE_TERMINAL' => TERMINAL_APP
        ]);
        $person->save($data);
        if (empty($person->ID)) {
            $this->fail("排查人员信息添加失败");
        }
        foreach ($extensions as $extension) {
            $extension->PERSON_ID = $person->ID;
            $extension->save();
        }
        $assignment = new TroubleshootingAssignment();
        $assignment->PERSON_ID = $person->ID;
        $assignment->COUNTY_CODE = $domicileCountyCode;
        $assignment->COUNTY_NAME = $domicileCountyName;
        $assignment->STREET_CODE = $domicileStreetCode;
        $assignment->STREET_NAME = $domicileStreetName;
        $assignment->COMMUNITY_CODE = $domicileCommunityCode;
        $assignment->COMMUNITY_NAME = $domicileCommunityName;
        $assignment->REASON = "系统根据户籍地信息自动指派";
        $assignment->ACTION = TroubleshootingAssignment::ACTION_ASSIGN;
        $assignment->CREATE_USER_ID = $user->ID;
        $assignment->CREATE_USER_NAME = $user->NAME;
        $assignment->CREATE_TIME = Carbon::now();
        $assignment->save();
        $this->ok('排查人员信息添加成功');
    }

    public function getPersonInfo(Request $request) {
        $id = $request->param('ID');
        if (empty($id)) {
            $this->fail('参数错误');
        }
        $queryFields = [
            'A.ID',
            'A.NAME',
            'A.ID_CODE',
            'A.DOMICILE_PROVINCE_CODE',
            'A.DOMICILE_PROVINCE_NAME',
            'A.DOMICILE_CITY_CODE',
            'A.DOMICILE_CITY_NAME',
            'A.DOMICILE_COUNTY_CODE',
            'A.DOMICILE_COUNTY_NAME',
            'A.DOMICILE_STREET_CODE',
            'A.DOMICILE_STREET_NAME',
            'A.DOMICILE_COMMUNITY_CODE',
            'A.DOMICILE_COMMUNITY_NAME',
            'A.DOMICILE_ADDRESS',
            'A.EXECUTOR_MOBILE',
            'A.EXECUTOR_NAME',
            'A.EXECUTE_TIME',
            'A.EXECUTE_STATUS',
            'A.REMARK',
            'B.ID TEMPLATE_ID',
            'B.NAME TEMPLATE_NAME'
        ];
        $info = TroubleshootingPerson::alias('A')
            ->leftJoin('troubleshoot_template B', 'A.TEMPLATE_ID = B.ID')
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->where('B.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->find($id);
        if (empty($info)) {
            $this->fail("排查人员信息已删除或不存在");
        }
        $queryFields = [
            'B.ID',
            'A.ID FIELD_ID',
            'A.CODE FIELD_CODE',
            'A.NAME FIELD_NAME',
            'A.WIDGET FIELD_WIDGET',
            'A.NULLABLE FIELD_NULLABLE',
            'B.FIELD_VALUE'
        ];

        $extension = TroubleshootingTemplateField::alias('A')
            ->leftJoin('troubleshoot_person_extension B', "B.FIELD_ID = A.ID and B.PERSON_ID = $id")
            ->where('A.TEMPLATE_ID', $info->TEMPLATE_ID)
            ->where('A.EFFECTIVE', EFFECTIVE)
            ->field($queryFields)
            ->select();
        $info->EXTENSION = $extension;
        foreach ($info->EXTENSION as $item) {
            if (empty($item->FIELD_VALUE)) {
                continue;
            }
            if (in_array($item->FIELD_WIDGET, TroubleshootingTemplateField::WIDGET_MULTI_MEDIA)) {
                $values = json_decode($item->FIELD_VALUE);
                foreach ($values as &$value) {
                    $value->URL = build_http_img_url($value->URL);
                }
                $item->FIELD_VALUE = $values;
            }
        }

        $this->ok('Success', [
            'info' => $info
        ]);
    }

    public function deleteFile(Request $request) {
        $personId = $request->param('PERSON_ID', 0, 'int');
        $fieldId = $request->param('FIELD_ID', 0, 'int');
        $fileId = $request->param('FILE_ID', '', 'trim');
        if (empty($personId) || empty($fieldId) || empty($fileId)) {
            $this->fail("参数错误");
        }
        $extension = TroubleshootingPersonExtension::where(['PERSON_ID' => $personId, 'FIELD_ID' => $fieldId])->find();
        if (empty($extension)) {
            $this->fail("排查人员扩展信息不存在");
        }
        $field = TroubleshootingTemplateField::where('ID', $fieldId)->find();
        if (!in_array($field->WIDGET, TroubleshootingTemplateField::WIDGET_MULTI_MEDIA)) {
            $this->fail("非多媒体文件字段类型");
        }
        if (!empty($extension->FIELD_VALUE)) {
            $value = json_decode($extension->FIELD_VALUE);
            foreach ($value as $index => $item) {
                if ($item->ID == $fileId) {
                    unset($value[$index]);
                    break;
                }
            }
            $extension->FIELD_VALUE = empty($value) ? '' : json_encode(array_values($value));
            $extension->save();
        }
        $this->ok('文件删除成功');
    }

}