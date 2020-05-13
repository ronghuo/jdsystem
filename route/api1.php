<?php
/**
 * Created by PhpStorm.
 * User: xiaohui
 * Date: 2019/3/17
 */


use think\facade\Route;

Route::any('/test/:name','api1/Test/:name');

Route::get('/options/areas','api1/Options/areas');
Route::get('/options/subareas','api1/Options/subAreas');
//Route::get('/options/subareasbak','api1/Options/subAreasbak');
Route::get('/options/hhlevelareas','api1/Options/hhlevelareas');
Route::get('/options/dmmcs','api1/Options/dmmcs');
Route::get('/options/depts','api1/Options/depts');
Route::get('/options','api1/Options/index');
Route::post('/upload/image','api1/Upload/images');
Route::post('/upload/video','api1/Upload/videos');
Route::post('/upload/audio','api1/Upload/audios');
Route::get('/version/index', 'api1/Version/index');

//-----------------------------------------------------//
//-------------------康复端----------------------------//
//-----------------------------------------------------//
Route::any('/uuser/test/:mothed','api1/uuser.Test/:mothed');




Route::post('/uuser/login','api1/uuser.Login/index');
Route::post('/uuser/smslogin','api1/uuser.Login/sms');
//首页
Route::get('/uuser/index','api1/uuser.Index/index')->middleware('Api1UUserAuth');
//消息
Route::get('/uuser/system_messages','api1/uuser.SystemMessage/index')->middleware('Api1UUserAuth');
Route::post('/uuser/system_message/read','api1/uuser.SystemMessage/setRead')->middleware('Api1UUserAuth');

//我的
Route::get('/uuser/me','api1/uuser.User/index')->middleware('Api1UUserAuth');
Route::post('/uuser/me','api1/uuser.User/update')->middleware('Api1UUserAuth');
Route::post('/uuser/phone_data','api1/uuser.User/phoneData')->middleware('Api1UUserAuth');
//报告
Route::get('/uuser/reports','api1/uuser.Report/index')->middleware('Api1UUserAuth');
Route::get('/uuser/report/:id','api1/uuser.Report/info')->middleware('Api1UUserAuth');
Route::post('/uuser/report/save','api1/uuser.Report/save')->middleware('Api1UUserAuth');
//申请事项
Route::get('/uuser/applies','api1/uuser.Apply/index')->middleware('Api1UUserAuth');
Route::get('/uuser/apply/:id','api1/uuser.Apply/info')->middleware('Api1UUserAuth');
Route::post('/uuser/apply/save','api1/uuser.Apply/save')->middleware('Api1UUserAuth');
//请假
Route::get('/uuser/holidays','api1/uuser.Holiday/index')->middleware('Api1UUserAuth');
Route::get('/uuser/holiday/:id','api1/uuser.Holiday/info')->middleware('Api1UUserAuth');
Route::post('/uuser/holiday/save','api1/uuser.Holiday/save')->middleware('Api1UUserAuth');
Route::post('/uuser/holiday/complete','api1/uuser.Holiday/complete')->middleware('Api1UUserAuth');
Route::post('/uuser/holiday/cancel','api1/uuser.Holiday/cancel')->middleware('Api1UUserAuth');
Route::post('/uuser/holiday/continue','api1/uuser.Holiday/addMore')->middleware('Api1UUserAuth');
//尿检
Route::get('/uuser/urans','api1/uuser.Uran/index')->middleware('Api1UUserAuth');
Route::get('/uuser/uran/:id','api1/uuser.Uran/info')->middleware('Api1UUserAuth');
//news
Route::get('/uuser/news','api1/Article/index',['client_tag'=>1]);
Route::get('/uuser/new/:id','api1/Article/info');
//sms
Route::post('/uuser/loginsms','api1/Sms/loginSms',['client_tag'=>1]);
Route::post('/uuser/regsms','api1/Sms/regSms',['client_tag'=>1]);

//-----------------------------------------------------//
//--------------------管理端---------------------------//
//-----------------------------------------------------//
Route::any('/manage/test/:mothed','api1/manage.Test/:mothed');

Route::post('/manage/applyaccount','api1/manage.ApplyAccount/index');

Route::post('/manage/login','api1/manage.Login/index');
Route::post('/manage/smslogin','api1/manage.Login/sms');
//首页
Route::get('/manage/index','api1/manage.Index/index')->middleware('Api1ManageAuth');

