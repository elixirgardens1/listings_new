<?php
$files_used[] = 'views/table_header.php'; //DEBUG
?>
<!--
Column header name present in each view:

Table Columns:
	Product Name
	Packaging Band
	Courier
	Cost Per Unit
	Lowest variation Weight
	Variation                    ... ONLY THE FIRST 6 USED ON 'Add'
	
	Total Weight (KG)            ... ONLY THE FIRST 7 USED ON 'Add Prime'
	
	Skus                         ... NOT 'Edit Listings' VIEW
	COMP1 (£)                    ... NOT 'Web' OR 'Edit Listings' VIEWS
	COMP2 (£)                    ... NOT 'Web' OR 'Edit Listings' VIEWS
	SPON COMP (£)                ... NOT 'Web' OR 'Edit Listings' VIEWS
	OURS
	Previous Price (£)
	Pricing Suggestion 20% (£)
	
	Price Suggest (£)            ... ONLY 'Edit Listings' VIEW
	
	New Price (£)
	CPU to Cust. (£)
	Advertising %
	Profit (£)
	Profit %
	
	10% Off Profit (£)           ... ONLY 'Web' VIEW
	10% Off Profit Perc          ... ONLY 'Web' VIEW
	
	Total Product Cost (£)
	Postage (£)
	VAT (£)
	Fees (£)                     ... NOT 'Web' VIEW
	[CHECKBOX SELECT]            ... ONLY 'Listings' VIEW
-->
<?php $bg_colour = 'bg-'.$lookup_platform[$platform_post]; ?>

<form name="main_form" method="post" id="main_form">
<table class="style-tbl">
	<tr>
		<th class="sticky product_name <?= $bg_colour ?>">Product Name</th>
		<th class="sticky packaging_band <?= $bg_colour ?>">Packaging Band</th>
		<th class="sticky courier <?= $bg_colour ?>">Courier</th>
		<th class="sticky cost_per_unit <?= $bg_colour ?>">Cost Per Unit</th>
		<th class="sticky lowest_variation_weight <?= $bg_colour ?>">Lowest variation Weight</th>
		<th class="sticky variation <?= $bg_colour ?>">Variation</th>
		
		<?php if( 'Add' != $view ){ ?><th class="sticky calc total_weight">Total Weight (KG)</th><?php } ?>
		
		<?php if( 'Add' != $view && 'Add Prime' != $view ){ ?>
		
			<?php if( 'Edit' != $view ){ ?><th class="sticky packaging_band <?= $bg_colour ?>">Skus</th><?php } ?>
			
			<?php if( 'Edit' == $view ){ ?><th class="sticky comp1 <?= $bg_colour ?>">Notes</th><?php } ?>
			
			<?php if( 'Edit' == $view && 'w' != $platform_post ){ ?><th class="sticky comp1 <?= $bg_colour ?>">COMPS & IDs</th><?php } ?>
			
			<?php if( 'Edit' != $view && 'w' != $platform_post ){ ?>
			<th class="sticky comp1 <?= $bg_colour ?>">COMP1 (&pound;)</th>
			<th class="sticky comp2 <?= $bg_colour ?>">COMP2 (&pound;)</th>
			<th class="sticky comp3 <?= $bg_colour ?>">SPON<br>COMP (&pound;)</th>
			<?php } ?>
			
			<th class="sticky comp3 <?= $bg_colour ?>">OURS</th>
			<th class="sticky prev_price <?= $bg_colour ?>">Previous Price (&pound;)</th>
			<th class="sticky calc pricing_suggestion_20perc">Pricing Suggestion 10% (&pound;)</th>
			
			<?php if( 'a' == $platform_post ){ ?><th class="sticky calc new_price">Ebay Price (&pound;)</th><?php } ?>
			
			<?php if( 'p' == $platform_post ){ ?><th class="sticky calc new_price">Amazon Price (&pound;)</th><?php } ?>
			
			<?php if( ( isset($_POST['group_edit']) || isset($_POST['add_to_group']) ) && 'w' != $platform_post ){ ?>
			<th class="sticky calc price_suggestion">Price Suggest (&pound;)</th>
			<?php } ?>
			
			<th class="sticky calc new_price">New Price (&pound;)</th>
			<th class="sticky calc cpu_to_cust">CPU to Cust. (&pound;)</th>
			<th class="sticky perc_advertising <?= $bg_colour ?>">Advertising %</th>
			<th class="sticky calc profit">Profit (&pound;)</th>
			<th class="sticky calc profit_perc">Profit %</th>
			
			<th class="sticky calc fees">PP1+<br>PP2 (%)</th>
			<th class="sticky calc fees">PP1 (&pound;)</th>
			<th class="sticky calc fees">PP2 (&pound;)</th>
			
			<?php if( 'w' == $platform_post ){ ?>
			<th class="sticky calc profit_10off">10% Off Profit (&pound;)</th>
			<th class="sticky calc profit_10off_perc">10% Off Profit Perc</th>
			<?php } ?>
			
			<th class="sticky calc total_product_cost">Total Product Cost (&pound;)</th>
			<th class="sticky calc postage">Postage (&pound;)</th>
			<th class="sticky calc vat">VAT (&pound;)</th>
			
			<?php if( 'w' != $platform_post ){ ?>
			<th class="sticky calc fees">Fees (&pound;)</th>
			<?php } ?>
			
			<?php if( !isset($_POST['group_edit']) && !isset($_POST['add_to_group']) ){ ?>
			<th class="sticky export_remove <?= $bg_colour ?>">
				<input id="allCheck" class="sudo_cbx" type="checkbox" data-modal-tickers-master="true">
				<label class="cbx" for="allCheck"></label>
			</th>
			<?php } ?>
		<?php } ?>
	</tr>