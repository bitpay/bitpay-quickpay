document.write('<script type="text/javascript" src="https:////bitpay.com/bitpay.min.js"></script>');
function showBpQp(env,api,price){
    if (env == 'test'){
        bitpay.enableTestMode()
    }
     var myObj = {
        price: price
     }
   var saveData = jQuery.ajax({
       type: 'POST',
       url: api,
        data: myObj,
        dataType: "text",
      
       success: function (resultsData) {
            response = JSON.parse(resultsData);
            console.log(response.data.id)
            //invoiceID = invoiceID.replace(/['"]+/g, '')
            bitpay.showInvoice(response.data.id)
       }
   });
}