//news
Route::get('/manage/news','api1/Article/index',['client_tag'=>2]);
Route::get('/manage/new/:id','api1/Article/info');
//sms
Route::post('/manage/loginsms','api1/Sms/loginSms',['client_tag'=>2]);
Route::post('/manage/regsms','api1/Sms/regSms',['client_tag'=>2]);
//报告
Route::get('/manage/reports','api1/manage.Report/index')->middleware('Api1ManageAuth');
Route::get('/manage/report/:id','api1/manage.Report/info')->middleware('Api1ManageAuth');
//申请事项
Route::get('/manage/applies','api1/manage.Apply/index')->middleware('Api1ManageAuth');
Route::get('/manage/apply/:id','api1/manage.Apply/info')->middleware('Api1ManageAuth');
Route::post('/manage/apply/check','api1/manage.Apply/check')->middleware('Api1ManageAuth');
//人员搜索
Route::get('/manage/search/uuserinfo/:id','api1/manage.Search/uuserInfo')->middleware('Api1ManageAuth');
Route::post('/manage/search/uuser','api1/manage.Search/uuserByName')->middleware('Api1ManageAuth');
Route::post('/manage/search/byareas','api1/manage.Search/uuserByAreas')->middleware('Api1ManageAuth');
//尿检
Route::get('/manage/urans','api1/manage.Uran/index')->middleware('Api1ManageAuth');
Route::get('/manage/uran/notify','api1/manage.Uran/notify')->middleware('Api1ManageAuth');
Route::post('/manage/uran/save','api1/manage.Uran/save')->middleware('Api1ManageAuth');
//请假
Route::get('/manage/holidays/:status','api1/manage.Holiday/index')->middleware('Api1ManageAuth');
Route::get('/manage/holiday/:id','api1/manage.Holiday/info')->middleware('Api1ManageAuth');
Route::post('/manage/holiday/check','api1/manage.Holiday/check')->middleware('Api1ManageAuth');
//QA
Route::get('/manage/qas','api1/manage.Qa/index')->middleware('Api1ManageAuth');
Route::post('/manage/qa/save','api1/manage.Qa/save')->middleware('Api1ManageAuth');
//毒情
Route::get('/manage/drugmesgs','api1/manage.DrugMessage/index')->middleware('Api1ManageAuth');
Route::get('/manage/drugmesg/:id','api1/manage.DrugMessage/info')->middleware('Api1ManageAuth');
Route::post('/manage/drugmesg/save','api1/manage.DrugMessage/save')->middleware('Api1ManageAuth');
Route::post('/manage/drugmesg/cancel','api1/manage.DrugMessage/cancel')->middleware('Api1ManageAuth');
//我的
Route::get('/manage/me','api1/manage.User/index')->middleware('Api1ManageAuth');
Route::post('/manage/me','api1/manage.User/update')->middleware('Api1ManageAuth');
//消息
Route::get('/manage/system_messages','api1/manage.SystemMessage/index')->middleware('Api1ManageAuth');
Route::post('/manage/system_message/read','api1/manage.SystemMessage/setRead')->middleware('Api1ManageAuth');
//帮扶
Route::get('/manage/helper/userlist','api1/manage.MHelper/userList')->middleware('Api1ManageAuth');
Route::get('/manage/helper/diarylist','api1/manage.MHelper/diaryList')->middleware('Api1ManageAuth');
Route::post('/manage/helper/savediary','api1/manage.MHelper/saveDiary')->middleware('Api1ManageAuth');
Route::post('/manage/helper/deletediary','api1/manage.MHelper/deleteDiary')->middleware('Api1ManageAuth');
Route::get('/manage/helper','api1/manage.MHelper/index')->middleware('Api1ManageAuth');
//统计
Route::post('/manage/tongji','api1/manage.Statistics/index')->middleware('Api1ManageAuth');
//Route::post('/manage/tongji/holiday','api1/manage.Statistics/holiday')->middleware('Api1ManageAuth');
//Route::post('/manage/tongji/userusers','api1/manage.Statistics/userUsers')->middleware('Api1ManageAuth');
//Route::post('/manage/tongji/uran','api1/manage.Statistics/uran')->middleware('Api1ManageAuth');
//Route::post('/manage/tongji/mhelper','api1/manage.Statistics/mhelper')->middleware('Api1ManageAuth');
//风险评估
Route::get('/manage/estimate/:uuid','api1/manage.Estimates/listByUser')->middleware('Api1ManageAuth');
// 决定书
Route::get('/manage/decision','api1/manage.Decision/index')->middleware('Api1ManageAuth');
Route::post('/manage/decision/save','api1/manage.Decision/save')->middleware('Api1ManageAuth');
// 康复计划
Route::get('/manage/recoveryplan','api1/manage.RecoveryPlan/index')->middleware('Api1ManageAuth');
Route::post('/manage/recoveryplan/save','api1/manage.RecoveryPlan/save')->middleware('Api1ManageAuth');
// 社戒社康协议
Route::get('/manage/agreement','api1/manage.AgreementAPI/index')->middleware('Api1ManageAuth');