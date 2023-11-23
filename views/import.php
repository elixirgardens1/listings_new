<?php
$errors = [];

// http://192.168.0.24/listings_new/

/*
Amazon,Prime: B009W3E0IY, B07N92F44V, B00FORS6S2 etc.
eBay, Prosalt, Floorworld: 174089704973, 402577651711, 333483712139 etc.
onBuy: c1234~p12345678, c12345~p12345678 etc

6.5-c5628~p59335049-2   7.9-c5628~p59335050-1   7.02-c5628~p59335064-3
9.49-c56289~p59335011-1 9.59-c56281~p59335044-2 9.69-c56248~p59335021-3

6.5-B143705246-2    7.9-B342748432-1    7.02-B342748431-3
9.49-B111111111-1   9.59-B222222222-2   9.69-B333333333-3

6.5-114370524618-2  7.9-234274843238-1  7.02-234274843111-3
9.49-111111111111-1 9.59-222222222222-2 9.69-333333333333-3
*/
$regex = '/^B[0-9A-Z]{9}(a|p)|[0-9]{12}(e|s|f)|c[0-9]{4,5}~p[0-9]{8}o$/';
$id_errs = [
    ['sources' => ['a','p'],
     'error' => 'Amazon/Prime ids must be 10 letters/digits, starting with B.'],
    ['sources' => ['e','s','f'],
     'error' => 'eBay ids must be 12 digits.'],
    ['sources' => ['o'],
     'error' => 'onBuy ids must be ???.'],
];

$regex_id_errs = [
    'regex' => $regex,
    'id_errs' => $id_errs,
];

$db_write = true;

