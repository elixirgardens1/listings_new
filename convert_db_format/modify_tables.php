<?php
/*
http://192.168.0.24/listings_new/convert_db_format/modify_tables.php

DESCRIPTION:
Creates the new database (listings_NEW.db3) and tables ($sql_create_tbls), then copies
the data from the live listings database.

The new database has several new tables:
  - comps_ids
  - config_fees
  - deletes
  - listings_couriers
  - prime_couriers
  - rooms_lookup [*** This will only exist in the StockControl database, when Ryan has added 'id' field ]
  - skus

The comps, IDs & skus originally existed in the listings_{PLATFORM} tables.

NOTE: New listings_{PLATFORM} tables (eg. listings_floorworld, listings_onbuy etc) need to added dynamically
      when new plaform listings are imported.
*/

//=========================================================================
// New database format loses the sku, comp1, comp2, comp3 & IDs columns
// from the platform tables.
// The skus have their own table (skus):
//    +---+------+-------+----------+
//    |id |sku   |source |timestamp |
//    +---+------+-------+----------+
//    |1  |02057 |a      |          |
//    |1  |02057 |a      |          |
//    etc.
// 
// comp1, comp2, comp3 & IDs now use the 'comps_ids':
//    +----+-------+-------+-------+------------+------------+------------+-------+-------+-------+--------+-----------+
//    | id | comp1 | comp2 | comp3 | id1        | id2        | id3        | type1 | type2 | type3 | source | timestamp |
//    +----+-------+-------+-------+------------+------------+------------+-------+-------+-------+--------+-----------+
//    | 1  | 8.75  | 6.59  | 9.49  | B009W3E0IY | B082H2JM8Q | B07JJF8Q7B | 1     | 1     | 1     | a      |           |
//    | 2  | 7.99  | 8.69  | 10.2  | B07N92F44V | B08X25WK1F | B0071B1BUE | 1     | 1     | 1     | a      |           |
//    | 3  | 10.75 |       |       | B0071B1C2Q |            |            | 1     |       |       | a      |           |
// 
//=========================================================================



// This creates database if not exist
// unlink('listings_NEW.db3');
// $db = new PDO('sqlite:../dbase/listings_NEW2.db3');
$db = new PDO('sqlite:../dbase/listings_NEW.db3');

require_once $_SERVER['DOCUMENT_ROOT'].'/database_paths.php';
$db_orig = new PDO('sqlite:'.$listings_db_path);


