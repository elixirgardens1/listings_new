<?php
$files_used[] = 'js/js_multiselect_chkbxs.php'; //DEBUG
?>
<script>
	$(function() {
		/*=========================================================================
		| Multiselect checkboxes
		|========================================================================*/
		var clickData = {isShift: false};

		function updateTickersCount() {
			var $selTickers = $('input[type=checkbox]', $('input[data-modal-tickers-master=true]').closest('table')).not($('input[data-modal-tickers-master=true]'));
			var amountTicked = $selTickers.filter(':checked').length;
			var amountVisibleTicked = $selTickers.filter(':checked').filter(':visible').length;
			var totalAmount = $selTickers.length;
			var isAllSelectedText = 'selected';
			if (!amountTicked || amountTicked == totalAmount) {
				isAllSelectedText = 'all';
			}
			if (!amountTicked) {
				amountTicked = 'No';
			}
			if (amountVisibleTicked < amountTicked) {
				$('.modal-ticker-warning').show();
			} else {
				$('.modal-ticker-warning').hide();
			}
			$('.modal-ticker-counter').text(amountTicked + ' orders selected');
			$('.modal-ticker-isallselected').text(isAllSelectedText);

			// console.log(amountTicked);

			// Disable 'submit' button if no checkboxes are checked
			if( 'No' == amountTicked ){
				$('input[value="submit"]').attr("disabled", true);
				$('input[value="submit"]').removeClass('btn');
				$('input[value="submit"]').addClass('btn-disabled');
			}
			else{
				$('input[value="submit"]').attr("disabled", false);
				$('input[value="submit"]').removeClass('btn-disabled');
				$('input[value="submit"]').addClass('btn');
			}
		}
		updateTickersCount();

		$('input[data-modal-tickers-master=true]').click(function(){
			var $selMaster = $(this);
			var $selTable = $(this).closest('table');
			var $selTicks = $('input[type=checkbox]', $selTable).not($selMaster);
			$selTicks.each(function() {
				if ($(this).is(':visible')) {
					if ($selMaster.is(':checked')) {
						$(this).prop('checked', true);
						$(this).closest('tr').attr('data-modal-tickers-ticked', 'true');
					} else {
						$(this).prop('checked', false);
						$(this).closest('tr').attr('data-modal-tickers-ticked', 'false');
					}
				}
			});
		});
		$('label.cbx').click(function(e){
			var id = $(this).attr('for');
			if (id) {
				var checkboxEvent = new $.Event('click');
				clickData.isShift = e.shiftKey;
				$('#' + id).click();
				return false;
			}
		});
		$('input[type=checkbox]', $('input[data-modal-tickers-master=true]').closest('table')).bind('click', clickData, function(e){
			var $selMaster = $('input[data-modal-tickers-master=true]');
			var $selTable = $(this).closest('table');
			var $selTicks = $('input[type=checkbox]', $selTable).not($selMaster);
			var $selTicksChecked = $selTicks.filter(':checked');
			$(this).closest('tr').attr('data-modal-tickers-ticked', $(this).is(':checked'));
			if (e.data.isShift && !$(this).is($selLastChecked)) {
				var $selThisRow = $(this).closest('tr');
				var $selLastRow = $selLastChecked.closest('tr');
				if ($selLastRow.prevAll().filter($selThisRow).length) {
					$($selLastRow).prevUntil($selThisRow).add($selThisRow).each(function() {
						if ($(this).is(':visible')) {
							$(this).attr('data-modal-tickers-ticked', $selLastChecked.is(':checked'));
							$('input[type=checkbox]', this).prop('checked', $selLastChecked.is(':checked'));
						}
					});
				}
				if ($selLastRow.nextAll().filter($selThisRow).length) {
					$($selLastRow).nextUntil($selThisRow).add($selThisRow).each(function() {
						if ($(this).is(':visible')) {
							$(this).attr('data-modal-tickers-ticked', $selLastChecked.is(':checked'));
							$('input[type=checkbox]', this).prop('checked', $selLastChecked.is(':checked'));
						}
					});
				}
				$selTicksChecked = $selTicks.filter(':checked');
			}
			if (!$selTicksChecked.length) {
				$selMaster.prop('checked', false);
				$selMaster.prop('indeterminate', false);
			} else {
				if ($selTicks.length == $selTicksChecked.length) {
					$selMaster.prop('checked', true);
					$selMaster.prop('indeterminate', false);
				} else {
					$selMaster.prop('checked', true);
					$selMaster.prop('indeterminate', true);
				}
			}
			$selLastChecked = $(this);
			updateTickersCount();
		});
	});
</script>