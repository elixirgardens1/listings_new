<?php
/*
http://192.168.0.24/listings_new/
http://localhost/listings_new/index.php
http://localhost/elixir/listings_new/index.php


To convert the existing LISTINGS database into the required format run the following script:
http://192.168.0.24/listings_new/convert_db_format/modify_tables.php


*/

//DEBUG
// echo '<div style="position:fixed; margin-left:1710px; margin-top:40px;"><pre style="background:#002; color:#fff;">'; print_r($_POST); echo '</pre></div>';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Default to Login view
$view = isset($_POST['view']) ? $_POST['view'] : 'Login';

//DEBUG
$files_used = [];
$files_used[] = "VIEW == $view";
$files_used[] = 'index.php';

$platform_post = 'e';
if( isset($_POST['platform']) ){ $platform_post = $_POST['platform']; }

// Enables the 'errors' page to reload when a '--SELECT--' menu option is selected
if( isset($_POST['fix_errors_view_cat']) || isset($_POST['fix_errors_view_platform']) ){
	$session['view'] = 'errors';

	$_POST['cat'] = $_POST['fix_errors_view_cat'];
	$_POST['platform'] = $_POST['fix_errors_view_platform'];
}

require_once 'incs/db_connections.php';
require_once 'incs/php_functions.php';

$db_listings = new PDO("sqlite:$listings_db_path");
$db_stock    = new PDO("sqlite:$stock_control_db_path");


// $sql = "SELECT * FROM `rooms_lookup`";
// $results = $db_stock->query($sql);
// $results = $results->fetchAll(PDO::FETCH_COLUMN);

require_once 'incs/sessions.php';
require_once 'incs/lookups.php';

//DEBUG
// echo '<pre style="background:#002; color:#fff;">'; print_r($lookup_platform); echo '</pre>'; die();

// INSERT, UPDATE, DELETE DATA
require_once 'incs/db_add_del_skus.php';         // add / edit skus
require_once 'incs/db_add_to_listing.php';       // add to listing
require_once 'incs/db_add_prime_to_listing.php'; // add prime to listing
require_once 'incs/db_save_listing.php';         // save to listing
require_once 'incs/db_update_matrix_prices.php';  // save to listing

require_once 'incs/db_add_new_listings.php';
require_once 'incs/db_add_new_product.php';
require_once 'incs/db_export_remove.php';
require_once 'incs/db_copy_couriers.php';
require_once 'incs/db_delete_prime.php';
require_once 'incs/db_update_config.php';
require_once 'incs/db_import.php';

// Order sub categories in $session['lookup_prod_cats'] array so that sub categories drop-down appears in alphabetical order.
$tmp = [];
foreach ($session['lookup_prod_cats'] as $key => $val) {
	array_shift($val); // Remove --SELECT--
	asort($val); // Sort associative array by value
	array_unshift($val, '- - SELECT - -');
	$tmp[$key] = $val;
}
$session['lookup_prod_cats'] = $tmp;

// Default to Login view
// $view = isset($_POST['view']) ? $_POST['view'] : 'Login';

