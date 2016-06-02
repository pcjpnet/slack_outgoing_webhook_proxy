<?php
//////////////////////////////////////////////////
//	Slack Outgoing Webhook Proxy Script	//
//						//
//		by @pcjpnet (http://pc-jp.net/)	//
//////////////////////////////////////////////////
//
// 1. put this file to the web server.
//    (require php, https access)
// 2. edit settings in this file.
// 3. set webhook
//
// ========== SETTINGS ==========
$post_url = array(
"https://****************/"
);

$save_log = "false";	//true or false
$log_mode = "false";	//single or size or all
$log_file_name = "./log.txt";
$log_max_size = 1000;	//bytes (size mode)
$display_log = "false";	//display log in browser

$web_whitelist_mode = "false";	// IP Whitelist (Web)

//IP Whitelist 
$web_whitelist = array(
"192.168.0.",
"192.168.1."
);

// ========== END ==========

$post = file_get_contents("php://input");
$ip = $_SERVER["REMOTE_ADDR"];

if ($post) {
	foreach($post_url as $url) {
		if ($url) { http_post($url, $post); }
	}

	if ($save_log == "true") {
		if ($log_mode == "single") {
			file_put_contents($log_file_name, $post);
		} elseif ($log_mode == "size") {
			if (filesize($log_file_name) > $log_max_size) {
				file_put_contents($log_file_name, $post);
			} else {
				file_put_contents($log_file_name, $post, FILE_APPEND);
			}
		} else {
			file_put_contents($log_file_name, $post, FILE_APPEND);
		}
	}

} else {
	if ($web_whitelist_mode == "true" && match_ip($ip, $web_whitelist) === false) {
		exit;
	}
	if ($_SERVER["HTTPS"]) {
		print("set Webhook:<br />\n");
		print("https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
		print("<br />\n<br />\n");
		if ($save_log == "true" && $display_log == "true") {
			print("<pre>");
			$data = file_get_contents($log_file_name);
			parse_str($data, $out);
			print_r($out);
			print("</pre>");
		} else {
			print("save_log or display_log = false");
		}
	} else {
		print("Require HTTPS");
	}
}


// POST FUNCTION
function http_post($url, $data) {
	$header = array(
		"Content-Type: application/x-www-form-urlencoded",
		"Content-Length: ".strlen($data));
	$context = array(
		"http" => array(
		"method"  => "POST",
		"header"  => implode("\r\n", $header),
		"content" => $data));
	return file_get_contents($url, false, stream_context_create($context));
}

// IP WHITELIST
function match_ip($ip, $lists) {
	foreach ($lists as $list) {
		if (strpos($ip, $list) === 0) {
			return true;
		}
	}
	return false;
}

?>

