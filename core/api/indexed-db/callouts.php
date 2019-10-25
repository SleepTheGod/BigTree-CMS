<?php
	namespace BigTree;
	
	/*
	 	Function: indexed-db/callouts
			Returns an array of IndexedDB commands for either caching a new set of callout data or updating an existing data set.
		
		Method: GET
	 
		Parameters:
	 		since - An optional timestamp to return updated data since.
	 	
		Returns:
			An array of IndexedDB commands
	*/
	
	$actions = [];
	$user_level = Auth::user()->Level;
	
	if (!defined("API_SINCE") || defined("API_PERMISSIONS_CHANGED")) {
		$raw = DB::getAll("callouts");
		$callouts = [];
		
		foreach ($raw as $index => $callout) {
			$callouts[] = API::getCalloutsCacheObject($callout);
		}
		
		$actions["put"] = $callouts;
	}
	
	// No deletes in this request
	if (!defined("API_SINCE")) {
		API::sendResponse(["cache" => ["callouts" => $actions]]);
	}
	
	$deleted_records = [];
	$audit_trail_deletes = SQL::fetchAll("SELECT entry FROM bigtree_audit_trail
										  WHERE `table` = 'config:callouts' AND `date` >= ? AND `type` = 'delete'
										  ORDER BY id DESC", API_SINCE);
	
	// Run deletes first, don't want to pass creates/updates for something deleted
	foreach ($audit_trail_deletes as $item) {
		$actions["delete"][] = $item["entry"];
		$deleted_records[] = $item["entry"];
	}
	
	// If permissions changed we've already done all put statements
	if (!defined("API_PERMISSIONS_CHANGED")) {
		// Creates / updates
		$audit_trail_updates = SQL::fetchAll("SELECT DISTINCT(entry) FROM bigtree_audit_trail
											  WHERE `table` = 'config:callouts' AND `date` >= ?
												AND (`type` = 'update' OR `type` = 'add')
											  ORDER BY id DESC", API_SINCE);
		
		foreach ($audit_trail_updates as $item) {
			if (in_array($item["entry"], $deleted_records)) {
				continue;
			}
			
			$callout = DB::get("callouts", $item["entry"]);
			
			if ($callout) {
				$actions["put"][$item["entry"]] = API::getCalloutsCacheObject($callout);
			}
		}
	}
	
	API::sendResponse(["cache" => ["callouts" => $actions]]);
	