<?php
/*
http://localhost/listings_new/tools/group_signoff.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../incs/db_connections.php';
$db_listings = new PDO("sqlite:$listings_db_path");
$db_stock    = new PDO("sqlite:$stock_control_db_path");

// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT cat_id,group_,product_name FROM `listings` WHERE `remove` IS NULL";
$res = $db_listings->query($sql);
$listings_info = $res->fetchAll(PDO::FETCH_ASSOC);

$tmp = [];
foreach ($listings_info as $rec) {
    $tmp[] = [
        'sub_cat'      => $rec['cat_id'],
        'group'        => $rec['group_'],
        'product_name' => $rec['product_name'],
    ];
}
$listings_info = $tmp;
/*
[
    [
        [sub_cat] => a78
        [group] => a
        [product_name] => Frost Fleece | 1.5m thermagro FL1807 x 5m
    ][
        [sub_cat] => a78
        [group] => a
        [product_name] => Frost Fleece | 1.5m thermagro FL1807 x 10m
    ][
        [sub_cat] => a78
        [group] => a
        [product_name] => Frost Fleece | 1.5m thermagro FL1807 x 15m
    etc.
*/

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($listings_info); echo '</pre>'; die();

// Category lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT cat,name FROM `cats`";
$res = $db_stock->query($sql);
$cats_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);
/*
[
    [agg] => Aggregates
    [bam] => Bamboo
    [bir] => Birds / Wildlife
    etc.
*/
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($cats_lkup); echo '</pre>'; die();


// Sub category lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT cat,cat_id,product_cat FROM `lookup_prod_cats`";
$res = $db_listings->query($sql);
$sub_cats_lkup = $res->fetchAll(PDO::FETCH_ASSOC);

$tmp = [];
foreach ($sub_cats_lkup as $rec) {
    $tmp[$rec['cat_id']] = [
        'cat' => $rec['cat'],
        'product_cat' => $rec['product_cat'],
    ];
}
$sub_cats_lkup = $tmp;
/*
[
    [a1] => [
        [cat] => acc
        [product_cat] => Doormats
    ]
    [a2] => [
        [cat] => acc
        [product_cat] => Pen
    ]
    [a3] => [
        [cat] => acc
        [product_cat] => Raised Bed
    etc.
*/

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($sub_cats_lkup); echo '</pre>'; die();

$listings_groups = [];
foreach ($listings_info as $rec) {
    $cat = $sub_cats_lkup[$rec['sub_cat']]['cat'];
    $sub_cat = $sub_cats_lkup[$rec['sub_cat']]['product_cat'];
    // rec['sub_cat']
    $group = $rec['group'];
    $product_name = $rec['product_name'];
    $listings_groups[$cats_lkup[$cat]][$sub_cat][$group][] = $product_name;
}
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($listings_groups); echo '</pre>'; die();

foreach ($listings_groups as $cat => $arr1) {
    foreach ($arr1 as $sub_cat => $arr2) {
        foreach ($arr2 as $group => $groups_items) {
            if (count($groups_items) > 1) {
                $root = extract_common_start($groups_items);
                $root = preg_replace('/ x$/', '', trim($root));
                
                // $root = extract_common_end($groups_items);
                
                if ('' == $root) {
                    $root = '__EMPTY_ROOT__';
                }
                
                $listings_groups[$cat][$sub_cat][$group]['root'] = $root;
            }
        }
    }
}

$tmp = [];
foreach ($listings_groups as $cat => $arr1) {
    foreach ($arr1 as $sub_cat => $arr2) {
        foreach ($arr2 as $g_ => $groups_items) {
            // Single item
            if (!isset($groups_items['root'])) {
                $root = $groups_items[0];
                $tmp[] = "$cat | $sub_cat | '__SINGLE_ITEM__' | $root";
            }
            elseif ('__EMPTY_ROOT__' != $groups_items['root']) {
                $root = $groups_items['root'];
                $tmp[] = "$cat | $sub_cat | $root";
            }
            elseif ('__EMPTY_ROOT__' == $groups_items['root']) {
                unset($groups_items['root']);
                
                $items = implode("|", $groups_items);
                
                $tmp[] = "$cat | $sub_cat | __GROUP__ $g_ | $items";
            }
        }
    }
}

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r(implode("\n", $tmp)); echo '</pre>'; die();

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($listings_groups); echo '</pre>'; die();


function extract_common_start($arr)
{
    $words = array_map('explode', array_fill(0, count($arr), ' '), $arr);
    $min_length = min(array_map('count', $words));
    $common_start = '';
    for ($i = 0; $i < $min_length; $i++) {
        $word = $words[0][$i];
        foreach ($words as $w) {
            if ($w[$i] !== $word) {
                return $common_start;
            }
        }
        $common_start .= ($i > 0 ? ' ' : '') . $word;
    }
    return $common_start;
}

function extract_common_end($arr)
{
    $reversed = array_map('strrev', $arr);
    $common_start_reversed = extract_common_start($reversed);
    $common_end = strrev($common_start_reversed);
    return $common_end;
}


// User lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT * FROM `user`";
$res = $db_listings->query($sql);
$user_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

// Required due to removal of secondary user records
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$user_lkup[11] = 'David 2';
$user_lkup[12] = 'Lewis 2';
$user_lkup[13] = 'Kevin 2';
$user_lkup[14] = 'Mark 2';
$user_lkup[15] = 'Rachel 2';
$user_lkup[16] = 'Robert 2';
$user_lkup[17] = 'Josh 2';
$user_lkup[18] = 'Vova 2';
$user_lkup[19] = 'Peter 2';

// Platform lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT id,txt FROM `platforms`";
$res = $db_listings->query($sql);
$platform_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);


