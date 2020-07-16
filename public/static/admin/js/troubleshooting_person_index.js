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

});