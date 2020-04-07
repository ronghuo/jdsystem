/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var myueditorconfig = ['fullscreen','source', 'bold', 'italic', 'underline', 'fontborder', 'strikethrough', '|',
        'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
        'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|',
        'forecolor', 'backcolor', '|', 'fontfamily', 'fontsize', '|',
        'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|',
        'link', 'unlink', 'map', '|', 'simpleupload', 'insertimage', 'insertvideo','drafts'];
    

//删除草稿
function delDrafts(){
    var editid = 'editor',
    dkey = getEditDraftKey(editid),ue = UE.getEditor(editid);
    ue.removePreferences(dkey);
}

//获取百度编辑器的草稿KEY
function getEditDraftKey(editorid){
   return ( location.protocol + location.host + location.pathname ).replace( /[.:\/]/g, '_' ) + editorid+'-drafts-data';
}