// Get required data for current view
if( 'Login' == $view ){
	$sql = "SELECT name,id FROM `user`";
	$results = $db_listings->query($sql);
	$users = $results->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Listings</title>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

<?php
$files_used[] = 'incs/style.css'; //DEBUG
?>
<link rel="stylesheet" href="incs/style.css">

<?= "<style>$header_colours</style>"; ?>

</head>
<body>
	
<?php
if( 'Login' == $view ){ require_once 'views/login.php';}

elseif( 'Dashboard' == $view ){ require_once 'views/dashboard.php'; }

elseif( 'Listings' == $view || 'Edit' == $view || 'Add' == $view || 'Add Prime' == $view ){
	
	require_once 'views/header.php';
	
	if( 'Listings' == $view || 'Edit' == $view || 'Add Prime' == $view ){
		$session['listings'] = [];
		if( isset($_POST['cat_id']) ){
			require_once 'views/retrieve_listings.php';
		}
		
		if( $session['listings'] && $prime_records ){
			require_once 'views/table_header.php';
			
			if( 'Listings' == $view ){
				require_once 'views/listings.php';
				
				foreach( ['form_edit_','form_add_','form_add_prime_'] as $form_type ){
					if( 'form_edit_' == $form_type ){
						$name = 'group_edit';
						$view = 'Edit';
					}
					elseif( 'form_add_' == $form_type ){
						// $name = 'add_to_group';
						$name = 'group_edit';
						$view = 'Add';
					}
					elseif( 'form_add_prime_' == $form_type ){
						// $name = 'add_prime_to_group';
						$name = 'group_edit';
						$view = 'Add Prime';
					}
					
					// Create hidden form fields linked to 'Edit, Add, Add Prime' buttons
					foreach( $forms_data as $i => $vals ){
						echo '<form id="'.$form_type.$i.'" method="post">';
						echo '<input type="hidden" name="posY" value="0">';
						// Used to select the required group: a, b, c etc.
						echo '<input type="hidden" name="'.$name.'" value="'.$vals['group_edit'].'">';
						// Used to display the required view: Edit, Add, Add Prime etc.
						echo '<input type="hidden" name="view" value="'.$view.'">';
						// Used to create the secondary dropdown - Eg.
						// [agg] => [
						//     [] => - - SELECT - -
						//     [a8] => Aggregates
						//     [a244] => Rock Salt
						// ]
						echo '<input type="hidden" name="cat" value="'.$vals['cat'].'">';
						// Used to retrieve all the secondary dropdown groups - Eg.
						// a16 => thermoguard analogue items, thermoguard digital items, Humidity Controller items.
						echo '<input type="hidden" name="cat_id" value="'.$vals['cat_id'].'">';
						// Used to 
						echo '<input type="hidden" name="key" value="'.$vals['key'].'">';
						echo '<input type="hidden" name="platform" value="'.$vals['platform'].'">';
						echo '<input type="hidden" name="user" value="'.$_POST['user'].'">';
						if( 'form_edit_' == $form_type || 'form_add_' == $form_type ){
							// Cost Per Unit
							echo '<input type="hidden" name="cpu" value="'.$vals['cpu'].'">';
							// Lowest Variation Weight
							echo '<input type="hidden" name="lvw" value="'.$vals['lvw'].'">';
						}
						if( 'form_add_' == $form_type ){
							// Used to check if variation already exists when a new listing gets added
							echo '<input type="hidden" name="vars" value="'.implode('|', $vals['vars']).'">';
						}
						echo '</form>';
					}
				}
			}
			elseif( 'Edit' == $view ){
				require_once 'views/listings_edit.php';
			}
			elseif( 'Add Prime' == $view ){
				require_once 'views/listings_add_prime.php';
			}
		}
		else{
			echo '<div style="font-size: 120px; text-align: center;">No records!</div>';
		}
	}
	elseif( 'Add' == $view ){
		require_once 'views/table_header.php';
		require_once 'views/listings_add.php';
	}
}

elseif( 'Errors' == $view ){ require_once 'views/errors.php'; }

elseif( 'Changes' == $view ){ require_once 'views/changes.php'; }

elseif( 'Add New Product' == $view ){ require_once 'views/add_new_product.php'; }

elseif( 'Add New Listings' == $view ){ require_once 'views/add_new_listings.php'; }

elseif( 'Config' == $view ){ require_once 'views/config.php'; }

elseif( 'Import' == $view ){ require_once 'views/import.php'; }
?>

<?php
require('js/js_multiselect_chkbxs.php');
require('js/js_connect_btns_to_forms.php');
if ('Edit' != $view) { require('js/js_retain_scroll_pos.php'); }
?>

<?php
// DEBUG - Display files used
if ($DEBUG = false) {
	$tmp = [];
	$tmp['incs'] = [];
	$tmp['views'] = [];
	$tmp['js'] = [];
	$tmp['root'] = [];
	// Order files
	foreach( $files_used as $file ){
		if( 'incs/' == substr($file, 0,5) ){
			$tmp['incs'][] = $file;
		}
		elseif( 'views/' == substr($file, 0,6) ){
			$tmp['views'][] = $file;
		}
		elseif( 'js/' == substr($file, 0,3) ){
			$tmp['js'][] = $file;
		}
		else{
			$tmp['root'][] = $file;
		}
	}
	$files = array_merge($tmp['root'], $tmp['incs'], $tmp['views'], $tmp['js']);
	$files = implode('<br>', $files);
	echo '<pre style="
		background:#002;
		color:#fff;
		font-size:12px;
		position: fixed;
		left:1650px;
		top:300px;
	">'; print_r( '<div style="font-size:16px;"><span style="font-size:inherit; text-decoration: underline;">Used Files</span>:</div>'. $files ); echo '</pre>';
}
?>

</body>
</html>