if( isset($_POST['upload_file']) ){
    $timestamp = time();

    if( isset($_POST['create_new_platform']) ){
        $source = strtolower( trim($_POST['platform_id']) );
        $txt = trim($_POST['platform_name']);
        $platform_lc = strtolower($txt);
        
        $platform_fees = $_POST['platform_fees'];
        $platform_20perc = $_POST['platform_20perc'];
        $no_price_matrix = isset($_POST['no_price_matrix']) ? '1' : NULL;
    }
    else{
        $source = $_POST['select_platform'];
    }
    
    //=========================================================================
    // ALLOWABLE CSV FIELDS
    //=========================================================================
    $legal_csv_flds_assoc = [
        // listings
        'product_name'            => TRUE,
        // 'product_name'            => 'listings',
        'packing'                 => 'listings',
        'packaging_band'          => 'listings',
        'lowest_variation_weight' => 'listings',
        'variation'               => 'listings',
        'remove'                  => 'listings',
        // listings_couriers/prime_couriers
        'courier' => 'couriers',
        // listings_{PLATFORMS}
        'prev_price'       => 'listings_pf',
        'new_price'        => 'listings_pf',
        'perc_advertising' => 'listings_pf',
        'notes'            => 'listings_pf',
        // comps_ids
        'comp1' => 'comps_ids',
        'comp2' => 'comps_ids',
        'comp3' => 'comps_ids',
        
        // skus
        'sku'         => 'skus',
        'sku_add'     => 'skus', // Helps to minimise mistakes. if sku_total = 6, the number of '<$>' (separators) in the 'sku' field should equal 5.
        'sku_replace' => 'skus',
        // 'sku_delete'  => 'skus',
    ];
    // NOTE: If 'sku' field exists, 'sku_add' or 'sku_replace' must also exist.
    //       Need to add code to check for this.
    
    if( isset($_POST['create_new_platform']) ){
        unset($legal_csv_flds_assoc['courier']);
    }
    
    $legal_csv_flds = array_keys($legal_csv_flds_assoc);
    
    //=========================================================================
    // IMPORT CSV FILE
    //=========================================================================
    $target_file = 'import_listings.csv';
    move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file);
    $csv = file('import_listings.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    //=========================================================================
    // CHECK FOR CSV IMPORT ERRORS
    //=========================================================================
    $csv_vals = [];
    $product_names = [];
    foreach ($csv as $i => $row) {
        // Get field names and their positions (1st row)
        if( 0 == $i ){
            $flds = explode(",", $row);
            
            // $flds array must have at least 2 items
            $not2flds = count($flds) > 1 ? FALSE : TRUE;
            
            // only 1 fld can be 'product_name'
            $required_flds = FALSE;
            foreach( $legal_csv_flds as $i => $fld ){
                if( in_array($fld, $flds) ){
                    $required_flds = TRUE;
                    break;
                }
            }
            
            // Check that if 'sku' field exists in the imported CSV, the 'sku_add' field or 'sku_replace' field also exists
            $flds_as_keys = array_flip($flds);
            if( isset($flds_as_keys['sku']) && !isset($flds_as_keys['sku_add']) && !isset($flds_as_keys['sku_replace']) ){
                $errors['csv'][] = "The imported CSV has a 'sku' field but no 'sku_add' or 'sku_replace' field.";
            }
            
            
            $no_product_name = FALSE;
            if( !in_array('product_name', $flds) ){
                $no_product_name = TRUE;
            }
            $illegal_csv_flds = [];
            foreach( $flds as $fld ){
                if( !in_array($fld, $legal_csv_flds) ){
                    $illegal_csv_flds[] = $fld;
                }
            }

            if( $no_product_name || count($illegal_csv_flds) || $not2flds || !$required_flds ){
                if( !$required_flds ){
                    $errors['csv'][] = 'Not the required fields.';
                }
                if( $not2flds ){
                    $errors['csv'][] = '2 or more fields are required.';
                }
                if( $no_product_name ){
                    $errors['csv'][] = "'product_name' is required.";
                }
                foreach( $illegal_csv_flds as $fld ){
                    $errors['csv'][] = "The following field is not allowed: $fld";
                }
            }

            $fld_names = [];
            foreach ($flds as $index => $fld) {
                $fld_names[$fld] = $index;
            }
            // $fld_names:
            // [product_name] => 0
            // [new_price]    => 1
            // [notes]        => 2
            // [comp1]        => 3
            // [comp2]        => 4
            // [sku]          => 5
            // [sku_replace]  => 6
        }
        // Get CSV date (2nd row onwards)
        else{
            $rec = explode(",", $row);
            $csv_vals[] = $rec;
            $product_names[] = $rec[$fld_names['product_name'] ];
        }
    }
    
    //=======================================================================
    // Skus have 2 options: 'sku_add' & 'sku_replace'.
    // Both add new skus to the skus table, but replace deletes the existing
    // skus before doing so.
    //=======================================================================
    if( !isset($errors['csv']) ){
        $timestamp = time();
        
        $product_names_str = implode("','", $product_names);
        
        $where = " WHERE `product_name` IN ('$product_names_str')";
        $sql = "SELECT * FROM `listings`$where";
        $results = $db_listings->query($sql);
        $results_listings = $results->fetchAll(PDO::FETCH_ASSOC);
        
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results_listings); echo '</pre>'; die(); //DEBUG
        
        
        $id_lkup = [];
        foreach( $results_listings as $rec ){
            $id_lkup[$rec['product_name']] = $rec['id_lkup'];
        }
        
        $sql = "SELECT `id`,`txt` FROM `platforms`";
        $results = $db_listings->query($sql);
        $results_platforms = $results->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $platform_lc = isset($_POST['create_new_platform']) ? $platform_lc : strtolower($results_platforms[$source]);
        $fld_names_keys = array_keys($fld_names);
        
        $tbls2process = [];
        foreach( $fld_names_keys as $name ){
            $tbls2process[$legal_csv_flds_assoc[$name]] = 1;
        }
        // [listings] => 1
        // [listings_pf] => 1
        // [comps_ids] => 1
        // [skus] => 1
        
        $sql_arr = [];
        foreach( $csv_vals as $i => $rec ){
            if( !isset($id_lkup[$rec[$fld_names['product_name']]]) ){
                $errors['product_name'] = "The following product name does not exist: '{$rec[$fld_names['product_name']]}'";
                break;
            }
            
            $id = $id_lkup[$rec[$fld_names['product_name']]];
            
            // If any listings fields exist in the imported CSV
            if( isset($tbls2process['listings']) ){
                $args = [
                    'tbl' => 'listings',
                    'flds' => [
                        'packing',
                        'packaging_band',
                        'lowest_variation_weight',
                        'variation',
                        'remove',
                    ],
                    'fld_names_arr' => $fld_names,
                    'id' => $id,
                    'rec' => $rec,
                ];
                $tmp = sql_arr_fnc($args);
                $sql_arr = array_merge_recursive_distinct_fnc($sql_arr, $tmp);
            }
            
            // If a courier field exists in the imported CSV
            if( isset($tbls2process['couriers']) ){
                $args = [
                    'tbl' => 'couriers',
                    'flds' => [
                        'courier',
                    ],
                    'fld_names_arr' => $fld_names,
                    'id' => $id,
                    'rec' => $rec,
                    'couriers' => $session['lookup_couriers'],
                ];
                $return = sql_arr_fnc($args);
                
                if( count($return['sql_arr']) ){
                    $sql_arr['couriers'][] = $return['sql_arr'];
                }
                if( isset($return['errors']['comps_ids']) ){
                    $errors['couriers'][] = $return['errors'];
                }
            }
            
            // If any listings_pf fields exist in the imported CSV
            if( isset($tbls2process['listings_pf']) ){
                $args = [
                    'tbl' => 'listings_pf',
                    'flds' => [
                        'prev_price',
                        'new_price',
                        'perc_advertising',
                        'notes',
                    ],
                    'fld_names_arr' => $fld_names,
                    'id'            => $id,
                    'rec'           => $rec,
                ];
                $tmp = sql_arr_fnc($args);
                $sql_arr = array_merge_recursive_distinct_fnc($sql_arr, $tmp);
            }
            
            // If any comps_ids fields exist in the imported CSV
            if( isset($tbls2process['comps_ids']) ){
                $args = [
                    'tbl'           => 'comps_ids',
                    'fld_names_arr' => $fld_names,
                    'rec'           => $rec,
                    'link_type'   => $link_type,
                    'source'        => $source,
                    'id'            => $id,
                ];
                $args = array_merge_recursive_distinct_fnc($args, $regex_id_errs);
                $return = sql_arr_fnc($args);
                
                if( count($return['sql_arr']) ){
                    $sql_arr['comps_ids'][] = $return['sql_arr'];
                }
                if( isset($return['errors']) && '' != $return['errors'] ){
                    $errors['comps_ids'][] = $return['errors'];
                }
            }

            // If any sku fields exist in the imported CSV
            if( isset($tbls2process['skus']) ){
                $args = [
                    'tbl'           => 'skus',
                    'fld_names_arr' => $fld_names,
                    'rec'           => $rec,
                    'source'        => $source,
                    'id'            => $id,
                ];
                $return = sql_arr_fnc($args);
                
                $op_type = $return['op_type'];
                
                
                if( count($return['sql_arr']) ){
                    $sql_arr['skus'][] = $return['sql_arr'];
                }
            }
            // END: If any sku fields exist
        }
        // END LOOP
        
        if( isset($sql_arr['skus']) ){
            $tmp = $sql_arr['skus'][0];
            for ($i=0; $i < count($sql_arr['skus']); $i++) { 
                if( !isset($sql_arr['skus'][$i+1]) ){ break; }
                $tmp = array_merge($tmp, $sql_arr['skus'][$i+1]);
            }
            $sql_arr['sku'] = $tmp;
        }
        
        
        //=========================================================================
        // Update all tables here - assuming no errors
        // NOTE: The records to insert/update are built up separately in sql_arr_fnc(). 
        //       This is kept separate so that no database changes occur until we
        //       can be sure that all the CSV data is valid.
        //=========================================================================
        if( !isset($errors['csv']) && !isset($errors['product_name']) && !isset($errors['comps_ids']) && !isset($errors['sku']) ){
            // $db_write = false;
            
            // UPDATE 'listings' table
            if( isset($sql_arr['listings']) ){
                foreach( $sql_arr['listings'] as $id => $rec ){
                    $set = [];
                    $vals = [];
                    foreach( $rec as $fld_name => $val ){
                        $set[] = "`$fld_name` = ?";
                        $vals[] = $val;
                    }
                    $set[] = "`timestamp` = ?";
                    $set_str = implode(',', $set);
                    
                    $sql_update = "UPDATE `listings` SET $set_str WHERE `id_lkup` = ?";
                    $sql_vals = array_merge($vals, [$timestamp,$id]);
                    
                    if( $db_write ){
                        $stmt = $db_listings->prepare( $sql_update );
                        $db_listings->beginTransaction();
                        $stmt->execute( $sql_vals );
                        $db_listings->commit();
                    }
                    // Replace '?' placeholders with actual values for debuggung.
                    else{ display_sql_fnc($sql_vals, $sql_update); }
                }
            }
            
            // INSERT 'listings_couriers'/'prime_couriers' table (DELETE existing records beforehand)
            if( isset($sql_arr['couriers']) ){
                foreach( $sql_arr['couriers'] as $rec ){
                    $couriers_tbl = 'p' != $source ? 'listings_couriers' : 'prime_couriers';
                    
                    if( !isset($_POST['create_new_platform']) ){
                        $sql_delete = "DELETE FROM `$couriers_tbl` WHERE `id` = '{$rec[0]}'";
                        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px; margin-top:-4px; margin-bottom:6px;">'; print_r($sql_delete); echo '</pre><br>';
                    }
                    
                    $sql_insert = "INSERT INTO `$couriers_tbl` VALUES (?,?,?)";
                    $sql_vals = [$rec[0],$rec[1],$timestamp];
                    
                    if( $db_write ){
                        $stmt = $db_listings->prepare( $sql_insert );
                        $db_listings->beginTransaction();
                        
                        if( !isset($_POST['create_new_platform']) ){
                            $db_listings->query($sql_delete);
                            $stmt->execute( $sql_vals );
                        }
                        
                        $db_listings->commit();
                    }
                    else{ display_sql_fnc($sql_vals, $sql_insert); }
                }
            }
            
            
            
            //==================================================================================
            // A new listings_{PLATFORM} table must be created if 'create_new_platform'
            // Records also need adding to the `platforms` and `header_colour_selection` tables.
            //==================================================================================
            if( isset($_POST['create_new_platform']) ){
                $sql_vals_platform = [$source, $txt, $platform_fees, $platform_20perc, $no_price_matrix, $timestamp];
                $sql_vals_header_colour = [$platform_lc, $_POST['header_color'], $timestamp];
                
                $sql_platforms = "INSERT INTO `platforms` VALUES (?,?,?,?,?,?)";
                $sql_header_colour_selection = "INSERT INTO `header_colour_selection` VALUES (?,?,?)";
                
                $tbl = "listings_{$platform_lc}";
                $sql_create_tbls = "CREATE TABLE `$tbl`(
                    `id` INT PRIMARY KEY,
                    `prev_price` REAL,
                    `new_price` REAL,
                    `perc_advertising` INT,
                    `notes` TEXT,
                    `timestamp` INT
                );";
                
                if( $db_write ){
                    // Insert new platfrom data into 'platforms' table.
                    $stmt_insert_platform = $db_listings->prepare($sql_platforms);
                    // Set header colour for new platform
                    $stmt_insert_header_colour = $db_listings->prepare($sql_header_colour_selection);
                    
                    $db_listings->beginTransaction();
                    $stmt_insert_platform->execute($sql_vals_platform);
                    $stmt_insert_header_colour->execute($sql_vals_header_colour);
                    
                    $db_listings->exec($sql_create_tbls);
                    $db_listings->commit();
                }
                else{
                    display_sql_fnc($sql_vals_platform, $sql_platforms);
                    display_sql_fnc($sql_vals_header_colour, $sql_header_colour_selection);
                    
                    $view = str_replace("\t\t\t\t", '', $sql_create_tbls);
                    $view = str_replace("\t", '    ', $view);
                    
                    echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($view); echo '</pre><br>';
                }
            }
            
            // UPDATE/INSERT 'listings_{PLATFORM}' table
            if( isset($sql_arr['listings_pf']) ){
                foreach( $sql_arr['listings_pf'] as $id => $rec ){
                    // UPDATE 'listings_{PLATFORM}' table
                    if( !isset($_POST['create_new_platform']) ){
                        $set = [];
                        $vals = [];
                        foreach( $rec as $fld_name => $val ){
                            $set[] = "`$fld_name` = ?";
                            $vals[] = $val;
                        }
                        $set[] = "`timestamp` = ?";
                        $set_str = implode(',', $set);
                        
                        $sql_update = "UPDATE `listings_$platform_lc` SET $set_str WHERE `id` = ?";
                        // $sql_update = "UPDATE `listings_{$lookup_platform[$source]}` SET $set_str WHERE `id` = ?";
                        $sql_vals = array_merge($vals, [$timestamp,$id]);
                        
                        if( $db_write ){
                            $stmt = $db_listings->prepare( $sql_update );
                            $db_listings->beginTransaction();
                            $stmt->execute( $sql_vals );
                            $db_listings->commit();
                        }
                        else{ display_sql_fnc($sql_vals, $sql_update); }
                    }
                    // INSERT 'listings_{PLATFORM}' table
                    else{
                        $sql_insert = "INSERT INTO `listings_$platform_lc` VALUES (?,?,?,?,?,?)";
                        $notes = isset($rec['notes']) ? $rec['notes'] : '';
                        $sql_vals = [$id,'',$rec['new_price'],'0',$notes,$timestamp];
                        
                        if( $db_write ){
                            $stmt = $db_listings->prepare( $sql_insert );
                            $db_listings->beginTransaction();
                            $stmt->execute( $sql_vals );
                            $db_listings->commit();
                        }
                        else{
                            display_sql_fnc($sql_vals, $sql_insert);
                        }
                    }
                }
            }
            // INSERT 'comps_ids' table (DELETE existing records beforehand)
            if( isset($sql_arr['comps_ids']) ){
                foreach( $sql_arr['comps_ids'] as $rec ){
                    if( !isset($_POST['create_new_platform']) ){
                        $sql_delete = "DELETE FROM `comps_ids` WHERE `id` = '{$rec[0]}' AND `source` = '$source'";
                        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px; margin-top:-4px; margin-bottom:6px;">'; print_r($sql_delete); echo '</pre><br>';
                    }
                    
                    // $type_a = type_name2id_fnc($rec[7], $link_type);
                    // $type_b = type_name2id_fnc($rec[8], $link_type);
                    // $type_c = type_name2id_fnc($rec[9], $link_type);
                    
                    $type_a = $rec[7];
                    $type_b = $rec[8];
                    $type_c = $rec[9];
                    
                    $sql_insert = "INSERT INTO `comps_ids` (`id`,`comp1`,`comp2`,`comp3`,`id1`,`id2`,`id3`,`type1`,`type2`,`type3`,`source`,`timestamp`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
                    $sql_vals = [$rec[0],$rec[1],$rec[2],$rec[3],$rec[4],$rec[5],$rec[6],$type_a,$type_b,$type_c,$rec[10],$timestamp];
                    
                    if( $db_write ){
                        $stmt = $db_listings->prepare( $sql_insert );
                        $db_listings->beginTransaction();
                        
                        if( !isset($_POST['create_new_platform']) ){
                            $db_listings->query($sql_delete);
                        }
                        
                        // display_sql_fnc($sql_vals, $sql_insert);
                        $stmt->execute( $sql_vals );
                        $db_listings->commit();
                    }
                    else{ display_sql_fnc($sql_vals, $sql_insert); }
                }
            }
            if( isset($sql_arr['sku']) ){
                $ids = array_unique(array_column($sql_arr['sku'], '0'));
                
                $sql_delete = '';
                
                // DELETE existing SKUs if 'sku_replace' column exists.
                if( !isset($_POST['create_new_platform']) ){
                    foreach( $ids as $id ){
                        if( 'sku_replace' == $op_type ){
                            $sql_delete = "DELETE FROM `skus` WHERE `id` = '$id' AND `source` = '$source'";
                            // echo '<pre style="background:#111; color:#b5ce28; font-size:11px; margin-top:-4px; margin-bottom:6px;">'; print_r($sql_delete); echo '</pre><br>';
                        }
                    }
                }
                
                foreach( $sql_arr['sku'] as $rec ){
                    $sql_insert = "INSERT INTO `skus` VALUES (?,?,?,?)";
                    $sql_vals = [$rec[0],$rec[1],$rec[2],$timestamp];
                    
                    if( $db_write ){
                        $stmt = $db_listings->prepare( $sql_insert );
                        $db_listings->beginTransaction();
                        
                        if( !isset($_POST['create_new_platform']) && '' != $sql_delete ){
                            // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql_delete); echo '</pre>';
                            $db_listings->query($sql_delete);
                        }
                        
                        // display_sql_fnc($sql_vals, $sql_insert);
                        $stmt->execute( $sql_vals );
                        $db_listings->commit();
                    }
                    else{ display_sql_fnc($sql_vals, $sql_insert); }
                }
            }
        }
    }
}
// End update


