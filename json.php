<?php
function getJSON() {
	global $db;
	
	/**
	 * Gather info from db
	 */
	$state = $db->row("SELECT * FROM space_state LIMIT 1");
	$open = $state->open;
	$last_change = $state->last_update;
	
	// get checkins
	$q = $db->query("select `e`.`id` AS `id`,`e`.`mac_address` AS `mac_address`,`e`.`join_date` AS `join_date`,`e`.`part_date` AS `part_date`,`e`.`radio` AS `radio`,`e`.`ssid` AS `ssid`,`e`.`last_update` AS `last_update`,`u`.`username` AS `username`,`u`.`sex` AS `sex`,`m`.`device` AS `device`,`e`.`signal` AS `signal` from ((`wifi_event` `e` left join `user_mac_address` `m` on((`m`.`mac_address` = `e`.`mac_address`))) left join `user` `u` on((`m`.`user_id` = `u`.`id`))) WHERE username <> '' order by `e`.`join_date` DESC LIMIT 5");
	$tmp_events = array();
	
	foreach ($q as $o) {
		if ($o->part_date > 0) {
			$tmp_events[$o->part_date] = array("name"=>$o->username . "'s " . $o->device,"type"=>"check-out");	
		}
		$tmp_events[$o->join_date] = array("name"=>$o->username . "'s " . $o->device,"type"=>"check-in");
	}
	
	krsort($tmp_events);
	$count = 1;
	$events = array();
	
	foreach ($tmp_events as $t => $event) {
		$events[] = array("t"=>$t,"name"=>$event['name'],"type"=>$event['type']);
		if ($count >= 5) break;
		$count++;
	}
	
	global $_json_keymasters, $_json_feeds;
	/**
	 * Output as JSON
	 */
	$reply = array(
		'api'		=> JSON_API,
		'space'		=> JSON_SPACE,
		'logo'		=> JSON_LOGO,
		'url'		=> JSON_URL,
        'icon'      => array (
                        'open'      => JSON_ICON_OPEN,
                        'closed'    => JSON_ICON_CLOSED,
            ),
		'address' 	=> JSON_ADDRESS,
		'contact'	=> array (
						'phone'		=> JSON_PHONE,
						'irc'		=> JSON_IRC,
						'twitter'	=> JSON_TWITTER,
						'email'		=> JSON_EMAIL,
						'ml'		=> JSON_ML
			),
		'lat'		=> JSON_LAT,
		'lon'		=> JSON_LON,
		'cam'		=> array (JSON_CAM),
		'stream'	=> array ("mjpg" => JSON_STREAM),
		'open'		=> ($open == 1),
		'lastchange'=> intval($last_change),
		'events'	=> $events,
		'feeds'		=> $_json_feeds,
		'keymasters'=> $_json_keymasters
	);
	
	return json_encode($reply);
}