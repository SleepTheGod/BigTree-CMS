<?php
	namespace BigTree;

	// Simple CSRF checker
	if (count($_POST)) {
		$clean_referer = str_replace(["http://", "https://"], "//", $_SERVER["HTTP_REFERER"]);
		$clean_admin_root = str_replace(["http://", "https://"], "//", ADMIN_ROOT)."developer/";

		// The referer MUST contain a URL from within the developer section to post to it.
		if (strpos($clean_referer,$clean_admin_root) === false) {
			die();
		}
	}

	$developer_root = ADMIN_ROOT."developer/";
	define("DEVELOPER_ROOT", $developer_root);

	Auth::user()->requireLevel(2);
?>
<div class="developer">