//=========================================================================
// FUNCTIONS
//=========================================================================
function display_sql_fnc($sql_vals, $sql_statement)
{
    foreach( $sql_vals as $val ){
        $sql_statement = preg_replace('/\?/', "'$val'", $sql_statement, 1);
    }
    echo '<pre style="background:#111; color:#b5ce28; font-size:11px; margin-top:-4px; margin-bottom:6px;">'; print_r($sql_statement); echo '</pre><br>';
}

function sql_arr_fnc($args)
{
    $tbl              = $args['tbl'];
    $fld_names        = $args['fld_names_arr'];
    $rec              = $args['rec'];
    $id               = $args['id'];
    $flds             = isset($args['flds']) ? $args['flds'] : NULL;
    $source           = isset($args['source']) ? $args['source'] : NULL;
    $couriers_lkup    = isset($args['couriers']) ? $args['couriers'] : NULL;
    
    if( 'couriers' == $tbl ){
        $errors = '';
        $sql_arr = [];
        
        $courier_val = $rec[$fld_names['courier']];
        // Check that the courier field value is allowed
        if( !isset($couriers_lkup[$courier_val]) ){
            $errors = "'$courier_val' is not one of our shipping bands!";
        }
        else{
            $shipping_band_num_arr = array_flip(array_keys($couriers_lkup));
            $shipping_band_num = $shipping_band_num_arr[$courier_val]+1;
            
            $sql_arr = [$id,$shipping_band_num];
        }
        
        return [
            'errors'  => $errors,
            'sql_arr' => $sql_arr,
        ];
        
    }
    elseif( 'comps_ids' == $tbl ){
        $regex   = $args['regex'];
        $id_errs = $args['id_errs'];
        
        $link_type      = $args['link_type'];
        // $link_type_flp  = array_flip($link_type);
        // $link_type_str = array_values($link_type);
        // $link_type_str = implode(', ', $link_type);
        
        $tmp = [];
        foreach( $link_type as $key => $val ){
            $tmp[] = "$key ($val)";
        }
        $link_type_str = implode(', ', $tmp);
        
        $errors = '';
        $insert = [];
        for ($j=1; $j<4; $j++) {
            if( isset($fld_names["comp$j"]) && '' != $rec[$fld_names["comp$j"]] ){
                $cits = explode('<$>', $rec[$fld_names["comp$j"]]);
                
                
                
                
                if( 3 != count($cits) ){
                    $errors = "3 values should be supplied (comp, id & type), separated by '<$>'";
                    break;
                }
                elseif( !preg_match('/^\d{1,4}(\.\d{1,2})?$/', $cits[0]) ){
                    $errors = 'Price must be between £1-£10000 and either no pence or 1 to 2 decimal places.';
                    break;
                }
                
                // Check if comp IDs use the correct format.
                // This uses a regex (REGular EXpression) and the $id_errs array
                // to display the correct error message for any given source (a = Amazon, e,s,f = ebay etc).
                // NOTE: $regex and $id_errs are currently located at the top of this script, but they need
                //       to be placed in a config file.
                elseif( !preg_match($regex, $cits[1].$source) ){
                    foreach( $id_errs as $arr ){
                        if( in_array($source, $arr['sources']) ){
                            $err = $arr['error'];
                            break;
                        }
                    }
                    $errors = $err;
                    break;
                }
                
                elseif( !isset($link_type[ $cits[2] ]) ){
                // elseif( !isset($link_type_flp[ strtolower($cits[2]) ]) ){
                    $errors = 'type values can only be '.$link_type_str;
                }
                else{
                    $insert['comps'][$j] = $cits[0];
                    $insert['ids'][$j] = $cits[1];
                    $insert['types'][$j] = $cits[2];
                }
            }
        }
        // END: Loop
        
        $sql_arr = [];
        if( '' == $errors && isset($insert['comps']) ){
            $a = ['c1'=>'','c2'=>'','c3'=>'','i1'=>'','i2'=>'','i3'=>'','t1'=>'','t2'=>'','t3'=>''];
            for ($i=1; $i<count($insert['comps'])+1; $i++) {
                $a["c$i"] = $insert['comps'][$i];
                $a["i$i"] = $insert['ids'][$i];
                $a["t$i"] = $insert['types'][$i];
            }
            $sql_arr = [$id,$a['c1'],$a['c2'],$a['c3'],$a['i1'],$a['i2'],$a['i3'],$a['t1'],$a['t2'],$a['t3'],$source];
        }
        
        return [
            'errors'  => $errors,
            'sql_arr' => $sql_arr,
        ];
    }
    elseif( 'skus' == $tbl ){
        $op_type = '';
        $errors = '';
        
        if( isset($fld_names['sku_add']) ){ $op_type = 'sku_add'; }
        elseif( isset($fld_names['sku_replace']) ){ $op_type = 'sku_replace'; }
        
        $sku_str = trim($rec[$fld_names['sku']]);
        
        // If 'sku' field or 'sku_add'/'sku_replace' field is missing, but not both
        if( ('' == $sku_str && '' != $rec[$fld_names[$op_type]]) || ('' != $sku_str && '' == $rec[$fld_names[$op_type]]) ){ // Notice: Undefined index
            $errors = "Skus require 2 fields (sku, sku_op) and both fields to be populated - $sku_str.";
        }
        // If 'sku_add' field exists, check that 'sku_add' value is only a number
        elseif( isset($fld_names['sku_add']) && preg_match('/^(\d+)$/', $rec[$fld_names['sku_add']], $m) ){
            $total_skus = $m[1];
        }
        // If 'sku_replace' field exists, check that 'sku_replace' value is only a number
        elseif( isset($fld_names['sku_replace']) && preg_match('/^(\d+)$/', $rec[$fld_names['sku_replace']], $m) ){
            $total_skus = $m[1];
        }
        // 'sku_add' or 'sku_replace' don't just contain digits
        else{
            // 
        }
        
        $sql_arr = [];
        if( '' != $op_type && '' != $sku_str ){
            $skus_total = substr_count($sku_str,'<$>')+1;
            
            // Check that $total_skus value matches number of skus in 'sku' fields.
            if( $skus_total == $total_skus ){
                // Add to array to be inserted into table later
                foreach( explode('<$>', $sku_str) as $sku ){
                    if( '' != $sku ){
                        $sql_arr[] = [$id,$sku,$source];
                    }
                }
            }
            // Numbers don't match
            else{
                $errors = "$skus_total sku(s) supplied but 'sku_op' number ($total_skus) doesn't match!";
            }
        }
        
        return [
            'errors'  => $errors,
            'sql_arr' => $sql_arr,
            'op_type' => $op_type,
        ];
    }
    else{
        $sql_arr = [];
        foreach( $flds as $fld ){
            if( isset($fld_names[$fld]) && '' != $rec[$fld_names[$fld]] ){ $sql_arr[$tbl][$id][$fld] = $rec[$fld_names[$fld]]; }
        }
        
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql_arr); echo '</pre>';
        
        return $sql_arr;
    }
}

