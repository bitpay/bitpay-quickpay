function setDefaultCode($looper){
    if($looper == 0){
        setTimeout(function(){ 
            generateBPQPCode('paywithbitpaybutton'); 
            }, 500);
    }
}

function generateBPQPCode(button){
   
    //clear out other text boxes
    jQuery('.bp_input').each(function (i, obj) {
       if (obj.id != 'gen_' + button && obj.id != 'desc_' + button) {
          obj.value = ''
       }
    });
    val = jQuery('#gen_'+button).val();
    val = val.trim();
    if (val.length == 0 || !jQuery.isNumeric(val)){
        val = val.substring(0, val.length - 1)
        jQuery('#gen_' + button).val(val)
        if (!jQuery.isNumeric(val)){
        jQuery("#generated_code").html(' <b>your generated code will appear here</b>');
        }
        return
    }
   
   if(isNaN(parseInt(val)) || parseInt(val) == 0){
    alert('Minimum of 1.00 is required')
    return
   }
  
    var str;
    
    str = '[bitpayquickpay name ="'+button+'"';
    str += ' price = "'+val+'"';

    //is there a description?
    var d = jQuery('#desc_'+button).val();
    d = d.trim();
    
    if(d.length>0){
        str+=' description = "'+d+'"';
    }
    var chk =  jQuery('#chk_'+button).is(':checked');
    if(chk){
        str+=' allow_override = "true"';
    }

    str+=']'
    jQuery("#generated_code").text(str);

}

function BPQP_CleanDefault(val){
    console.log('aaa',val)
    if (val.length == 0 || !jQuery.isNumeric(val)){
        val = val.substring(0, val.length - 1)
            jQuery("#bitpayquickpay_option_default_amount").val(val)
        return
    }   
}


function BPQP_Clean(val,button){
    if (val.length == 0 || !jQuery.isNumeric(val)){
        val = val.substring(0, val.length - 1)
        jQuery('#gen_' + button).val(val)
        if (!jQuery.isNumeric(val)){
        jQuery("#generated_code").html(' <b>your generated code will appear here</b>');
        }
        return
    }

    //clear out other text boxes
    jQuery('.bp_input').each(function (i, obj) {
       if (obj.id != 'gen_' + button) {
          obj.value = ''
       }
    });
    
}
