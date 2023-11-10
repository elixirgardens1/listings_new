<?php
$files_used[] = 'js/js_retain_scroll_pos.php'; //DEBUG
?>
<script>
$(function() {
	// Set 'posY' hidden form field to current scroll position
	$(document).scroll(function(e) {
		var scroll = $(document).scrollTop();
		$('input[name="posY"]').val(scroll);
	});

	// Keep the same scroll position when page reloads
	<?php if (isset($_POST['posY']) ): ?>
	$(document).scrollTop(<?= $_POST['posY'] ?>);
	<?php endif; ?>
});
</script>