$(function() {

    $('a[id^=reader_]').on('click', function() {
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '排查详情', 600, 600);
    });

    $('a[id^=assign_]').on('click',function(){
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '排查人员指派', 720, 560);
    });

    $('#btnImport').on('click',function(){
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '导入人员', 600, 360);
    });

    $('#btnExport').on('click',function(){
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        $('form').attr('action', url);
        $('form').submit();
    });

});