<?php
$files_used[] = 'incs/modal_boxes.php'; //DEBUG
?>

<!-- Edit Skus Modal Box -->
<?php $width=660; $height=500; ?>
<div id="modalBgEditSkus" class="modalBg">
	<div id="modalBox" class="modalBox" style="width:<?= $width ?>px; height:<?= $height ?>px;">
		<!-- modal close button -->
		<input type="button"
		name="close_modal"
		class="btn modalClose"
		style="margin-left:<?= $width-20 ?>px; margin-top:-8px; height:26px; border:0; border-radius:20px;"
		value="X">
		
		<!-- Modal Title -->
		<h2 style="font-size: 18px;">Edit SKUs &ndash; <span class="modal_vars"></span></h2>
		
		<!-- Add new skus -->
		<form action="index.php" method="post">
			<input type="hidden" name="add_sku_form">
			<input type="hidden" class="modal_id" name="modal_id">
			<input type="hidden" name="posY" value="0">
			<input type="hidden" name="platform" value="<?= $platform_post ?>">
			<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
			<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
			<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
			<input type="hidden" name="view" value="<?= $_POST['view'] ?>">
			
			<!-- Text Input to Add New SKU -->
			<div class="mb10 ta-l ml50 fl-l">
				<!--
				Nb. autofocus doesn't work in Firefox so JS solution required.
					The required code - $('.add_sku_txt').focus(); - has been
					added to the onclick event. Otherwise it doesn't work due
					to the modal being hidden when page loads.
				-->
				<input type="text" class="w350 add_sku_txt focus_txt" name="add_sku_txt[]" autocomplete="off">
				<div class="h10"></div>
				<input type="text" class="w350 add_sku_txt" name="add_sku_txt[]" autocomplete="off">
				<div class="h10"></div>
				<input type="text" class="w350 add_sku_txt" name="add_sku_txt[]" autocomplete="off">
			</div>
			
			<input type="submit" class="btn w120 dis-no fl-r" id="add_sku_btn" value="add sku(s)">
		</form>
		<div class="cl-b"></div>
		
		
		<!-- Delete existing skus -->
		<form action="index.php" method="post">
			<input type="hidden" name="del_skus_form">
			<input type="hidden" class="modal_id" name="modal_id">
			<input type="hidden" name="posY" value="0">
			<input type="hidden" name="platform" value="<?= $platform_post ?>">
			<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
			<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
			<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
			<input type="hidden" name="view" value="<?= $_POST['view'] ?>">
			
			<!--
			Display existing sku(s), checkboxes to delete and delete
			button (if any checkboxes checked : requires js code below)
			-->
			<div class="mb10 ta-l ml50" style="height:300px;">
				<span class="display_skus fl-l mr20"></span>
				<span class="display_skus2 fl-l mr20"></span>
				<span class="display_skus3 fl-l mr20"></span>
			</div>
			<div class="fl-r">
				<input type="submit" class="btn w80 dis-no" id="del_sku" value="delete">
			</div>
		</form>
	</div>
</div>


