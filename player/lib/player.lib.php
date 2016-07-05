<?php

function json_safe_encode($data){
	return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function list_folder($path) {
	$dir = scandir($path);
	$items = array_diff($dir, array('.', '..'));
	foreach($items as $item) {
		if (is_dir($path.DIRECTORY_SEPARATOR.$item)) {
			$dir_list[] = $item;
		}
	}
	return $dir_list;
}

function all_file_list($path) {
	static $files = array();
	$dir = scandir($path);
	$items = array_diff($dir, array('.', '..'));
	foreach($items as $item) {
		if (is_dir($path.DIRECTORY_SEPARATOR.$item)) {
			all_file_list($path.DIRECTORY_SEPARATOR.$item);
		} elseif (is_file($path.DIRECTORY_SEPARATOR.$item)) {
			if (strtolower(substr($item, -4)) == '.cue') {
				if (strtolower(substr($item, -10)) != '_utf-8.cue') {
					$files[] = $path.DIRECTORY_SEPARATOR.$item;
				}
			}
		}
	}
	return $files;
}


?>
