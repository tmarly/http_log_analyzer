<?php

function get_suspicious_ua_list($list, $nb_max) {
	$evil = array();

	// get data from cache
	$ua_filename = "lib/ua.list";
    $known_ua = array();
	if (file_exists($ua_filename)) {
		$fp = fopen($ua_filename, "r");
        while ($ua = fgets($fp)) {
            $ua = trim($ua); // remove line endings
            $known_ua[$ua] = 1;
        }
		fclose($fp);
	}


	foreach($list as $ua => $count) {
		// In cache ?
		if (!isset($known_ua[$ua])) {
            $evil[$ua] = $count;
		}

		if (count($evil) >= $nb_max) {
			break;
		}
	}

	return $evil;
}