<!-- Edit Comps and IDs Modal Box -->
<?php $width=600; $height=280; ?>
<div id="modalBgEdit" class="modalBg">
	<div id="modalBox" class="modalBox" style="width:<?= $width ?>px; height:<?= $height ?>px;">
		<input type="button"
		name="close_modal"
		class="btn modalClose"
		style="margin-left:<?= $width-20 ?>px; margin-top:-8px; height:26px; border:0; border-radius:20px;"
		value="X">
		
		<h2 style="font-size: 18px;">Edit Comps and IDs &ndash; variation <span class="modal_vars"></span></h2>
		
		<table class="modal-mod" style="margin-left: 50px; margin-top: 30px;">
			<tr>
				<td class="ra">COMP1 (£)</td>
				<td class=""><input type="text" id="comp1" name="comp1" style="width: 60px;" autocomplete="off" data-lpignore="true"></td>
				<td class="ra w30">ID1</td>
				<td class=""><input type="text" id="id1" name="id3" style="width: 100px;" autocomplete="off" data-lpignore="true"></td>
				<td class="ra w50">TYPE1</td>
				<td class="">
					<select id="type1" name="type1">
					<?php foreach( $link_type as $i => $type ){ ?>
						<option value="<?= $i ?>"><?= $type ?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="ra">COMP2 (£)</td>
				<td class=""><input type="text" id="comp2" name="comp2" style="width: 60px;" autocomplete="off" data-lpignore="true"></td>
				<td class="ra">ID2</td>
				<td class=""><input type="text" id="id2" name="id2" style="width: 100px;" autocomplete="off" data-lpignore="true"></td>
				<td class="ra">TYPE2</td>
				<td class="">
					<select id="type2" name="type2">
						<?php foreach( $link_type as $i => $type ){ ?>
							<option value="<?= $i ?>"><?= $type ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="ra">SPON COMP (£)</td>
				<td class=""><input type="text" id="comp3" name="comp3" style="width: 60px;" autocomplete="off" data-lpignore="true"></td>
				<td class="ra">ID3</td>
				<td class=""><input type="text" id="id3" name="id3" style="width: 100px;" autocomplete="off" data-lpignore="true"></td>
				<td class="ra">TYPE3</td>
				<td class="">
					<select id="type3" name="type3">
						<?php foreach( $link_type as $i => $type ){ ?>
							<option value="<?= $i ?>"><?= $type ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			
			<tr>
				<td></td>
				<td colspan="3"><button id="clear_flds" style="width:100%">Clear Fields</button></td>
				<td colspan="2"></td>
			</tr>
		</table>
		
		<div style="font-size:30px; color:red; margin-top:14px;">Do Not Press Enter(Return)</div>
		<div style="font-size:20px; margin-top:10px;">Just press ESC after updating form fields</div>
		
		<input type="hidden" id="modal_id" name="id">
	</div>
</div>

<script>
	$('#clear_flds').on('click', function(){
		let myId = $('#modal_id').val();
		console.log(myId);
		
		for( let i=1; i<=3; i++ ){
			// comp1, comp2, comp3
			$( '#' +myId+ '_comp' +i ).val('');
			$( '#comp' +i ).val('');
			
			// id1, id2, id3
			$( '#' +myId+ '_id' +i ).val('');
			$( '#id' +i ).val('');
			
			updateBtnColour(myId);
			
			// Remove comp prices below 'edit comps & ids' button
			$( '#' +myId+ '_comp_display' ).css({'display': 'none'});
		}
	});
	
	// Display add/edit Skus modal box
	$('.triggerSkuEdit').on('click', function(){
		// Clear add sku text fields & add sku(s) button
		$('.add_sku_txt' ).val('');
		$('#add_sku_btn').css({'display': 'none'});
		
		$('#modalBgEditSkus').css({'display': 'block'});
		
		let fmtSkus = [];
		let fmtSkus2 = [];
		let fmtSkus3 = [];
		
		let myId = $(this).data('id');
		$('.modal_id').val(myId);
		
		let myVars = $(this).data('vars');
		$('.modal_vars').html(myVars);
		
		let mySkus = $(this).data('skus');
		let skusArr = mySkus.split('_-_');
		
		for( let i=0; i<skusArr.length; i++ ){
			if( i < 15 ){
				fmtSkus.push('<input type="checkbox" class="sk" id="sk'+i+'" name="modal_skus[]" value="'+skusArr[i]+'"><label for="sk'+i+'">'+skusArr[i]+'</label>');
			}
			else if( i < 30 ){
				fmtSkus2.push('<input type="checkbox" class="sk" id="sk'+i+'" name="modal_skus[]" value="'+skusArr[i]+'"><label for="sk'+i+'">'+skusArr[i]+'</label>');
			}
			else{
				fmtSkus3.push('<input type="checkbox" class="sk" id="sk'+i+'" name="modal_skus[]" value="'+skusArr[i]+'"><label for="sk'+i+'">'+skusArr[i]+'</label>');
			}
		}
		
		$('.display_skus').html('');
		$('.display_skus2').html('');
		$('.display_skus3').html('');
		if( '' != skusArr[0] ){
			$('.display_skus').html( fmtSkus.join('<br>') );
			$('.display_skus2').html( fmtSkus2.join('<br>') );
			$('.display_skus3').html( fmtSkus3.join('<br>') );
		}
	
		$('.focus_txt').focus();
		
		// mySkus.replace('_-_','<br>')
	});
	
	$('.add_sku_txt' ).on('input', function(){
		if( '' != $(this).val() && $(this).val().length > 2 ){
			$('#add_sku_btn').css({'display': 'block'});
		}
		else{
			$('#add_sku_btn').css({'display': 'none'});
		}
		
		// console.log( $(this).val() );
	});
	
	//=========================================================================
	// Display / hide 'delete' button
	//=========================================================================
	let totalChkd = 0;
	$(document).on('change', '.sk', function(){
	    // Display 'delete' button if any checkbox is checked
	    if( $(this).is(':checked') ){
	    	$('#del_sku').css({'display': 'block'});
	    	// Keep a record of total checkboxes checked
	    	totalChkd++;
	    }
	    // Keep a record of total checkboxes unchecked
	    else{
	    	totalChkd--;
	    }
	    
	    // Only hide 'delete' button if all checkboxes have been unchecked
	    if( 0 === totalChkd ){
	    	$('#del_sku').css({'display': 'none'});
	    }
	});



	$('.triggerModalEdit').on('click', function(){
		$('#modalBgEdit').css({'display': 'block'});
		
		// Update modal box hidden field: 'modal_id'
		let myId = $(this).data('id');
		$('#modal_id').val(myId);
		
		let myVars = $(this).data('vars');
		$('.modal_vars').html(myVars);
		
		for( let i=1; i<=3; i++ ){
			// Populate 'Edit Comps and IDs' modal fields
			let c = $( '#' +myId+ '_comp' +i ).val();
			let id = $( '#' +myId+ '_id' +i ).val();
			let type = $( '#' +myId+ '_type' +i ).val();
			$('.modal-mod #comp' +i ).val( c );
			$('.modal-mod #id' +i ).val( id );
			
			// This sets TYPE drop-downs to first item if empty
			type = '' == type ? 1 : type;

			$('.modal-mod #type' +i ).val(type).prop('checked', true);

			// Update hidden fields when 'Edit Comps and IDs' modal fields change

			// comp1, comp2, comp3
			$('.modal-mod #comp' +i ).on('input', function(){
				let myId = $('#modal_id').val();
				let c_val = $(this).val();
				$( '#' +myId+ '_comp' +i ).val(c_val);
				updateBtnColour(myId);
			});

			// id1, id2, id3
			$('.modal-mod #id' +i ).on('input', function(){
				let myId = $('#modal_id').val();
				let id_val = $(this).val();
				$( '#' +myId+ '_id' +i ).val(id_val);
				updateBtnColour(myId);
			});
			
			// type1, type2, type3
			$('.modal-mod #type' +i ).on('change', function(){
				let myId = $('#modal_id').val();
				let type_val = $(this).val();
				$( '#' +myId+ '_type' +i ).val(type_val);
				// console.log( type_val );
				updateBtnColour(myId);
			});
		}
	});
	
	function updateBtnColour(myId)
	{
		if(
			('' != $( '#' +myId+ '_comp1' ).val() && '' != $( '#' +myId+ '_id1' ).val() ) ||
			('' != $( '#' +myId+ '_comp2' ).val() && '' != $( '#' +myId+ '_id2' ).val() ) ||
			('' != $( '#' +myId+ '_comp3' ).val() && '' != $( '#' +myId+ '_id3' ).val() )
			
			// ('' != $( '#' +myId+ '_comp1' ).val() && '' != $( '#' +myId+ '_id1' ).val() && '' != $( '#' +myId+ '_type1' ).val() ) ||
			// ('' != $( '#' +myId+ '_comp2' ).val() && '' != $( '#' +myId+ '_id2' ).val() && '' != $( '#' +myId+ '_type2' ).val() ) ||
			// ('' != $( '#' +myId+ '_comp3' ).val() && '' != $( '#' +myId+ '_id3' ).val() && '' != $( '#' +myId+ '_type3' ).val() )
		){
			// This static variable is used to keep track of the last button 'id' that had comps and ids updated
			if( typeof updateBtnColour.persistent_id === 'undefined' ){
				updateBtnColour.persistent_id = myId;
			}
			else{
				// remove highlighted border from previous button
				$('[data-id=' +updateBtnColour.persistent_id+ ']').css({'border': 'none'});
			}
			
			// Add colour - shows that at least 1 set of comps and IDs exist
			$('[data-id=' +myId+ ']').css({'color': '#ff0', 'border': 'dotted 3px #fff'});
			updateBtnColour.persistent_id = myId;
		}else{
			// Remove colour - shows that no comps and IDs exist
			$('[data-id=' +myId+ ']').css({'color': '#fff', 'border': 'none'});
		}
	}









	// Close modalBox when escape key is pressed
	$(document).on('keyup',function(e){
		if (e.keyCode === 27) { // esc
			$('#modalBg, #modalBgEdit, #modalBgEditSkus, #del_sku').css({'display': 'none'});
			totalChkd = 0;
		}
	});

	// Close modalBox when cancel is clicked
	$('.modalClose').on('click',function (){
		$('#modalBg, #modalBgEdit, #modalBgEditSkus, #del_sku').css({'display': 'none'});
		totalChkd = 0;
 	});
	
	// Close modalBox when modalBg is clicked
	$('#modalBg, #modalBgEdit, #modalBgEditSkus').on('click',function(){
		$('#modalBg, #modalBgEdit, #modalBgEditSkus, #del_sku').css({'display': 'none'});
		totalChkd = 0;
	});
	
	// Don't hide modal box if clicked directly
	$('#modalBox, #modalBgEdit, #modalBgEditSkus').on('click',function(e){
		e.stopPropagation();
	});
</script>