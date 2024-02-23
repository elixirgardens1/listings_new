<?php
if (isset($_POST['add_group_note_form'])) {
	echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($_POST); echo '</pre>'; die();
}

/*
Array
(
    [add_group_note_form] => 
    [modal_id] => 
    [posY] => 0
    [platform] => e
    [cat] => agg
    [cat_id] => a8
    [user] => 1
    [view] => Listings
    [group_note] => Lorem ipsum dolor sit amet consectetur adipisicing elit.
)
*/