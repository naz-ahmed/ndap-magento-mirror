jQuery(document).ready(function() {
    //$('#wait_1').hide();
    
    jQuery('#drop_1').change(function(){
            //$('#wait_1').show();
            //$('#result_1').hide();
            jQuery.get("/fitter/dropdown.php", {
                                    func: "drop_1",
                                    drop_var: jQuery('#drop_1').val()
                                    }, function(response){
                                                           // $('#result_1').fadeOut();
                                                            setTimeout("finishAjax('result_1', '"+escape(response)+"')", 400);
                                                            });
        return false;
        });

});

function finishAjax(id, response) {
  jQuery('#wait_1').hide();
  jQuery('#'+id).html(unescape(response));
  jQuery('#'+id).fadeIn();
}

function finishAjax2(id, response) {
  jQuery('#wait_2').hide();
  jQuery('#'+id).html(unescape(response));
  jQuery('#'+id).fadeIn();
}