// Append array2 (multi-dimensional) to array1 (multi-dimensional)
function array_merge_recursive_distinct_fnc( array &$array1, array &$array2 )
{
    $merged = $array1;
    foreach ( $array2 as $key => &$value ){
        if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ){
            $merged[$key] = array_merge_recursive_distinct_fnc( $merged[$key], $value );
        }
        else{
            $merged[$key] = $value;
        }
    }
    return $merged;
}

// function type_name2id_fnc($type_name, $link_type)
// {
//     $lkup = [
//         'like 4 like' => 1,
//         'cheapest' => 2,
//         'most popular' => 3,
//         'out of stock' => 4,
//     ];
    
//     $type_name = strtolower($type_name);
    
//     return '' != $type_name ? $lkup[$type_name] : '';
// }


// Get config_fees
$sql = "SELECT * FROM `config_fees`";
$results = $db_listings->query($sql);
$config_fees = $results->fetchAll(PDO::FETCH_ASSOC);

$projection_20perc_options = [];
$platform_fee_options = [];
foreach( $config_fees as $rec ){
    if( 'projection_20perc' == $rec['type'] ){
        $projection_20perc_options[] = [
            'val' => $rec['value'],
            'id' => $rec['id'],
        ];
    }
    elseif( 'platform_fees' == $rec['type'] ){
        $platform_fee_options[] = [
            'val' => $rec['value'],
            'id' => $rec['id'],
        ];
    }
}