$sql_create_tbls = "
  CREATE TABLE `changes` (
    `id` INT,
    `changes` TEXT,
    `user` INT,
    `timestamp` INT
  );  
  CREATE TABLE `comps_ids`(
    `id` INT,
    `comp1` REAL,
    `comp2` REAL,
    `comp3` REAL,
    `id1` TEXT,
    `id2` TEXT,
    `id3` TEXT,
    `type1` INT,
    `type2` INT,
    `type3` INT,
    `source` TEXT,
    `timestamp` INT
  );
  CREATE TABLE `config_fees` (
    `type` TEXT,
    `value` TEXT,
    `id` INT,
    `timestamp` INT
  );
  INSERT INTO `config_fees` VALUES ('projection_20perc','2.02715','1','');
  INSERT INTO `config_fees` VALUES ('projection_20perc','2.15828','2','');
  INSERT INTO `config_fees` VALUES ('platform_fees','0.14','1','');
  INSERT INTO `config_fees` VALUES ('platform_fees','0.17','2','');
  
  CREATE TABLE `deletes` (
    `id` INT,
    `deletes` TEXT,
    `user` INT,
    `timestamp` INT
  );
  
  CREATE TABLE `header_colours` (
    `id` INT PRIMARY KEY,
    `bg-color` TEXT,
    `fg-color` TEXT,
    `timestamp` INT
  );
  INSERT INTO `header_colours` VALUES (1,'#ff8000','','');
  INSERT INTO `header_colours` VALUES (2,'#5b9bd5','','');
  INSERT INTO `header_colours` VALUES (3,'#ff0','#000','');
  INSERT INTO `header_colours` VALUES (4,'#090','','');
  INSERT INTO `header_colours` VALUES (5,'deeppink','','');
  INSERT INTO `header_colours` VALUES (6,'yellowgreen','','');
  INSERT INTO `header_colours` VALUES (7,'green','','');
  
  CREATE TABLE `header_colour_selection` (
    `platform` TEXT,
    `id` INT,
    `timestamp` INT
  );
  INSERT INTO `header_colour_selection` VALUES ('amazon',1,'');
  INSERT INTO `header_colour_selection` VALUES ('ebay',2,'');
  INSERT INTO `header_colour_selection` VALUES ('prime',3,'');
  INSERT INTO `header_colour_selection` VALUES ('web',4,'');
  
  CREATE TABLE `listings`(
    `id_lkup` INT PRIMARY KEY,
    `key` TEXT,
    `cat_id` TEXT,
    `group_` TEXT,
    `ignore_zero_vat` INT,
    -- `product_name` TEXT,
    `product_name` TEXT,
    -- `product_name` TEXT UNIQUE,
    `packing` TEXT,
    `packaging_band` INT,
    `lowest_variation_weight` INT,
    `variation` INT,
    `remove` INT,
    `timestamp` INT
  );
  CREATE TABLE `listings_amazon`(
    `id` INT PRIMARY KEY,
    `prev_price` REAL,
    `new_price` REAL,
    `perc_advertising` INT,
    `notes` TEXT,
    `timestamp` INT
  );
  CREATE TABLE `listings_couriers`(
    `id` INT PRIMARY KEY,
    `courier` INT,
    `timestamp` INT
  );
  CREATE TABLE `listings_ebay`(
    `id` INT PRIMARY KEY,
    `prev_price` REAL,
    `new_price` REAL,
    `perc_advertising` INT,
    `notes` TEXT,
    `timestamp` INT
  );
  CREATE TABLE `listings_prime`(
    `id` INT PRIMARY KEY,
    `prev_price` REAL,
    `new_price` REAL,
    `perc_advertising` INT,
    `notes` TEXT,
    `timestamp` INT
  );
  -- CREATE TABLE `listings_prosalt`(
  --  `id` INT PRIMARY KEY,
  --  `prev_price` REAL,
  --  `new_price` REAL,
  --  `perc_advertising` INT,
  --  `notes` TEXT,
  --  `timestamp` INT
  -- );
  CREATE TABLE `listings_web` (
    `id` INT PRIMARY KEY,
    `prev_price` REAL,
    `new_price` REAL,
    `perc_advertising` INT,
    `notes` TEXT,
    `timestamp` INT
  );
  -- CREATE TABLE `lookup_couriers` (
  --  `name` TEXT,
  --  `courier` TEXT,
  --  `cost` TEXT,
  --  `weight` TEXT,
  --  `timestamp` INT
  -- );
  
  CREATE TABLE `lookup_couriers_plus_fuel` (
    `name` TEXT,
    `courier` TEXT,
    `cost` TEXT,
    `fuel` TEXT,
    `weight` TEXT,
    `timestamp` INT
  );
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Post 0-100g','Whistl','1','%0','0.1','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Post 100-250g','Whistl','1.31','%0','0.25','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Post 250-500g','Whistl','1.48','%0','0.5','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Post 500-750g','Whistl','1.59','%0','0.75','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('0-1kg Packet Under 10','Whistl','1.94','%0','1','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('0-1kg Packet Over 10','Hermes','2.09','%14.5','1','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('1-2kg Packet','Hermes','2.09','%14.5','2','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('2-3kg Packet','Whistl','2.55','%5','3','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('3-15kg Parcel','Hermes','2.73','%14.5','15','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('15-30kg Parcel','Whistl','3.81','%5','30','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('0-30kg Hermes L+L','Hermes','7.19','%14.5','30','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('0-20kg DX Over 8ft','DX','17.19','%0','20','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('20-30kg DX Over 8ft','DX','20.99','%0','30','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Quarter Pallet','SCS','49','%0','250','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Half Pallet','SCS','51','%0','500','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Full Pallet','SCS','54','%0','1200','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('PRIME Post','Prime','3.07','%0','0.75','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('PRIME Parcel 0-2kg','Prime','3.07','%0','2','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('PRIME Parcel 2-7kg','Prime','4.17','%0','7','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('PRIME Parcel 7-20kg','Prime','5.35','%0','20','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('PRIME Parcel 20-23kg','Prime','7.05','%0','23','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Dropship','DX','15','%0','1200','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Expedited Small Parcel 0-15kg','Whistl','3.21','%5','15','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Dropship + Cutting Fee','DX','30','%0','150','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('Expedited Parcel 0-30kg','Whistl','3.79','%0','30','');
  INSERT INTO `lookup_couriers_plus_fuel` VALUES ('- PLEASE SELECT -','Whistl','0.72','%0','0.1','');
  
  -- CREATE TABLE `lookup_packaging_names` (
  --  `packaging_name` TEXT,
  --  `weight` TEXT,
  --  `key` TEXT,
  --  `timestamp` INT
  -- );
  CREATE TABLE `lookup_postage_bands` (
    `band` TEXT,
    `cost` TEXT,
    `max_weight` TEXT,
    `example_packaging` TEXT,
    `timestamp` INT
  );
  CREATE TABLE `lookup_prod_cats` (
    `cat` TEXT,
    `cat_id` TEXT PRIMARY KEY,
    `product_cat` TEXT,
    `zero_vat` INT,
    `timestamp` INT
  );
  CREATE TABLE `multi_cpu` (
    `key` INT,
    `keys` TEXT,
    `percs` TEXT,
    `timestamp` INT
  );
  
  CREATE TABLE `platforms` (
    `id` TEXT PRIMARY KEY,
    `txt` TEXT,
    `platform_fees` int,
    `projection_20perc` INT,
    `no_price_matrix` INT,
    `timestamp` INT
  );
  INSERT INTO `platforms` VALUES ('e','eBay',1,1,NULL,'');
  INSERT INTO `platforms` VALUES ('a','Amazon',2,2,NULL,'');
  INSERT INTO `platforms` VALUES ('w','Web',2,1,1,'');
  INSERT INTO `platforms` VALUES ('p','Prime',2,2,NULL,'');
  -- INSERT INTO `platforms` VALUES ('f','Floorworld',1,1,'');
  -- INSERT INTO `platforms` VALUES ('s','Prosalt',1,1,'');
  -- INSERT INTO `platforms` VALUES ('o','Onbuy',2,1,'');
  
  CREATE TABLE `prime_couriers`(
    `id` INT PRIMARY KEY,
    `courier` INT,
    `timestamp` INT
  );
  CREATE TABLE `rooms_lookup` (
    `id` TEXT,
    `room` TEXT,
    PRIMARY KEY (`id`)
  );
  INSERT INTO `rooms_lookup` VALUES ('1','Middle');
  INSERT INTO `rooms_lookup` VALUES ('2','Salt');
  INSERT INTO `rooms_lookup` VALUES ('3','Fert');
  INSERT INTO `rooms_lookup` VALUES ('4','Poison');
  INSERT INTO `rooms_lookup` VALUES ('5','Bamboo');
  INSERT INTO `rooms_lookup` VALUES ('6','Gallup');
  INSERT INTO `rooms_lookup` VALUES ('7','Cutting');
  
  CREATE TABLE `skus`(
    `id` INT,
    `sku` TEXT,
    `source` TEXT,
    `timestamp` INT
  );
  
  CREATE TABLE `tub_costs` (
    `greater_than_15` text,
    `greater_than_10` text,
    `greater_than_5` text,
    `greater_than_1` text,
    `else` text,
    `timestamp` INT
  );
  INSERT INTO `tub_costs` VALUES ('4','2.5','2','1.5','1','');
  
  CREATE TABLE `user` (
    `id` INT PRIMARY KEY,
    `name` TEXT
  );
";
$db->exec($sql_create_tbls);


















$tbls = [
  'listings_amazon',
  'listings_ebay',
  // 'listings_floorworld',
  // 'listings_onbuy',
  'listings_prime',
  // 'listings_prosalt',
  'listings_web',
  
  'changes',
  'listings',
  // 'lookup_couriers',
  // 'lookup_packaging_names',
  'lookup_postage_bands',
  'lookup_prod_cats',
  'multi_cpu',
  'user',
];

$platforms = [
  'amazon'=>1,
  'ebay'=>1,
  // 'floorworld'=>1,
  // 'onbuy'=>1,
  'prime'=>1,
  // 'prosalt'=>1,
  'web'=>1,
];

$missing_ids = [];
foreach( $tbls as $tbl ){
  // Also retrieve rowid if 'listings' table
  $sql = 'listings' != $tbl ? "SELECT * FROM `$tbl`" : "SELECT rowid,* FROM `$tbl`";
  $results = $db_orig->query($sql);
  $insert_vals = $results->fetchAll(PDO::FETCH_ASSOC);

  // $platform = str_replace('listings_', '', $tbl);

  if( isset($platforms[ $platform = str_replace('listings_', '', $tbl) ]) ){
    $stmt_platforms = $db->prepare("INSERT INTO `$tbl` VALUES (?,?,?,?,?,?)");
    $stmt_skus      = $db->prepare("INSERT INTO `skus` VALUES (?,?,?,?)");
    $stmt_comps_ids = $db->prepare("INSERT INTO `comps_ids` VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    
    // listings_couriers
    // prime_couriers
    
    $stmt_couriers    = $db->prepare("INSERT INTO `listings_couriers` VALUES (?,?,?)");
    $stmt_pr_couriers = $db->prepare("INSERT INTO `prime_couriers` VALUES (?,?,?)");

    // http://192.168.0.24/listings_new/convert_db_format/modify_tables2.php

    // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r("TABLE: $tbl\n"); print_r($insert_vals); echo '</pre>';

    $db->beginTransaction();
    foreach ($insert_vals as $rec) {
      // if ('13919' == $rec['id']) {continue;}
      
      $stmt_platforms->execute([
        $rec['id'],
        // $rec['courier'],
        $rec['prev_price'],
        'listings_web' != $tbl ? $rec['new_price'] : '',
        $rec['perc_advertising'],
        $rec['notes'],
        $rec['timestamp'],
      ]);
      
      if( 'ebay' == $platform ){
        $stmt_couriers->execute([
          $rec['id'],
          $rec['courier'],
          $rec['timestamp'],
        ]);
      }
      if( 'prime' == $platform ){
        $stmt_pr_couriers->execute([
          $rec['id'],
          $rec['courier'],
          $rec['timestamp'],
        ]);
      }
      
      
      $platform_first_letter = substr($platform, 0,1);

      // Split comma deliited skus into array
      foreach( explode(',', $rec['sku']) as $sku ){
        if( '' != trim($sku) ){
          $stmt_skus->execute([
            $rec['id'],
            trim($sku),
            $platform_first_letter,
            $rec['timestamp'],
          ]);
        }
      }
      
      if( 'web' != $platform ){
        // Extract comps and ids to new table
        $ids = explode(',', $rec['IDs']);
        $comp1 = $rec['comp1'];
        $comp2 = $rec['comp2'];
        $comp3 = $rec['comp3'];
        
        $id1 = '';
        $id2 = '';
        $id3 = '';
        
        if( isset($ids[2]) ){
          $id3 = $ids[2];
          $id2 = $ids[1];
          $id1 = $ids[0];
        }
        elseif( isset($ids[1]) ){
          $id2 = $ids[1];
          $id1 = $ids[0];
        }
        elseif( isset($ids[0]) ){
          $id1 = $ids[0];
        }
        
        // If 1 element of a comp/id pair don't exist, delete both
        if( '' == $comp1 || '' == $id1 ){ $comp1 = ''; $id1 = ''; }
        if( '' == $comp2 || '' == $id2 ){ $comp2 = ''; $id2 = ''; }
        if( '' == $comp3 || '' == $id3 ){ $comp3 = ''; $id3 = ''; }
        
        $type1 = '' != $comp1 ? 1 : '';
        $type2 = '' != $comp2 ? 1 : '';
        $type3 = '' != $comp3 ? 1 : '';
        
        // Add to table if 1 or more comp vals exist
        if( '' != $comp1 || '' != $comp2 || '' != $comp3 ){
          $ins_arr = [
            $rec['id'],
            $comp1,
            $comp2,
            $comp3,
            $id1,
            $id2,
            $id3,
            $type1,
            $type2,
            $type3,
            $platform_first_letter,
            '',
          ];
          $stmt_comps_ids->execute($ins_arr);
        }
        else{
          $missing_ids[] = 'Missing COMPS ('.$rec['id']. ' | ' . $platform . ')';
        }
      }
    }
    $db->commit();

    // echo '<pre style="background:#002; color:#fff;">'; print_r($missing_ids); echo '</pre>';
  }
  else{
    // Count total fields to create correct number of '$que_marks'
    $que_marks = str_repeat(',?',count($insert_vals[0]) -1);
    
    if (
      'lookup_prod_cats' == $tbl ||
      'listings' == $tbl
    ) {$que_marks = $que_marks.',?';}
    
    $sql = "INSERT INTO `$tbl` VALUES (?$que_marks)";
    
    $stmt = $db->prepare($sql);

    $duplicates = [];
    $db->beginTransaction();
    foreach ($insert_vals as $i => $rec) {
      if( 'listings' == $tbl ){
        if (1 == $rec['remove']) {continue;}
        
        // if (in_array($rec['product_name'], $duplicate_prods)) {
        //   echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($rec['product_name'] .' > '. $rec['remove']); echo '</pre>';
        // }
        
        // if( !isset($duplicates[ $rec['product_name'].$rec['variation'] ]) ){
        //   $duplicates[$rec['product_name'].$rec['variation']] = 1;
        // }
        // else{
        //   $duplicates[$rec['product_name'].$rec['variation']] = $duplicates[$rec['product_name'].$rec['variation']] +1;
        // }
        
        // if( $duplicates[$rec['product_name'].$rec['variation']] > 1 ){
        //   $rec['product_name'] = $rec['product_name'] . ' *** '.$duplicates[$rec['product_name'].$rec['variation']];
        // }
        
        $rec = [
          'rowid'                   => $rec['rowid'],
          'key'                     => $rec['key'],
          'cat_id'                  => $rec['cat_id'],
          'group_'                  => $rec['group_'],
          'ignore_zero_vat'         => NULL,
          'product_name'            => $rec['product_name'],
          'packing'                 => $rec['packing'],
          'packaging_band'          => $rec['packaging_band'],
          'lowest_variation_weight' => $rec['lowest_variation_weight'],
          'variation'               => $rec['variation'],
          'remove'                  => $rec['remove'],
          'timestamp'               => $rec['timestamp']
        ];
        
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql); echo '</pre>';
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($rec); echo '</pre>';
      }
      
      if ('lookup_prod_cats' == $tbl) {
        $zero_vat = NULL;
        
        if (
          'a11'  == $rec['cat_id'] ||
          'a160' == $rec['cat_id'] ||
          'a164' == $rec['cat_id'] ||
          'a224' == $rec['cat_id'] ||
          'a282' == $rec['cat_id']
        ) {$zero_vat = 1;}
        
        $rec = [
          'cat'         => $rec['cat'],
          'cat_id'      => $rec['cat_id'],
          'product_cat' => $rec['product_cat'],
          'zero_vat'    => $zero_vat,
          'timestamp'   => $rec['timestamp']
        ];
      }
      
      $stmt->execute( array_values($rec) );
    }
    $db->commit();
  }
}


$ids_in = "'14288','14289','14290','14291','14292','14293','14294'";

foreach ([
    'listings_amazon',
    'listings_ebay',
    'listings_web',
] as $tbl) {
    $sql = "DELETE FROM $tbl WHERE `id` IN ($ids_in)";
    $db->query($sql);
}

//=========================================================================
// Update courier lokkup values
//=========================================================================
$sql = "DELETE FROM `listings_couriers` WHERE `courier` = '28'";
$db->query($sql);

$stmt = $db->prepare("UPDATE `listings_couriers` SET `courier` = ? WHERE `courier` = ?");

$db->beginTransaction();
$stmt->execute(['28', '29']);
$db->commit();


$sql = "DELETE FROM `lookup_couriers_plus_fuel` WHERE `name` = '- PLEASE SELECT -'";
$db->query($sql);

$stmt = $db->prepare("INSERT INTO `lookup_couriers_plus_fuel` VALUES (?,?,?,?,?,?)");
$db->beginTransaction();
foreach ([
  ['Dropship + Cutting Fee','DX','30','%0','150',''],
  ['No Shipping','Elixir','0','%0','1200',''],
  ['Full Pallet x 2','SCS','108','%0','1200',''],
  ['- PLEASE SELECT -','Whistl','0.72','%0','0.1','']
] as $rec) {
  $stmt->execute($rec);
}
$db->commit();







// 2ltr Gallup + Cup &amp; Gloves


//DEBUG
// echo '<pre style="background:#002; color:#fff;">'; print_r($results); echo '</pre>'; die();

/*
26 Dropship + Cutting Fee
27 No Shipping
28 - PLEASE SELECT -
29 Full Pallet x 2
 */
