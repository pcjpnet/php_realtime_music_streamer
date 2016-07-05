<?php

function cue_read($cue_filename) {

// File Check
if (!file_exists($cue_filename)) {
	//print('file exists');
	//die();
	return false;
}

if (!is_readable($cue_filename)) {
	//print('file not readable');
	//die();
	return false;
}

// Read Cue Sheet
$in_enc = 'ASCII,JIS,UTF-8,CP932,SJIS,SJIS-win,EUC-JP,eucJP-win,ISO-8859-1';
$out_enc = 'UTF-8';
$cue_sheet = file($cue_filename);		// Cue Sheet Array
$quotes = "\"'`";						// Remove Quotes
$track = 0;								// Current Track
$cue_rem = '';							// Comment
$cue_track_rem = array();				// Track Comment
$cue_cdtextfile = '';					// CD-TEXT File
$cue_catalog = '';						// Catalog Number
$cue_track_isrc = array();				// Track ISRC Number
$cue_title = '';						// Title
$cue_track_title = array();				// Track Title
$cue_performer = '';					// Performer
$cue_track_performer = array();			// Track Performer
$cue_songwriter = '';					// Songwriter
$cue_track_songwriter = array();		// Track Songwriter
$cue_file = '';							// File Path (Ex: ********.wav)
$cue_file_type = '';					// File Type (WAVE/BINARY/MP3/AIFF/MOTOROLA)
$cue_track_file = array();				// Track File Path (Ex: ********.wav)
$cue_track_file_type = array();			// Track File Type (WAVE/BINARY/MP3/AIFF/MOTOROLA)
$cue_track_index_0 = array();			// Track Index0 Timestamp
$cue_track_index_1 = array();			// Track Index1 Timestamp
$cue_track_index_0_frame = array();		// Track Index0 Timestamp (Frame Number)
$cue_track_index_1_frame = array();		// Track Index1 Timestamp (Frame Number)
$cue_track_pregap = array();			// Track Pregap Timestamp
$cue_track_postgap = array();			// Track Postgap Timestamp
$cue_track_pregap_frame = array();		// Track Pregap Timestamp (Frame Number)
$cue_track_postgap_frame = array();		// Track Postgap Timestamp (Frame Number)
$cue_track_flags = array();				// Track Flags (DCP/4CH/PRE/SCMS)


// Loop
foreach ($cue_sheet as $line) {

// Encoding
$line = mb_convert_encoding($line, $out_enc, $in_enc);

$cmd = explode(" ", trim($line), 2);

// Start Switch Case
switch (strtoupper(trim($cmd[0]))) {

case 'REM':
	if ($track == 0) {
		// Comment
		if ($cue_rem) {
			$cue_rem .= "\n";
		}
		$cue_rem .= trim($cmd[1]);
	} else {
		// Track Comment
		if ($cue_track_rem[$track - 1]) {
			$cue_track_rem[$track - 1] .= "\n";
		}
		$cue_track_rem[$track - 1] .= trim($cmd[1]);
	}
	break;

case 'CDTEXTFILE':
	if ($track == 0) {
		$cue_cdtextfile = trim(trim($cmd[1]), $quotes);
	}
	break;

case 'CATALOG':
	if ($track == 0) {
		$cue_catalog = trim(trim($cmd[1]), $quotes);
	}
	break;

case 'ISRC':
	if ($track != 0) {
		$cue_track_isrc[$track - 1] = trim(trim($cmd[1]), $quotes);
	}
	break;

case 'TITLE':
	if ($track == 0) {
		$cue_title = trim(trim($cmd[1]), $quotes);
	} else {
		$cue_track_title[$track - 1] = trim(trim($cmd[1]), $quotes);
	}
	break;

case 'PERFORMER':
	if ($track == 0) {
		$cue_performer = trim(trim($cmd[1]), $quotes);
	} else {
		$cue_track_performer[$track - 1] = trim(trim($cmd[1]), $quotes);
	}
	break;

case 'SONGWRITER':
	if ($track == 0) {
		$cue_songwriter = trim(trim($cmd[1]), $quotes);
	} else {
		$cue_track_songwriter[$track - 1] = trim(trim($cmd[1]), $quotes);
	}
	break;

case 'FILE':
	//$param = explode(" ", trim($cmd[1]), 2);
	$param = array_map('strrev', explode(' ', strrev(trim($cmd[1])), 2));
	if ($track == 0) {
		$cue_file = trim(trim($param[1]), $quotes);
		$cue_file_type = trim($param[0]);
	} else {
		$cue_track_file[$track - 1] = trim(trim($param[1]), $quotes);
		$cue_track_file_type[$track - 1] = trim($param[0]);
	}
	break;

case 'TRACK':
	$param = explode(" ", trim($cmd[1]), 2);
	if (strtoupper(trim($param[1])) == 'AUDIO') {
		$track = intval(trim($param[0]));
	}
	break;

case 'INDEX':
	$param = explode(" ", trim($cmd[1]), 2);
	switch (trim($param[0])) {
		case '00':
			$cue_track_index_0[$track - 1] = trim($param[1]);
			$cue_track_index_0_frame[$track - 1] = time_to_frame($param[1]);
			break;
		case '01':
			$cue_track_index_1[$track - 1] = trim($param[1]);
			$cue_track_index_1_frame[$track - 1] = time_to_frame($param[1]);
			break;
	}
	break;

case 'PREGAP':
	$cue_track_pregap[$track - 1] = trim($cmd[1]);
	$cue_track_pregap_frame[$track - 1] = time_to_frame($cmd[1]);
	break;

case 'POSTGAP':
	$cue_track_postgap[$track - 1] = trim($cmd[1]);
	$cue_track_postgap_frame[$track - 1] = time_to_frame($cmd[1]);
	break;

case 'FLAGS':
	$cue_track_flags[$track - 1] = trim($cmd[1]);
	break;

}
// End Switch

}
// End Loop

// Return Array
$cue = array(
			'track'			=>	$track,
			'rem'			=>	$cue_rem,
			'cdtext'		=>	$cue_cdtextfile,
			'catalog'		=>	$cue_catalog,
			'title'			=>	$cue_title,
			'performer'		=>	$cue_performer,
			'songwriter'	=>	$cue_songwriter,
			'file'			=>	$cue_file,
			'filetype'		=>	$cue_file_type,
			'trk_rem'			=>	$cue_track_rem,
			'trk_isrc'			=>	$cue_track_isrc,
			'trk_title'			=>	$cue_track_title,
			'trk_performer'		=>	$cue_track_performer,
			'trk_songwriter'	=>	$cue_track_songwriter,
			'trk_file'			=>	$cue_track_file,
			'trk_filetype'		=>	$cue_track_file_type,
			'trk_index0'		=>	$cue_track_index_0,
			'trk_index1'		=>	$cue_track_index_1,
			'trk_index0_frm'	=>	$cue_track_index_0_frame,
			'trk_index1_frm'	=>	$cue_track_index_1_frame,
			'trk_pregap'		=>	$cue_track_pregap,
			'trk_postgap'		=>	$cue_track_postgap,
			'trk_pregap_frm'	=>	$cue_track_pregap_frame,
			'trk_postgap_frm'	=>	$cue_track_postgap_frame,
			'trk_flags'			=>	$cue_track_flags
		);

	return $cue;

}


function time_to_frame($str) {
	if (!isset($str) || $str === '') {
		return false;
	} else {
		$time = explode(":", trim($str), 3);
		$frame = intval(trim($time[2]));				//Frame
		$frame += intval(trim($time[1])) * 75;			//Second -> Frame
		$frame += intval(trim($time[0])) * 60 * 75;		//Minute -> Frame
		return $frame;
	}
}

?>
