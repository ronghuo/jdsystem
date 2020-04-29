
$(function(){

    if(areaids.length>0){

        var i =0,len = areaids.length;

        for(;i<len;i++){

            if($('#a'+areaids[i]).length>0){
                $('#a'+areaids[i]).closest('ul').show();
            }
        }
    }


    $('.togglebtn').on('click',function(){

        $(this).next('ul').toggle();

    });

    $('#subform').on('click',function () {


        return true;
    });

    var $areaCheckers = $(':checkbox[name^=lv]');
    $areaCheckers.on('click', function () {
        var name = escapeJquery(this.name);
        var self = $(this);
        if (self.is(':checked')) {
            var checkedCheckers = $areaCheckers.filter(':checked').not(self);
            if (checkedCheckers.length > 0) {
                checkedCheckers.attr('checked', false);
            }
        }
    });

});