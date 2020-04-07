
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

});