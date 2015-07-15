<?php
function pshot_shortcode($atts, $content = null) {
	$width = intval ( $atts ['width'] );
	$sh = intval ( $atts ['sh'] );
	$sw = intval ( $atts ['sw'] );
	$ct = intval ( $atts ['ct'] );
	$cl = intval ( $atts ['cl'] );
	$cw = intval ( $atts ['cw'] );
	$ch = intval ( $atts ['ch'] );
	$ext = ($atts ['ext']);
	// $width = (100 <= $width && $width <= 300) ? $width : 200;
	$site = trim ( $atts ['site'] );
	if ($site != '') {
		$query_url = 'http://example.com.au/pshots/p1/index.php?' . urlencode ( $site ) . '?w=' . $width . '&sh=' . $sh . '&sw=' . $sw . '&ct=' . $ct . '&cl=' . $cl . '&cw=' . $cw . '&ch=' . $ch . '&ext=' . $ext;
		$image_tag = '<img class="din_screenshot_img" alt="' . $site . '" width="' . $width . '" src="' . $query_url . '" />';
		$res = '<a class="din_screenshot_link" href = "' . $site . '" target="_blank">' . $image_tag . '</a>';
	} else {
		$res = 'Bad screenshot url!';
	}
	return $res;
}
add_shortcode ( 'pshot', 'pshot_shortcode' );




