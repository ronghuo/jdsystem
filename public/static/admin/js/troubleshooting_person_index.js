$(function() {

    $('a[id^=reader_]').on('click', function() {
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '排查详情', 500, 600);
    });

});