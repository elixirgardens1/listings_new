<?php
/*
http://localhost/listings_new/tools/update_listings_new_price.php
http://192.168.0.24/FESP-REFACTOR/listings_new/tools/update_listings_new_price.php

SELECT id,new_price FROM `listings_ebay` WHERE `id` IN ('2578','6406','14137','9306','9310','9305');
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (isset($_POST['submit'])){
    $data = explode("\r\n", trim($_POST['data']));
    $data = array_map('trim', $data);
    $data = array_map(
        function ($value) {
            return preg_replace('/\s+/', ' ', $value);
        },
        $data
    );
    
    $errors = [];
    foreach ($data as $rec) {
        if (false === stripos($rec, ' ')) {
            $errors[] = "'id' & 'new_price' must be separated by a space!";
        }
        if (preg_match('/\s.*\s/', $rec)) {
            $errors[] = "There can only be a single space?";
        }
    }
    
    if (!$errors) {
        $db = new PDO('sqlite:../dbase/listings_NEW.db3');
        
        $tbl = $_POST['platform'];
        
        $stmt = $db->prepare("UPDATE `$tbl` SET `new_price` = ? WHERE `id` = ?");
        
        $db->beginTransaction();
        foreach ($data as $rec) {
            list($id, $np) = explode(' ', $rec);
            $stmt->execute( [$np, $id] );
        }
        $db->commit();
        
        $total_records_updated = count($data);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Update new price (listings)</title>

</head>
<body>

<form method="post">
    <select name="platform">
        <option value="listings_ebay">listings_ebay</option>
        <option value="listings_amazon">listings_amazon</option>
        <option value="listings_web">listings_web</option>
        <option value="listings_prime">listings_prime</option>
    </select><br>

    <textarea name="data" placeholder="id new_price" style="height: 400px;"></textarea>

    <input type="submit" name="submit" value="Update">
</form>

<?php
if (isset($_POST['submit'])) {
    if (isset($total_records_updated)) {
        echo $total_records_updated . ' records updated.';
    }
    elseif ($errors) {
        echo "<h3 style='margin-bottom:10px;''>Error(s):</h3>";
        echo '* '.implode("<br>* ", $errors);
    }
}
?>


</body>
</html>