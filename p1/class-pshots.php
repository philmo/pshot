<?php
if (! class_exists ( 'pshots' )) {
	class pshots {
		const snapshot_age = 86400;
		const disable_requeue = false;
		const location_base = '/var/www/html/static/thumbnails';
		const snapshot_default = 'http://example.com.au/pshots/default.gif';
		const queue = 21671;
		
		private $mmime = "";
		private $snapshot_url = "";
		private $snapshot_file = "";
		private $parsed_url = "";
		private $requeue = false;
		private $invalidate = false;
		private $extension = "jpg";
		private $sh = 768;
		private $sw = 1024;
		private $ct = 0;
		private $cl = 0;
		private $cw = 1024;
		private $ch = 768;
		
		function __construct() {
			ob_start ();
			$this->parsed_url = parse_url ( "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" );
			
			$array_check = explode ( '/', substr ( $this->parsed_url ['path'], 1 ) );
			
			if (isset ( $this->parsed_url ['query'] ) && $this->parsed_url ['query']) {
				if (strrpos ( $this->parsed_url ['query'], "?" )) {
					$this->parsed_url ['path'] .= "?" . substr ( $this->parsed_url ['query'], 0, strrpos ( $this->parsed_url ['query'], "?" ) );
					$this->parsed_url ['query'] = substr ( $this->parsed_url ['query'], strrpos ( $this->parsed_url ['query'], "?" ) + 1 );
				}
				if (isset ( $this->parsed_url ['query'] ) && $this->parsed_url ['query']) {
					parse_str ( $this->parsed_url ['query'], $_GET );
				}
			}
			
			if ($array_check [1] != 'p1')
				$this->my404 ();
			for($checkLoop = 2; $checkLoop < count ( $array_check ); $checkLoop ++) {
				if (false !== strpos ( urldecode ( $array_check [$checkLoop] ), '127.0.0.' ))
					$this->my404 ();
				if (false !== strpos ( urldecode ( $array_check [$checkLoop] ), '::1' ))
					$this->my404 ();
				if (false !== strpos ( urldecode ( $array_check [$checkLoop] ), 'localhost' ))
					$this->my404 ();
			}
			
			if ((isset ( $_GET ['requeue'] ) && "true" == $_GET ['requeue'])) {
				$this->requeue = true;
				$this->invalidate = true;
			}
			
			$this->snapshot_url = substr ( $this->parsed_url ['path'], 21 );
			$this->snapshot_url = preg_replace ( "#^http%3A%2F%2Fhiderefer.com%2F%3F?#", "", $this->snapshot_url );
			
			if (0 === strlen ( $this->snapshot_url ))
				$this->my404 ();
			
			$this->snapshot_url = rawurldecode ( $this->snapshot_url );
			
			if (0 === strpos ( $this->snapshot_url, "http://www.facebook.com/" )) {
				$this->snapshot_url = "https://www.facebook.com/" . substr ( $this->snapshot_url, 24, strlen ( $this->snapshot_url ) );
			}
			if (0 === strpos ( $this->snapshot_url, "http://twitter.com/" )) {
				$this->snapshot_url = "https://twitter.com/" . substr ( $this->snapshot_url, 19, strlen ( $this->snapshot_url ) );
			}
			
			$this->snapshot_file = $this->resolve_filename ( $this->snapshot_url );
		}
		function __destruct() {
			;
		}
		public function requeue_snapshot() {
			ignore_user_abort ( true );
			header ( "Connection: Close" );
			flush ();
			ob_end_flush ();
			
			$sh = 768;
			$sw = 1024;
			$ct = 0;
			$cl = 0;
			$cw = 1024;
			$ch = 768;
			
			$t_snph = (isset ( $_GET ['sh'] ) && $_GET ['sh']) ? $_GET ['sh'] : $sh;
			$t_snpw = (isset ( $_GET ['sw'] ) && $_GET ['sw']) ? $_GET ['sw'] : $sw;
			$t_clipt = (isset ( $_GET ['ct'] ) && $_GET ['ct']) ? $_GET ['ct'] : $ct;
			$t_clipl = (isset ( $_GET ['cl'] ) && $_GET ['cl']) ? $_GET ['cl'] : $cl;
			$t_clipw = (isset ( $_GET ['cw'] ) && $_GET ['cw']) ? $_GET ['cw'] : $cw;
			$t_cliph = (isset ( $_GET ['ch'] ) && $_GET ['ch']) ? $_GET ['ch'] : $ch;
			$urlkey = sha1 ( $this->snapshot_url );
			$queue = msg_get_queue ( self::queue );
			$msg_object = new stdclass ();
			$msg_object->url = $this->snapshot_url;
			$msg_object->file = $this->snapshot_file;
			$msg_object->snap_h = $t_snph;
			$msg_object->snap_w = $t_snpw;
			$msg_object->snap_top = $t_clipt;
			$msg_object->snap_left = $t_clipl;
			$msg_object->snap_clipw = $t_clipw;
			$msg_object->snap_cliph = $t_cliph;
			$msg_object->id = $urlkey;
			
			// db capture
			
			$requeue_file = $this->snapshot_file;
			
			// check only valid urls sent to queue
			if (filter_var ( $this->snapshot_url, FILTER_VALIDATE_URL ) === false)
				return false;
			$headers = @get_headers ( $this->snapshot_url );
			if (strpos ( $headers [0], '200' ) === false)
				return false;
			if (msg_send ( $queue, 1, $msg_object )) {
				// you can use the msg_stat_queue() function to see queue status
				// print_r(msg_stat_queue($queue));
				// error_log(print_r($this->parsed_url[ 'query' ], true));
				error_log ( 'success' );
			} else {
				error_log ( 'failed' );
			}
		}
		function send_snapshot() {
			$mmime = "";
			if ($this->requeue) {
				$this->send_nocache_header ();
			} else {
				$timestamp = filemtime ( $this->snapshot_file );
				header ( "Last-Modified: " . gmdate ( 'D, d M Y H:i:s', $timestamp ) . " GMT" );
				if (self::snapshot_age < (time () - $timestamp)) {
					header ( "Expires: " . gmdate ( 'D, d M Y H:i:s', time () + 600 ) . " GMT" );
					header ( "Cache-Control: public, max-age=600" );
					$this->requeue = true;
				} else {
					header ( "Expires: " . gmdate ( 'D, d M Y H:i:s', time () + 43200 ) . " GMT" );
					header ( "Cache-Control: public, max-age=43200" );
				}
			}
			$mmime = mime_content_type ( $this->snapshot_file );
			switch ($mmime) {
				case "image/png" :
					header ( "Content-Type: image/png" );
					break;
				case "image/gif" :
					header ( "Content-Type: image/gif" );
					break;
				case "image/jpeg" :
					header ( "Content-Type: image/jpeg" );
					break;
				default :
					header ( "Content-Type: image/jpeg" );
					break;
			}
			$this->image_resize_and_output ( $this->snapshot_file, $mmime );
		}
		public function must_requeue() {
			return ($this->requeue && (! self::disable_requeue));
		}
		public function have_snapshot() {
			clearstatcache ();
			return file_exists ( $this->snapshot_file );
		}
		public function send_default_image() {
			$this->requeue = true;
			$matches = array ();
			if (preg_match ( '#^http(s)?://([^/]+)/([^/]+)#i', $this->snapshot_url, $matches )) {
				$top_level_url = "http" . $matches [1] . "://" . $matches [2] . "/";
				$toplevel_snapshot_file = $this->resolve_filename ( $top_level_url );
				if (file_exists ( $toplevel_snapshot_file )) {
					$snapshot_default_url = $this->resolve_pshots_url ( $top_level_url );
					$this->my307 ( $snapshot_default_url );
					return;
				}
			}
			
			$this->my307 ( self::snapshot_default );
		}
		private function send_nocache_header() {
			header ( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
			header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
			header ( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
			header ( 'Pragma: no-cache' );
		}
		private function image_resize_and_output($image_filename, $mmine) {
			try {
				if ($image = imagecreatefromstring ( file_get_contents ( $image_filename ) )) {
					$width = imagesx ( $image );
					$height = imagesy ( $image );
					$original_aspect = $width / $height;
					// if we are not supplied with the width, use the original image's width
					$thumb_width = (isset ( $_GET ['w'] ) && $_GET ['w']) ? $_GET ['w'] : $width;
					if ($thumb_width > 1280)
						$thumb_width = 1280;
					if ($thumb_width < 20)
						$thumb_width = 20;
						// if we are not supplied with the height, calculate it from the original image aspect ratio
					$thumb_height = (isset ( $_GET ['h'] ) && $_GET ['h']) ? $_GET ['h'] : ($thumb_width / ($width / $height));
					if ($thumb_height > 960)
						$thumb_height = 960;
					if ($thumb_height < 20)
						$thumb_height = 20;
					$thumb_aspect = $thumb_width / $thumb_height;
					if (($thumb_width == $width && $thumb_height == $height)) {
						switch ($mmime) {
							case "image/png" :
								imagepng ( $image, null );
								break;
							case "image/gif" :
								imagegif ( $image, null );
								break;
							case "image/jpeg" :
								imagejpeg ( $image, null, 90 );
								break;
							default :
								imagejpeg ( $image, null, 90 );
								break;
						}
					} else {
						if ($original_aspect >= $thumb_aspect) {
							$new_height = $thumb_height;
							$new_width = $width / ($height / $thumb_height);
						} else {
							$new_width = $thumb_width;
							$new_height = $height / ($width / $thumb_width);
						}
						$thumb = imagecreatetruecolor ( $thumb_width, $thumb_height );
						$indentX = 0 - ($new_width - $thumb_width) / 2;
						imagecopyresampled ( $thumb, $image, $indentX, 0, 0, 0, $new_width, $new_height, $width, $height );
						switch ($mmime) {
							case "image/png" :
								imagepng ( $image, null );
								break;
							case "image/gif" :
								imagegif ( $image, null );
								break;
							case "image/jpeg" :
								imagejpeg ( $image, null, 90 );
								break;
							default :
								imagejpeg ( $image, null, 90 );
								break;
						}
					}
				} else {
					$this->my404 ();
				}
			} catch ( Exception $ex ) {
				error_log ( "error processing filename : " . $image_filename );
				$this->my404 ();
			}
		}
		private function my404() {
			header ( "Content-Type: text/plain" );
			header ( "HTTP/1.1 404 Not Found" );
			header ( "Last-Modified: " . gmdate ( 'D, d M Y H:i:s', 1 ) . " GMT" );
			header ( "Expires: " . gmdate ( 'D, d M Y H:i:s', 0 ) . " GMT" );
			die ( "HTTP/1.1 404 Not Found" );
		}
		private function my307($redirect_url) {
			header ( "HTTP/1.1 307 Temporary Redirect" );
			header ( "Last-Modified, 01 Jan 2013 01:00:00 GMT" );
			header ( "Expires: " . gmdate ( 'D, d M Y H:i:s' ) . " GMT" );
			header ( "Cache-Control: no-cache, no-store, must-revalidate, max-age=0, pre-check=1, post-check=2" );
			header ( "Pragma: no-cache" );
			header ( "Location: " . $redirect_url );
			header ( "Content-Type: text/html; charset=UTF-8" );
		}
		private function resolve_filename($snap_url) {
			$extension = "jpg";
			$url = @parse_url ( $snap_url );
			$host = sha1 ( strtolower ( $url ['host'] ) );
			$file = md5 ( $snap_url );
			$ext = (isset ( $_GET ['ext'] ) && $_GET ['ext']) ? $_GET ['ext'] : $extension;
			$fullpath = self::location_base . "/" . substr ( $host, 0, 3 ) . "/" . $host . "/" . $file . "." . $ext;
			return $fullpath;
		}
		private function resolve_pshots_url($url) {
			return sprintf ( "/pshots/p1/%s", rawurlencode ( $url ) );
		}
	}
}
?>