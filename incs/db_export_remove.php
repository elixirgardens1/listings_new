<?php
//=========================================================================
// INFO:
// Script uses record_changes_fnc(). The data that gets added to the 'changes'
// database table can be added as a single array: 
// 'rec' => ['id', 'changes, 'user', 'timestamp']
// or as a multi-dim array:
// 'recs' => [
//     'id1' => 'changes1',
//     'id2' => 'changes2',
//     'id3' => 'changes3',
//     ...
//  ],
//  'user' => $user,
//  'timestamp' => $timestamp,
//=========================================================================
if( isset($_POST['export_remove']) ){
	$files_used[] = 'incs/export_remove.php'; //DEBUG

	$timestamp = time();
	
	$ids = [];
	foreach ($_POST['export_remove'] as $val) {
		$ids[] = $val;
	}

	// Export listings
	if( 'e' == $_POST['export_remove_option_hidden'] ){
		$ids_in = implode("','", $ids);
		
		$sql = "SELECT id,new_price FROM 'listings_ebay' WHERE `id` IN ('$ids_in')";
		$results = $db_listings->query($sql);
		$new_prices_ebay = $results->fetchAll(PDO::FETCH_KEY_PAIR);
		
		$sql = "SELECT id,new_price FROM 'listings_{$lookup_platform[ $_POST['platform'] ]}' WHERE `id` IN ('$ids_in')";
		$results = $db_listings->query($sql);
		$new_prices = $results->fetchAll(PDO::FETCH_KEY_PAIR);
		
		$sql = "SELECT id,sku FROM 'skus' WHERE `id` IN ('$ids_in') AND `source` = '{$_POST['platform']}'";
		
		$results = $db_listings->query($sql);
		$skus = $results->fetchAll(PDO::FETCH_ASSOC); // Cannot be 'FETCH_KEY_PAIR' because of multiple 'skus' per 'id'

		$header1 = "skus";
		$header2 = "new_price";

		$recs = [];
		$recs[] = "$header1,$header2";
		
		foreach( $skus as $i => $rec ){
			// If 'id' exists in 'new prices' array and not empty, then use new price value. Otherwise use Ebay price.
			$new_prices_ = isset($new_prices[ $rec['id'] ]) && '' != $new_prices[ $rec['id'] ] ? $new_prices[ $rec['id'] ] : $new_prices_ebay[ $rec['id'] ];
			$recs[] = $rec['sku'].','.$new_prices_;
		}
	}

	// Remove (hide) listings
	elseif( 'r' == $_POST['export_remove_option_hidden'] ){
		$stmt = $db_listings->prepare("UPDATE `listings` SET `remove` = ?, `timestamp` = ? WHERE `id_lkup` = ?");
		
		$recs_changes = [];
		$db_listings->beginTransaction();
		foreach ($ids as $id) {
			//================================================================================================================================
			// id_lkup |key   |cat_id |group_ |product_name               |packing |packaging_band |lowest_variation_weight |variation |remove
			//    9159 |agg11 |a244   |a      |Brown Rock Salt x 5kg bag  |b       |             5 |                      1 |        5 |     1
			//    9160 |agg11 |a244   |a      |Brown Rock Salt x 10kg bag |b       |             6 |                      1 |       10 |     1
			//    9162 |agg11 |a244   |a      |Brown Rock Salt x 20kg bag |b       |             7 |                      1 |       20 |     1
			//   13012 |agg11 |a244   |a      |Brown Rock Salt x 1kg bag  |b       |             5 |                      1 |        1 |     1
			//================================================================================================================================
			$stmt->execute([ '1', $timestamp, $id ]);
			$recs_changes[$id] = '1';
		}
		$db_listings->commit();

		//=========================================================================
		// RECORD CHANGES
		//=========================================================================
		// id    |changes
		// 13012 |      1
		//  9159 |      1
		//  9160 |      1
		//  9162 |      1
		//===============
		record_changes_fnc([
			'db'        => $db_listings,
			'recs'      => $recs_changes,
			'user'      => $_POST['user'],
			'timestamp' => $timestamp
		]);
	}

	// Create CSV
	if( 'e' == $_POST['export_remove_option_hidden'] ){
		# $path = dirname(__FILE__).'/export_backups/';
		$path = 'export_backups/';

		$file = 'user' . $_POST['user'] .'_'. $lookup_platform[ $_POST['platform'] ] . '_listings_'.$timestamp.'.csv';
		
		$filepath = $path.$file;

		$file = fopen($filepath,"w");
		fwrite($file,  implode("\n", $recs) );
		fclose($file);
		
		// Backup export as zip
		$zip = new ZipArchive;
		$zipfile = "$filepath.zip";
		$status = $zip->open($zipfile, ZipArchive::CREATE);
		$zip->addFile($filepath);
		$zip->close();
		
		//=========================================================================
		// RECORD CHANGES
		//=========================================================================
		// id |changes                              
		//    |export_backups/user1_ebay_listings_1643296325
		//==================================================
		record_changes_fnc([
			'db'  => $db_listings,
			'rec' => ['', substr($filepath, 0,-4), $_POST['user'], $timestamp],
		]);

		if( file_exists($filepath) ){
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			readfile($filepath);

			unlink($filepath);
		}
	}
}
