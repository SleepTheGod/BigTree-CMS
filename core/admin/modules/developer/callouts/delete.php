<?php
	namespace BigTree;
	
	/**
	 * @global array $bigtree
	 */
	
	CSRF::verify();

	$callout = new Callout($_GET["id"]);
	$callout->delete();
	
	Utils::growl("Developer","Deleted Callout");
	Router::redirect(DEVELOPER_ROOT."callouts/");
	