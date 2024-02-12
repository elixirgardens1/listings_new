<!-- Upload data in to stock_control.db3 database with csv file in sku_am_eb table.
Needed to link all sku to platforms -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Platform Skus Uploads</title>
<style>
   {
      font-family: arial;
   }
   
   /* Button */
   .btn {
      background: #579;
      border: 1px solid #124;
      color: #fff;
      padding: 4px 8px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-family: arial;
      font-size: 1.6em;
      border-radius: 4px;
      cursor: pointer;
      -webkit-transition-duration: 0.1s;
      transition-duration: 0.1s;
   }
   .btn:hover {
      background: #005987;
      color: #fff;
   }
   .error {
      color: red;
   }
   
   table.tbl-1{
      border-collapse: collapse;
   }
   .tbl-1 th,.tbl-1 td{
      border: 1px solid #000;
      padding: 5px;
      vertical-align: top;
   }
   .tbl-1 td{
      font-size: 13px;
   }
   .tbl-1 td{ text-align: left; }
   .tbl-1 tr:nth-child(2n+2){ background: #dcdcfc; } /* light blue */
   .tbl-1 thead tr{ background: #ccc; } /* light grey */
   
   textarea {
      width: 400px;
      height: 60px;
   }
</style>
</head>
<body>
<form action="platform.php" method="post" enctype="multipart/form-data">
   <div style="float: left; margin-right: 30px;">
      <h3>Add or Update platform links</h3>
      <p><input type="file" name="file" id="file"></p>
      <p><button type="submit" id="submit" name="Import" class="btn">Upload CSV</button></p>
   </div>
   <div style="float: left; padding-top: 10px; margin-right: 30px;">
      The 3 column headings must be 'sku', 'id' & 'platform'.<br>
      <b><i>&#10148; CSVs must be saved: CSV Comma delimited</i></b><br>
      <b><i>&#10148; Every listing item must have a 'sku', 'id' & 'platform'.</i></b><br>
      <b><i>&#10148; Platform: 'a' - Amazon, 'e' - Ebay & 'w' - Website</i></b>
   </div>
   <div>
      Example:<br>
      <img src="..\img\platform_example_upload.png">
   </div>
</form>
<hr>
<form action="platform.php" method="post" enctype="multipart/form-data">
   <div style="float: left; margin-right: 30px;">
      <h3>Delete no needed links&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</h3>
      <p><input type="file" name="file" id="file"></p>
      <p><button type="submit" id="submit" name="Delete" class="btn">Upload CSV</button></p>
   </div>
   <div style="float: left; padding-top: 10px; margin-right: 30px;">
      Only one column heading must be 'sku'.<br>
      <b><i>&#10148; CSVs must be saved: CSV Comma delimited &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</i></b><br>
      <b><i>&#10148; Every listing item must have a 'sku' & 'platform'.</i></b><br>
      <b><i>&#10148; Platform: 'a' - Amazon, 'e' - Ebay & 'w' - Website</i></b>
   </div>
   <div>
      Example:<br>
      <img src="..\img\platform_example_delete.png">
   </div>
</form>
<?php
if(isset($_POST["Delete"])){
   $filename=$_FILES["file"]["tmp_name"];    
   if($_FILES["file"]["size"] > 0){ 
      $file = fopen($filename, "r");
      $keys = fgetcsv($file, 0, ",");
      while (($line = fgetcsv($file, 0, ",")) !== FALSE) {
         $data_file[] = array_combine($keys, $line);
      }
      fclose($file);
      echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($data_file); echo '</pre>'; die();
      $xDrivePath = 'C:/xampp/htdocs';
      $stock_c = "$xDrivePath\stocksystem\PHPAPI\stock_control.db3";
      $stock_control = new PDO('sqlite:'.$stock_c);
      // $stock_control = new PDO('sqlite:stock_control.db3');
      $stmt = $stock_control->prepare("DELETE FROM `sku_am_eb` WHERE `sku`=? AND `platform`=?");
      $stock_control->beginTransaction();
      foreach ($data_file as $vals) {
         $stmt->execute([$vals['sku'],$vals['platform']]);
      }
      $stock_control->commit();
      echo 'DELETED';
   }
   else{
      echo 'file empty';
   }
}
//--------------------------------------------------------------------------------
if(isset($_POST["Import"])){
   $filename=$_FILES["file"]["tmp_name"];    
   if($_FILES["file"]["size"] > 0){ 
      $file = fopen($filename, "r");
      $keys = fgetcsv($file, 0, ",");
      while (($line = fgetcsv($file, 0, ",")) !== FALSE) {
         $data_file[] = array_combine($keys, $line);
      }
      fclose($file);
      $xDrivePath = 'C:/xampp/htdocs';
      $stock_c = "$xDrivePath\stocksystem\PHPAPI\stock_control.db3";
      $stock_control = new PDO('sqlite:'.$stock_c);
      // $stock_control = new PDO('sqlite:stock_control.db3');
      $stmt = $stock_control->prepare("INSERT OR REPLACE INTO `sku_am_eb` (
         'sku',
         'id',
         'platform'
      ) VALUES (?,?,?)");
      $stock_control->beginTransaction();
      foreach ($data_file as $vals) {
         $stmt->execute([ $vals['sku'],$vals['id'],$vals['platform'] ]);
      }
      $stock_control->commit();
      echo 'UPLOADED';
   }
   else{
      echo 'file empty';
   }
}
?>

</body>
</html>