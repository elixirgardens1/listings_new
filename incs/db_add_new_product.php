<?php
if( isset($_POST['add_new_product_to_db']) ){
	$files_used[] = 'incs/db_add_new_product.php'; //DEBUG
	
	$db_modify = TRUE; // Comment out to stop database being updated
	
	$timestamp = time();
	
	$_POST = sanitize_post_data_fnc($_POST, [
		'my_trim',
		'remove_multiple_whitespace',
		'my_htmlspecialchars',
		'ascii_translit'
	]);
	
	$product       = str_replace('&quot;', '"', $_POST['product']);
	// $product       = $_POST['product'];
	$cat           = $_POST['cat'];
	$pri_sup       = $_POST['pri_sup'];
	$units         = $_POST['units'];
	$room          = $_POST['room'];
	$pkg_qty       = $_POST['pkg_qty'];
	$pkg_multiples = $_POST['pkg_multiples'];
	
	$cost = '' == $_POST['cost'] ? '0' : $_POST['cost'];
	
	$sql = "SELECT `cat`,`product` FROM `products`";
	$results = $db_stock->query($sql);
	$products = $results->fetchAll(PDO::FETCH_ASSOC);
	
	$cats = [];
	$product_names = [];
	foreach ($products as $vals) {
		$cats[] = $vals['cat'];
		$product_names[$vals['product']] = 1;
	}
	$cats = array_count_values($cats);
	$key = $cat . $cats[$cat];
	
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($cats); echo '</pre>'; die();
	
	
	// $db_stock->query("DELETE FROM `product_rooms` WHERE `rowid` = 777");
	// $db_stock->query("DELETE FROM `products` WHERE `rowid` = 792");
	// $db_stock->query("DELETE FROM `stock` WHERE `rowid` = 793");
	// $db_stock->query("DELETE FROM `stock_qty` WHERE `rowid` = 793");
	// die();
	
	//=========================================================================
	// Insert records to following stock control tables:
	// 1. products
	// 2. product_rooms
	// 3. stock_qty
	// 4. stock
	//=========================================================================
	$change = [];
	foreach( ['products','product_rooms','stock_qty','stock'] as $tbl ){
		//===========================================================================================================================
		// cat |sub_cat |key   |unit |product               |product_cost |primary_supplier |sec.. |con.. |to_.. |out.. |yel.. |red..
		// ele |  NULL  |ele49 |q    |Soil Warming Cable 3" |        3.96 |China            | NULL | NULL | NULL | NULL |   28 |   14
		//===========================================================================================================================
		if( 'products' == $tbl ){
			$insert_vals = [$cat,NULL,$key,$units,$product,$cost,$pri_sup,NULL,NULL,NULL,NULL,28,14,NULL,NULL,NULL,NULL];
			// {p}ele49{u}q{pn}Soil Warming Cable 3"{pc}3.96{ps}China
			$change[] = "{p}$key{u}$units{pn}$product{pc}$cost{ps}$pri_sup";
		}
		//============================
		// key   |room |shelf_location
		// ele49 |m    |
		//============================
		elseif( 'product_rooms' == $tbl ){
			$insert_vals = [$key,$room,NULL];
			// {pr}ele49{r}m
			$change[] = "{pr}$key{r}$room";
		}
		//=====================
		// key   |qty |date
		// ele49 |  0 |20220128
		//=====================
		elseif( 'stock_qty' == $tbl ){
			$insert_vals = [$key,0,date("Ymd")];
			// {sq}ele49
			$change[] = "{sq}$key";
		}
		//============================================
		// key   |pkg_qty |pkg_multiples |min_qty |qty
		// ele49 |      1 |            1 |      0 |  0
		//============================================
		elseif( 'stock' == $tbl ){
			$insert_vals = [$key,$pkg_qty,$pkg_multiples,0,0,'',''];
			// {s}ele49{pq}1{pm}1
			$change[] = "{s}$key{pq}$pkg_qty{pm}$pkg_multiples";
		}
		
		$qms = '?' . str_repeat(',?', count($insert_vals) - 1);
		$sql = "INSERT INTO `$tbl` VALUES ($qms)";
		
		if( isset($db_modify) ){
			$stmt = $db_stock->prepare($sql);
			$db_stock->beginTransaction();
			$stmt->execute($insert_vals);
			$db_stock->commit();
		}
		//DEBUG
		else{
			$insert_vals_str = implode(',',$insert_vals);
			echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql .' | '. $insert_vals_str); echo '</pre>';
		}
	}

	$changes = implode('',$change);
	
	if( !isset($db_modify) ){
		echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r('Changes to Stock Control: '. $changes); echo '</pre>';
		die();
	}

	//=========================================================================
	// Record changes
	//=========================================================================
	/*
	record_changes_to_stock_fnc([
		'changes'   => $changes,
		'user'      => $_POST['user'],
		'timestamp' => $timestamp,
		'db'        => $db_listings,
	]);
	*/
	
	// $_POST = [];
	
	unset(
		$_POST['product'],
		$_POST['cat'],
		$_POST['cost'],
		$_POST['units'],
		$_POST['room'],
		$_POST['pkg_qty'],
		$_POST['pkg_multiples'],
		$_POST['pri_sup'],
		$_POST['supplier'],
		$_POST['add_new_product'],
		$_POST['add_new_product_to_db']
	);
	
	// $_POST['view_'] = 'Add New Product';
}