// Get existing platforms
$sql = "SELECT * FROM `platforms`";
$results = $db_listings->query($sql);
$platforms = $results->fetchAll(PDO::FETCH_ASSOC);

// Get header colours
$sql = "SELECT * FROM `header_colours`";
$results = $db_listings->query($sql);
$header_colours = $results->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .tb-pad{
        padding-top: 4px;
        padding-bottom: 4px;
    }
    .color_picker{
        display: inline;
        width: 120px;
        height: 30px;
        padding: 8px;
    }
    <?php
    foreach( $header_colours as $rec ){
        $id = $rec['id'];
        $bg_color = $rec['bg-color'];
        $fg_color = '' == $rec['fg-color'] ? '#fff' : $rec['fg-color'];
    ?>
    .cp<?= $id ?>{ background:<?= $bg_color ?>; color:<?= $fg_color ?>; }
    <?php } ?>
</style>

<form method="post" class="fl-l mr30">
    <input type="hidden" name="user" value="<?= $_POST['user'] ?>">
    <input type="submit" name="view" value="Dashboard" class="btn">
</form>

<div style="font-size: 30px;">Import</div>

<div class="cl-l mb20"></div>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="user" value="<?= $_POST['user'] ?>">
    <input type="hidden" name="view" value="Import">
    
    <input type="submit" value="Upload" name="upload_file" disabled>
    <input type="file" name="fileToUpload" required>
    <br><br>
    
    
    <div style="display: none;" class="select_platform_class">
        <span class="create_new_platform_ckbox">
            <label class="hand"><input type="checkbox" name="create_new_platform" class="create_new_platform hand"> Create New Platform</label>
        </span>
        
        <span class="select_platform_span">
            <select name="select_platform">
                <option value="">Select Platform</option>
            <?php foreach( $platforms as $rec ){ ?>
                <option value="<?= $rec['id'] ?>"><?= $rec['txt'] ?></option>
            <?php } ?>
            </select>
    </div>
    
    <div style="display: none;" class="header_picker">
        <fieldset style="display: inline-block; border-radius: 8px; border: 1px solid #ccc;">
            <legend><h2 style="color: #000; font-size: 20px;">Header Colour</h2></legend>
            
            <label class="hand"><input type="radio" name="header_color" value="1" class="hand"> <div class="color_picker cp1">Product Name</div></label>
            <label class="hand"><input type="radio" name="header_color" value="2" class="hand"> <div class="color_picker cp2">Product Name</div></label>
            <label class="hand"><input type="radio" name="header_color" value="3" class="hand"> <div class="color_picker cp3">Product Name</div></label>
            <label class="hand"><input type="radio" name="header_color" value="4" class="hand"> <div class="color_picker cp4">Product Name</div></label>
            <label class="hand"><input type="radio" name="header_color" value="5" class="hand"> <div class="color_picker cp5">Product Name</div></label>
            <label class="hand"><input type="radio" name="header_color" value="6" class="hand"> <div class="color_picker cp6">Product Name</div></label>
            <label class="hand"><input type="radio" name="header_color" value="7" class="hand"> <div class="color_picker cp7">Product Name</div></label>
            <br><br>
        </fieldset>
        
        <div></div>
        
        <fieldset id="fieldset2" style="display: none; border-radius: 8px; border: 1px solid #ccc;">
            <legend><h2 style="color: #000; font-size: 20px;">Platform Fields</h2></legend>
            ID: <input type="text" name="platform_id" class="w40 mr20" autocomplete="off" disabled>
            Platform Name: <input type="text" name="platform_name" class="w140 mr20" autocomplete="off">
            20%: <select name="platform_20perc">
            <?php foreach( $projection_20perc_options as $rec ){ ?>
                <option value="<?= $rec['id'] ?>"><?= $rec['val'] ?></option>
            <?php } ?>
            </select>
            Fee: <select name="platform_fees">
            <?php foreach( $platform_fee_options as $rec ){ ?>
                <option value="<?= $rec['id'] ?>"><?= $rec['val'] ?></option>
            <?php } ?>
            </select>
            <label class="hand"><input type="checkbox" name="no_price_matrix" value="1" class="hand"> No Price Matrix &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<br><span style="float:right;">(check if platform uses eBay prices)</span></label>
            <br>
            <div class="mt10 red-bg">Note: Default ID is first letter of Platform Name (* MUST BE UNIQUE *)</div>
        </fieldset>
    </div>
