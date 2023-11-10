<?php
$files_used[] = 'views/login.php'; //DEBUG
?>
<form method="post">
	<select name="user" onchange="this.form.submit()">
		<option disabled="" selected="select">Please Select Your Name</option>
	<?php foreach( $users as $name => $id ): ?>
		<option value="<?= $id ?>"><?= $name ?></option>
	<?php endforeach; ?>
	</select>
	<input type="hidden" name="view" value="Dashboard">
</form>