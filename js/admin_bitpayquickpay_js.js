function generateBPQPCode(val,button){
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
       console.log(obj.id)
       if (obj.id != 'gen_' + button) {
          obj.value = ''
       }
    });
    var str;
    val = val.trim();
    str = '[bitpayquickpay name ="'+button+'"';
    str += ' price = "'+val+'"';
    str+=']'
    //[bitpayquickpay name ="paywithbitpaysupportedcurrencies" price="1.50"]
    jQuery("#generated_code").text(str);

}
