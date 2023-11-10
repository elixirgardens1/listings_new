<?php
$files_used[] = 'views/back_to_cat.php'; //DEBUG
?>
<form method="post">
	<input type="submit" name="back_to_category" value="Back to category" class="btn w200">
	
	<input type="hidden" name="posY" value="<?= $_POST['posY'] ?>">
	
	<input type="hidden" name="view" value="Listings">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="hidden" name="cat" value="<?= isset($_POST['cat']) ? $_POST['cat'] : '' ?>">
	<input type="hidden" name="cat_id" value="<?= isset($_POST['cat_id']) ? $_POST['cat_id'] : '' ?>">
	<input type="hidden" name="platform" value="<?= $_POST['platform'] ?>">
</form>