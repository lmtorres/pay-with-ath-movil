<?php
?>
<html>
<body>
<div id="ATHMovil_Checkout_Button" style="display:none;"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
	function getUrlVars()
	{
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	console.log(getUrlVars());

	let ATHM_Checkout = {

		env: 'sandbox',
		publicToken: 'sandboxtoken01875617264',

		timeout: 600,

		theme: 'btn',
		lang: 'en',

		total: 1.00,
		/*tax: 1.00,
		subtotal: 1.00,

		metadata1: 'metadata1 test',
		metadata2: 'metadata2 test',

		items: [
			{
				"name": "First Item",
				"description": "This is a description.",
				"quantity": "1",
				"price": "1.00",
				"tax": "1.00",
				"metadata": "metadata test"
			},
			{
				"name": "Second Item",
				"description": "This is another description.",
				"quantity": "1",
				"price": "1.00",
				"tax": "1.00",
				"metadata": "metadata test"
			}
		],*/

		onCompletedPayment: function (response)
		{
			$.post( "/?wc-api=athm_success", {
				id: '<?php echo $_GET['orderId']?>',
				result: 'success'
			}, function( data ) {
				window.location = '<?php echo $_GET['redirectUrl'] ?>';
			});
		},

		onCancelledPayment: function (response)
		{
			$.post( "/?wc-api=athm_success", {
				id: '<?php echo $_GET['orderId']?>',
				result: 'error'
			}, function( data ) {
				history.back();
			});
		},

		onExpiredPayment: function (response)
		{
			$.post( "/?wc-api=athm_success", {
				id: '<?php echo $_GET['orderId']?>',
				result: 'error'
			}, function( data ) {
				history.back();
			});
		}
	}
</script>

<script src="https://www.athmovil.com/api/js/v2/athmovilV2.js"></script>
<script>
	setTimeout(function(){
		$('#ATHMovil_Checkout_Button').click();
	}, 2000);
</script>

</body>
</html>
