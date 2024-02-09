<?php
//=========================================================================
/*
ISSUE:
Need to remove DB records earlier than Jan 2023.
DELETE FROM `changes` WHERE `timestamp` < 1672740470;

Need to removes user records greater than 9.
DELETE FROM `changes` WHERE `user` > 9;
*/

$files_used[] = 'views/changes.php'; //DEBUG
?>

<form method="post" style="float: left;">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="submit" name="view" value="Dashboard" class="btn">
</form>

<div class="h60"></div>

<?php
// Delete records from changes table older than 28 days
// $timestamp_minus_1_month = strtotime("-28 days");
// $sql = "DELETE FROM `changes` WHERE `timestamp` < $timestamp_minus_1_month";
// $db_listings->query($sql);

// Need users name
$sql = "SELECT id,name FROM `user`";
$results = $db_listings->query($sql);
$lookup_users = $results->fetchAll(PDO::FETCH_KEY_PAIR);

$sql = "SELECT * FROM `changes` ORDER BY `rowid` DESC";
$results = $db_listings->query($sql);
$changes = $results->fetchAll(PDO::FETCH_ASSOC);

$initials = [
	'np' => 'New Price: ',
	'c1' => 'Comp1: ',
	'c2' => 'Comp2: ',
	'c3' => 'Comp3: ',
	'id' => 'ID(s): ',
	's' => 'SKU(s): ',
	'pp' => 'Previous Price: ',
	'pb' => 'Packaging Band: ',
	'pn' => 'Product Name: ',
	'pa' => 'Advertising(%): ',
	'c' => 'Courier: ',
	'v' => 'Variation: ',
	'lv' => 'Lowest Variation Weight: ',
	'cp' => 'Cost Per Unit: ',
	
	'nt' => 'Note: ',
	'id1' => 'ID1: ',
	'id2' => 'ID2: ',
	'id3' => 'ID3: ',
	'ty1' => 'TYPE1: ',
	'ty2' => 'TYPE2: ',
	'ty3' => 'TYPE3: ',
];

$prices = [
	'np' => 1,
	'pp' => 1,
	'c1' => 1,
	'c2' => 1,
	'c3' => 1,
];

$tmp = [];
$users = [];
foreach ($changes as $i => $vals) {
	$date = date('Y-m-d', $vals['timestamp']);
	$time = date('H:i:s', $vals['timestamp']);
	
	$username = $lookup_users[$vals['user']];
	
	$explode_changes = [];
	if( false !== strpos($vals['changes'], '{s}') ){ $explode_changes = [$vals['changes']]; } // SKUs
	elseif( '' != $vals['id'] ){ $explode_changes = explode('_', $vals['changes']); }
	elseif( '' == $vals['id'] ){ $users[$date][$username][$time][] = 'EXPORT: ' . $vals['changes'] . ' (zip file in export_backups)'; }
	
	foreach( $explode_changes as $current_changes ){
		$changes = [];
		$flds = explode('{', $current_changes);
		
		$platform = $flds[0];
		foreach( $flds as $j => $fld ){
			if( $j ){
				list($fld_name, $change) = explode('}', $fld);
				
				// Price matrix update
				if( '{' == substr($current_changes, 0,1) && '}' == substr($current_changes, 2,1) ){
					$platform = $fld_name;
					
					// file_put_contents('debug', $current_changes."\n", FILE_APPEND);

					/*
					$ids_arr = explode(',', $vals['id']);
					$prices_arr = explode(',', $change);

					$tmp = [];
					foreach ($ids_arr as $k => $id) {
						echo "$k<br>";
						list($orig, $new) = explode('>', $prices_arr[$k]);
						$tmp[] = "ID:$id (&pound;$orig -> &pound;$new)";
					}

					$changes[] = implode(' | ', $tmp);
					*/
				}
				else{
					$prefix = isset($prices[$fld_name]) ? $prefix = '&pound;' : '';

					if( false !== strpos($change, '>') ){
						list($orig, $new) = explode('>', $change);

						if( isset($orig) ){
							if( '' == $orig ){ $change = "$prefix$new"; }
							elseif( '' == $new ){ $change = "removed $prefix$orig"; }
							else{ $change = "$prefix$orig -> $prefix$new"; }
						}
					}

					$changes[] = $initials[$fld_name] . $change;
				}
			}
		}
		
		if( !empty($changes) ){
			$platform_name = isset($lookup_platform[$platform]) ? $lookup_platform[$platform] : '';

			if( 'l' == $platform || '' == $platform_name ){ $platform_name = 'All Platforms'; }

			$changes_str = "$platform_name - " . implode(' | ', $changes);
			
			$id = $vals['id'];
			$id = '' != $id ? "{{$id}}" : '';
			
			foreach( $lookup_platform as $key => $val ){
				if( "{$key}" == substr($vals['changes'], 0,3) ){
					$id = '';
					break;
				}
			}

			// if(  '{e}' == substr($vals['changes'], 0,3)
			//   || '{a}' == substr($vals['changes'], 0,3)
			//   || '{p}' == substr($vals['changes'], 0,3)
			//   || '{w}' == substr($vals['changes'], 0,3)
			// ){ $id = ''; }

			$changes_str = "$platform_name$id - " . implode(' | ', $changes);

			if( !isset($users[$date][$username][$time][ $vals['id'].'_'.$vals['changes'] ]) ){
				$users[$date][$username][$time][ $vals['id'].'_'.$vals['changes'] ] = $changes_str;
			}
			else{
				$users[$date][$username][$time][ $vals['id'].'_'.$vals['changes'] ] = $users[$date][$username][$time][ $vals['id'].'_'.$vals['changes'] ] .' / '. $changes_str;
			}
		}
	}
}

$count = [];
$data = [];
foreach ($users as $date => $v) {
	foreach ($v as $user => $v2) {
		foreach ($v2 as $time => $v3) {
			$count[$date][$user][$time] = count($v3);
			$data[] = [$date, $user, $time, count($v3)];
		}
	}
}

$args_array_to_table = [
	'tbl_class' => 'style-tbl',
	'header' => ['Date','User','Time','Total','Changes'],
	'body' => $data,
];

echo array_to_table_fnc($args_array_to_table, $users);