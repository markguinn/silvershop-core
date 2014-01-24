(function($) {
 
    $.entwine('ss', function($){
        $('#Form_ItemEditForm_action_PrintInvoice').entwine({
			onclick: function(e){
				var $orderHeading = $('#Form_ItemEditForm_Title').html(),
					$orderSubHeading = $('#Form_ItemEditForm_Title').next().html(),
					$orderInfo = $('#OrderInformation').html();
									
				var printWindow = window.open(),
					srcWindowPath = printWindow.opener.location.origin,
					printWindowDoc = printWindow.document,
					headEl = printWindowDoc.getElementsByTagName('head')[0],
					logo = '<img class="logo" src="' + srcWindowPath + '/themes/daywind/images/layout/daywind-logo.png" />';
					
				headEl.innerHTML = '<link rel="stylesheet" type="text/css" href="' + srcWindowPath + '/shop/css/order_print.css" />';
				printWindowDoc.body.innerHTML =  logo + '<h2>Invoice - ' + $orderHeading + '</h2><h4>' + $orderSubHeading + '</h4>' + $orderInfo;
				printWindow.print();

				return false;
			}

        });
        
        $('#Form_ItemEditForm_action_PrintPackingSlip').entwine({
			onclick: function(e){
				var	url = '/daywindorder/printpackingslip/' + $(this).data('orderid');
		        var packingSlipWindow = window.open(url);
		        packingSlipWindow.print();
		        
		        return false;
	        }
        });
    });
 
}(jQuery));