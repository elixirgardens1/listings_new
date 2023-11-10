function enable_disable_fnc(true_false)
{
	if( true_false ){
		$('#btn_sbmt').prop('disabled',false);
		$('#btn_sbmt').removeClass('btn-disabled');
		$('#btn_sbmt').addClass('btn');
	}
	else{
		$('#btn_sbmt').prop('disabled',true);
		$('#btn_sbmt').removeClass('btn');
		$('#btn_sbmt').addClass('btn-disabled');
	}
	
	$('.err_msg_display').html('');
}