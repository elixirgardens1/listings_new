<?php
if( isset($_POST['add_sku_form']) ){
	$files_used[] = 'incs/db_add-del_skus.php'; //DEBUG
	
	$stmt = $db_listings->prepare("INSERT INTO `skus` (`id`,`sku`,`source`,`timestamp`) VALUES (?,?,?,?)");
	
	$timestamp = time();
	
	$_POST = sanitize_post_data_fnc($_POST, [
		'my_trim',
		'remove_multiple_whitespace',
		'my_htmlspecialchars',
		'ascii_translit'
	]);
	
	
	foreach( $_POST['add_sku_txt'] as $add_sku_txt ){
		if( '' != $add_sku_txt ){
			//=============================================
			// id    |sku               |source |timestamp
			// 13012 |Rock-Salt-Brown-1 |e      |1643291775
			//=============================================
			$db_listings->beginTransaction();
			$stmt->execute([
				$_POST['modal_id'],
				$add_sku_txt,
				$_POST['platform'],
				$timestamp,
			]);
			$db_listings->commit();
			
			//=========================================================================
			// RECORD CHANGES TO sku(s)
			//=========================================================================
			// id    |changes
			// 13012 |e{s}Rock-Salt-Brown-1
			//=============================
			record_changes_fnc([
				'db'  => $db_listings,
				'rec' => [
					$_POST['modal_id'],
					$_POST['platform']."{s}$add_sku_txt",
					$_POST['user'],
					$timestamp
				],
			]);
		}
	}
	
}

elseif( isset($_POST['del_skus_form']) ){
	$timestamp = time();
	
	foreach( $_POST['modal_skus'] as $sku ){
		$sql = "DELETE FROM `skus`
				WHERE `id` = '{$_POST['modal_id']}' AND `sku` = '$sku' AND `source` = '{$_POST['platform']}'";
		$db_listings->query($sql);
		
		
		//=========================================================================
		// RECORD DELETES TO sku(s)
		//=========================================================================
		// id    |deletes
		// 13012 |e{s}Rock-Salt-Brown-1
		//=============================
		record_deletes_fnc([
			'db'  => $db_listings,
			'rec' => [
				$_POST['modal_id'],
				$_POST['platform']."{s}$sku",
				$_POST['user'],
				$timestamp
			],
		]);
	}
}