<?php
$files_used[] = 'views/dashboard.php'; //DEBUG

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($lookup_dash_view_profit); echo '</pre>'; die();
?>
<h1>Dashboard View</h1>

<div style="position: absolute; left: 200px; top: 4px;">
	<a href="http://192.168.0.24/kevInterface.php" target="_blank" class="btn w200 h20">HOME</a>
	<a href="http://192.168.0.24/listings_new/views/platform.php" target="_blank" class="btn w200 h20">Platform Skus</a>
</div>

<div style="width: 1000px; margin-top: 30px;">
	<form method="post">
		<input type="hidden" name="user" value="<?= $_POST['user'] ?>">

		<input type="submit" name="view" value="Listings" class="btn w200">

		<input type="submit" name="view" value="Errors" class="btn w200">

		<input type="submit" name="view" value="Changes" class="btn w200">

		<div class="dropdown" style="float:right; width:120px;">
			<button onclick="myFunction()" class="dropbtn w120 h30">View Profit</button>
			<div id="myDropdown" class="dropdown-content w120">
				<!-- <a href="sort_by_profit.php?csv" target="_blank">eBay</a> -->
			<?php foreach( $lookup_dash_view_profit as $rec ){
					if( isset($rec['source']) ){ ?>
				<a href="sort_by_profit.php?source=<?= $rec['source'] ?>&csv" target="_blank"><?= $rec['txt'] ?></a>
			<?php } } ?>
				<!-- <a href="sort_by_profit.php?source=amazon&csv" target="_blank">Amazon</a> -->
				<!-- <a href="sort_by_profit.php?source=web&csv" target="_blank">Website</a> -->
				<!-- <a href="sort_by_profit.php?source=prime&csv" target="_blank">Prime</a> -->
			</div>
		</div>

		<br><br>

		<input type="submit" name="view" value="Add New Product" class="btn w200">
		
		<input type="submit" name="view" value="Add New Listings" class="btn w200">
		
		<!-- <input type="submit" name="view" value="Config" class="btn w200"> -->
		
		<input type="submit" name="view" value="Import" class="btn w200">

		<br><br>

		<input type="submit" name="copy_couriers" value="Copy eBay couriers to Amazon &amp; eBay listings" class="btn w400">
	</form>
	
	<p><a href="tools/update_listings_new_price.php" class="btn w200" style="line-height: 34px;" target="_blank">Bulk price Update</a></p>
</div>

<!-- DEBUG 
<div style="margin-top:100px">
	<p style="color: red; font-size:18px;"><i>DEBUG</i></p>
	
	<p><a href="dbase/copy_listings_copy_to_listings.php" target="_blank">Monitor changes to listings database / reset listings database</a></p>
	
	<p><a href="dbase/sqlite_operations.php" target="_blank">View all occurences of a listings ID in the listings database</a></p>
</div>
-->