</form>

<!-- Display notes -->
<?php if( !isset($_POST['upload_file'])  ){ ?>
<code style="
    position: absolute;
    left: 10px;
    top: 440px;
    padding: 10px;
    border-radius: 8px;
    max-width: 1600px;
    /*height: 200px;*/
    font-size: 14px;
    background: #bbb;
    color: #000;
">
<div style="font-size:18px; font-weight: bold; margin-bottom:6px;">NOTES:</div>
CSV import can be used to add new platform listings or replace existing listings. The 'product_name' field is used to identify the item to replace (must be included), but this field cannot be edited.
<br>The following fields can be replaced: <i>'<b>packing</b>', '<b>packaging_band</b>', '<b>lowest_variation_weight</b>', '<b>variation</b>', '<b>remove</b>', '<b>prev_price</b>', '<b>new_price</b>', '<b>perc_advertising</b>', '<b>notes</b>', '<b>comp1</b>', '<b>comp2</b>', '<b>comp3</b>', '<b>sku</b>'</i>.
<br>Any field can be excluded, if not required, and fields can be in any order. The field titles must be on the first row. An error will be displayed if spelt incorrectly. If a value is left empty the original value will not be replaced.
<div style="margin-top:4px; margin-bottom:-10px;">Example:</div>

<style>
    .pre_tbl{
        font-family: monospace;
        font-size:13px;
        padding:0;
        padding-left:3px;
        padding-right:3px;
        border-radius:0;
    }
</style>

<pre class="pre_tbl">
┌───────────────────────────┬────────────────┬────────────┬───────────┬─────────────────────────┬─────────────────────────┬─────────────────────────┬─────────┐
│       product_name        │ packaging_band │ prev_price │ new_price │ comp1                   │ comp2                   │           sku           │ sku_add │
├───────────────────────────┼────────────────┼────────────┼───────────┼─────────────────────────┼─────────────────────────┼─────────────────────────┼─────────┤
│ Brown Rock Salt x 1kg bag │              5 │            │      5.49 │ 8.04<$>114370524618<$>1 │ 7.99<$>162945819029<$>3 │ Rock-Salt-Brown-1kg-Bag │       1 │
│ Brown Rock Salt x 5kg bag │              5 │       7.99 │      7.99 │ 9.99<$>185102128915<$>2 │                         │ Rock-Salt-Brown-5kg-Bag │       1 │
└───────────────────────────┴────────────────┴────────────┴───────────┴─────────────────────────┴─────────────────────────┴─────────────────────────┴─────────┘
</pre>

<br>In the previous example the 'prev_price' for 'Brown Rock Salt x 1kg bag' will be left untouched, and likewise for 'Brown Rock Salt x 5kg bag' 'comp2'.

<div style="margin-top:4px;">
    The 'sku' field requires an additional field: ➤ '<b>sku_add</b>' or ➤ '<b>sku_replace</b>'. Unlike all the other fields discussed so far, additional skus can also be added (not just replaced). In the above example, 1 sku is being added for each Rock Salt item.
</div>

<div style="margin-top:-10px; margin-bottom:-14px;">
    The reason for the number is because multiple skus can be added, and it serves as simple cross reference. In the following example, 2 skus are being added &ndash; separated by <pre style="font-size:13px; padding:0; padding-left:3px; padding-right:3px; border-radius:0;"><$></pre>:
</div>

<pre class="pre_tbl" style="font-size:16px; float:left; margin-right:10px;">
┌───────────────────────────┬───────────────────────────────────────────────────┬─────────┐
│       product_name        │           sku                                     │ sku_add │
├───────────────────────────┼───────────────────────────────────────────────────┼─────────┤
│ Brown Rock Salt x 1kg bag │ Rock-Salt-Brown-1kg-Bag<span class="pre_tbl" style="padding-left:0;padding-right:0;color:#0f0; font-size:16px;"><$></span>rock_salt_brown_1kg_bag │       2 │
└───────────────────────────┴───────────────────────────────────────────────────┴─────────┘
</pre>
<div><br>If the number of skus in the <i>'<b>sku</b>'</i> column does not match the number in the<br>
<i>'<b>sku_add</b>'</i> / <i>'<b>sku_replace</b>'</i> column, an error will be displayed.</div>

<div style="clear:left;">
    Comps, IDs and Types:<br>
    The comp fields (comp1, comp2, comp3) require 3 values - price, link id and type (separated by '<$>').<br>
    <!-- The comp fields (comp1, comp2, comp3) require 3 values - price, link id and type (separated by '-').<br> -->
    Price must be between £1–£1000 and either no pence or 1 to 2 decimal places.<br>
    The link id format depends on the platform. eBay is a 12 digit number. Amazon/Prime is a mixture of 10 uppercase letters/digits, beginning with B.<br>
    Type can be 1 of 4 values: ➤ 1 (Like 4 Like), ➤ 2 (Cheapest), ➤ 3 (Most popular) ➤ 4 (Out of Stock). <b><i>*** Type names cannot be used. Numbers only ***</i></b>
</div>

</code>
<?php } ?>

