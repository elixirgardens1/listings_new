<?php
$files_used[] = 'js/js_connect_btns_to_forms.php'; //DEBUG
?>
<script>
$(function() {
	$('input[name="export_remove"]').on('click', function() {
		document.main_form.submit();
	});
	// Link 'Edit' buttons to form submit
	$('input[name="edit"]').on('click', function() {
		var id = $(this).attr('data-id');
		var form = document.getElementById("form_edit_" + id);
		form.submit();
	});
	// Link 'Add' buttons to form submit
	$('input[name="add"]').on('click', function() {
		var id = $(this).attr('data-id');
		var form = document.getElementById("form_add_" + id);
		form.submit();
	});
	// Link 'Add prime' buttons to form submit
	$('input[name="add_prime"]').on('click', function() {
		var id = $(this).attr('data-id');
		var form = document.getElementById("form_add_prime_" + id);
		// console.log(form);
		form.submit();
	});
	// Link 'Add SKUs' buttons to form submit
	$('input[name="add_skus"]').on('click', function() {
		var id = $(this).attr('data-id');
		var form = document.getElementById("form_add_skus_" + id);
		// console.log(form);
		form.submit();
	});

	// NEW_PRICE_MATRIX
	$('input[name="update_matrix_prices"]').on('click', function() {
		document.matrix_prices_form.submit();
	});
});
</script>