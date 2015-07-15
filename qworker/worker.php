<?php
define ( 'queue', 21671 );
define ( 'IMGPATH', "/var/www/html/static/work" );

$queue = msg_get_queue ( queue );

$msg_type = NULL;
$msg = NULL;
$max_msg_size = 2048;

while ( msg_receive ( $queue, 1, $msg_type, $max_msg_size, $msg ) ) {
	$urlid = $msg->id;
	$snap_file = $msg->file;
	$snap_url = $msg->url;
	$snap_url = escapeshellcmd ( $snap_url );
	$snph = $msg->snap_h;
	$snpw = $msg->snap_w;
	$snpt = $msg->snap_top;
	$snpl = $msg->snap_left;
	$scliph = $msg->snap_cliph;
	$sclipw = $msg->snap_clipw;
	$src = "var page = require('webpage').create();
		page.viewportSize = { width: {$snpw}, height: {$snph} };
		page.clipRect = { top: {$snpt}, left: {$snpl}, width: {$sclipw}, height: {$scliph} };
		page.open('{$snap_url}', function (status) {
			if (status !== 'success') {
				phantom.exit();
				}
			else {
				window.setTimeout(function () {
				page.render('{$snap_file}');
				phantom.exit();
				}, 4000);
			}
		}); ";
	
	$job_file = IMGPATH . '/' . $urlid . '.js';
	file_put_contents ( $job_file, $src );
	
	$exec = '/usr/local/bin/phantomjs ' . $job_file;
	$escaped_command = escapeshellcmd ( $exec );
	exec ( $escaped_command );
	sleep ( 2 );
	
	$msg_type = NULL;
	$msg = NULL;
}

?>