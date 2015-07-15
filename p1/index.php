<?php
require_once "./class-pshots.php";
if (class_exists ( 'pshots' )) {
	$o_pshots = new pshots ();
	if ($o_pshots->have_snapshot ()) {
		$o_pshots->send_snapshot ();
	} else {
		$o_pshots->send_default_image ();
	}
	if ($o_pshots->must_requeue ()) {
		$o_pshots->requeue_snapshot ();
	}
} else {
	die ( "The pshots class has not been defined." );
}
?>