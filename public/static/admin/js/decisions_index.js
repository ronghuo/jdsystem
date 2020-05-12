$(function(){

    $imageWrappers = $('.imageWrapper');
    for (var i = 0; i < $imageWrappers.length; i++) {
        new Viewer($imageWrappers[i], {
            url: 'data-original'
        });
    }

});