<?php
if( isset($errors['csv']) ){
    // array_unshift($errors['csv'], 'CSV IMPORT ERROR(s) - The first row must contain the field names.');
    echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">CSV Errors:<br>'; print_r($errors['csv']); echo '</pre>';
}
if( isset($errors['product_name']) ){
    echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">CSV Errors:<br>'; print_r($errors['product_name']); echo '</pre>';
}
if( isset($errors['comps_ids']) ){
    echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">CSV Errors:<br>'; print_r($errors['comps_ids']); echo '</pre>';
}
if( isset($errors['sku']) ){
    echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">CSV Errors:<br>'; print_r($errors['sku']); echo '</pre>';
}

// if( isset($sql_arr['listing']) ){
//  echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql_arr['listing']); echo '</pre><br>';
// }
// if( isset($sql_arr['listings_pf']) ){
//  echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql_arr['listings_pf']); echo '</pre><br>';
// }
// if( isset($sql_arr['comps_ids']) ){
//  echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql_arr['comps_ids']); echo '</pre><br>';
// }
// if( isset($sql_arr['sku']) ){
//  echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sql_arr['sku']); echo '</pre>';
// }
?>

<br><span class="error-txt"></span>

<script>
    <?php
    // Create 'existingPlatformNames' object (assoc array) to check if platform ID already exists
    $tmp = [];
    foreach( $platforms as $rec ){
        $tmp[] = "\"{$rec['id']}\":\"{$rec['txt']}\"";
    }
    ?>
    let existingPlatformNames = {<?= implode(",", $tmp) ?>};
    
    let header_colour_checked = false;
    let platform_name_entered = false;
    
    $(function(){
        // Dipslay 'Create New Platform' checkbox and 'Select Platform' dropdown if csv file selected.
        $("input:file").change(function(){
            let fileName = $(this).val();
            
            if( fileName.endsWith('.csv') ){
                $('.select_platform_class').css({'display': 'inline-block'});
            }
        });
        
        // Enables 'Upload' button when 'select_platform' dropdown changes.
        $(document).on('change', '[name="select_platform"]', function(){
            if( '' != $(this).val() && $("input:file").val().endsWith('.csv') ){
                // Enable 'Upload' button
                $(':input[type="submit"]').prop('disabled', false);
                // Hide 'Create New Platform' checkbox when 'Select Platform' dropdown selects a platform
                $('.create_new_platform_ckbox').css({'display': 'none'});
            }
            else{
                // Disable 'Upload' button
                $(':input[type="submit"]').prop('disabled', true);
                // Display 'Create New Platform' checkbox when 'Select Platform' dropdown selects 'Slect Platform' option  (no platform)
                $('.create_new_platform_ckbox').css({'display': 'inline-block'});
            }
        });
        
        // Display extra fields if 'Create New Platform' is clicked
        $(document).on('change', '.create_new_platform', function(){
            if( $(this).is(':checked') ){
                // Hide 'Select Platform' dropdown when 'Create New Platform' checkbox checked
                $('.select_platform_span').css({'display': 'none'});
                
                $('.header_picker').css({'display': 'block'});
                // $('[name="platform_name"]').focus();
            }
            else{
                // Display 'Select Platform' dropdown when 'Create New Platform' checkbox unchecked
                $('.select_platform_span').css({'display': 'inline-block'});
                $('.header_picker').css({'display': 'none'});
            }
            
        });
        
        $(document).on('change', '[name="header_color"]', function(){
            $('#fieldset2').css({'display': 'inline-block'});
            $('[name="platform_name"]').focus();
        });
        
        
        let platform_name_required_chars = 0;
        let platform_name_4chars = false;
        let platform_id_unique = false;
        let first_char = '';
        
        $(document).on('input', '[name="platform_name"]', function(){
            first_char = $(this).val().charAt(0).toLowerCase();
            
            platform_name_required_chars = $('[name="platform_name"]').val().length;
            
            // Display error message if 'Platform Name' is less than 4 characters
            if( platform_name_required_chars < 4 ){
                platform_name_4chars = false;
                
                $('.error-txt').css({'display': 'inline-block'});
                $('.error-txt').html( "'Platform Name' must 4 or more characters!" );
                
                if( !platform_id_unique ){
                    $('[name="platform_id"]').prop('disabled', true);
                    $('[name="platform_id"]').val('');
                }
            }
            // Remove error message if 'Platform Name' is more than 3 characters
            else{
                $('[name="platform_id"]').prop('disabled', false); // MOVE FROM HERE
                
                
                platform_name_4chars = true;
                
                $('.error-txt').css({'display': 'none'});
                $('.error-txt').html('');
                
                // console.log(existingPlatformNames);
                
                // ID needs to be unique - not used by any other platform.
                if( !existingPlatformNames[first_char] ){
                    $('.error-txt').css({'display': 'none'});
                    $('.error-txt').html('');
                    
                    platform_id_unique = true;
                }
                else{ platform_id_unique = false; }
                
                if( platform_id_unique ){
                    // $('[name="platform_id"]').prop('disabled', false); // MOVE FROM HERE
                    $('[name="platform_id"]').val( first_char );
                }
                
                if( !platform_id_unique && existingPlatformNames[first_char] ){
                    $('.error-txt').css({'display': 'inline-block'});
                    $('.error-txt').html( "ID '" + first_char + "' already exists. Enter a different letter." );
                }
            }
            
            // // ID needs to be unique - not used by any other platform.
            // if( !existingPlatformNames[first_char] ){
            //     $('.error-txt').css({'display': 'none'});
            //     $('.error-txt').html('');
                
            //     platform_id_unique = true;
            // }
            
            // console.log(platform_id_unique + ' | ' + platform_name_4chars);
            
            // Enable/disable submit button
            if( platform_id_unique && platform_name_required_chars > 3 ){
                $(':input[type="submit"]').prop('disabled', false);
            }
            else{
                $(':input[type="submit"]').prop('disabled', true);
            }
        });
        
        
        $(document).on('input', '[name="platform_id"]', function(){
            // console.log('platform_id');
            
            first_char = $(this).val().charAt(0).toLowerCase();
            
            // Only allows 1 character to be entered into the 'platform_id' text box.
            $(this).val(first_char);
            
            // ID needs to be unique - not used by any other platform.
            if( !existingPlatformNames[first_char] ){
                $('.error-txt').css({'display': 'none'});
                $('.error-txt').html('');
                
                platform_id_unique = true;
            }
            else{ platform_id_unique = false; }
            
            let no_id = true;
            if( '' != $('[name="platform_id"]').val() ){
                no_id = false;
            }
            
            if( !platform_id_unique && existingPlatformNames[first_char] ){
                $('.error-txt').css({'display': 'inline-block'});
                $('.error-txt').html( "ID '" + first_char + "' already exists. Enter a different letter." );
                platform_id_unique = false;
            }
            else{ platform_id_unique = true; }
            
            // console.log(platform_id_unique + ' | ' + platform_name_4chars);
            
            // Enable/disable submit button
            if( !no_id && platform_id_unique &&  platform_name_4chars ){
                $(':input[type="submit"]').prop('disabled', false);
            }
            else{
                $(':input[type="submit"]').prop('disabled', true);
            }
        });
        
        
        /*
        let platform_name_required_chars = 0;
        let platform_id_unique = false;
        let first_char = '';
        
        $(document).on('input', '[name="platform_name"]', function(){
            first_char = $(this).val().charAt(0).toLowerCase();
            
            platform_name_required_chars = $('[name="platform_name"]').val().length;
            
            if( platform_name_required_chars < 4 ){
                $(':input[type="submit"]').prop('disabled', true);
                
                $('.error-txt').css({'display': 'inline-block'});
                $('.error-txt').html( "'Platform Name' must 4 or more characters!" );
            }
            else{
                $(':input[type="submit"]').prop('disabled', false);
                
                $('.error-txt').css({'display': 'none'});
                $('.error-txt').html('');
            }
            
            if( platform_id_unique && platform_name_required_chars > 3 ){
                $(':input[type="submit"]').prop('disabled', false);
            }
            else{
                $(':input[type="submit"]').prop('disabled', true);
            }
        });
        
        
        $(document).on('input', '[name="platform_id"]', function(){
            first_char = $(this).val().charAt(0).toLowerCase();
            
            if( !existingPlatformNames[first_char] ){
                
            }
        });
        */
    });
</script>