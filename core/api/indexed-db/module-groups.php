<?php
	namespace BigTree;
	
	/*
	 	Function: indexeddb/module-groups
			Returns an array of IndexedDB commands for either caching a new set of module groups data or updating an existing data set.
		
		Method: GET
	 
		Parameters:
	 		since - An optional timestamp to return updated data since.
	 	
		Returns:
			An array of IndexedDB commands
	*/
	
	if (empty($_GET["since"])) {
		$groups = DB::getAll("module-groups");
		
		API::sendResponse(["insert" => ["module-groups" => $groups]]);
	}
	
	$actions = [];
	$deleted_records = [];
	$created_records = [];
	$since = date("Y-m-d H:i:s", strtotime($_GET["since"]));
	
	$audit_trail_deletes = SQL::fetchAll("SELECT entry FROM bigtree_audit_trail
										  WHERE `table` = 'config:module-groups' AND `date` >= ? AND `type` = 'delete'
										  ORDER BY id DESC", $since);
	
	// Run deletes first, don't want to pass creates/updates for something deleted
	foreach ($audit_trail_deletes as $item) {
		$actions["delete"]["module-groups"][] = $item["entry"];
		$deleted_records[] = $item["entry"];
	}
	
	// Creates next, if we have the latest data we don't need to run updates on it
	$audit_trail_creates = SQL::fetchAll("SELECT entry, type FROM bigtree_audit_trail
										  WHERE `table` = 'config:module-groups' AND `date` >= ? AND `type` = 'add'
										  ORDER BY id DESC", $since);
	
	foreach ($audit_trail_creates as $item) {
		if (in_array($item["entry"], $deleted_records)) {
			continue;
		}
		
		$group = DB::get("module-groups", $item["entry"]);
		
		if ($group) {
			$actions["insert"]["module-groups"][] = $group;
			$created_records[] = $item["entry"];
		}
	}
	
	// Finally, updates, but only the latest, so a distinct ID
	$audit_trail_updates = SQL::fetchAll("SELECT DISTINCT(entry) FROM bigtree_audit_trail
										  WHERE `table` = 'config:module-groups' AND `date` >= ? AND `type` = 'update'
										  ORDER BY id DESC", $since);
	
	foreach ($audit_trail_updates as $item) {
		if (in_array($item["entry"], $deleted_records) || in_array($item["entry"], $created_records)) {
			continue;
		}
		
		$group = DB::get("module-groups", $item["entry"]);
		
		if ($group) {
			$actions["update"]["module-groups"][$item["entry"]] = $group;
		}
	}
	
	API::sendResponse($actions);
	