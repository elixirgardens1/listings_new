<?php
if( isset($_POST['modify_config_db']) ){
	
	$_POST = sanitize_post_data_fnc($_POST, [
		'my_trim',
		'remove_multiple_whitespace',
		'my_htmlspecialchars',
		'ascii_translit'
	]);
	
	$orig_vals_json = str_replace('QUOT', '"', $_POST['orig_vals']);
	$orig_vals = json_decode($orig_vals_json, true);
	
	//DEBUG
	echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($_POST); echo '</pre>';
	echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($orig_vals); echo '</pre>'; die();
}
