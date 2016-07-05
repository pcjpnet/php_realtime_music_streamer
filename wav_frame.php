<?php

$filename = '/share/Music/CUE/'.urldecode($_GET['f']);

if (strtolower(substr($filename, -4)) != '.wav') {
	echo 'not wav file';
	die();
}

// READ WAVE FILE
$fp = fopen($filename, 'r');

// READ RIFF HEADER
$riff_head = fread($fp, 4);
$riff_size = array_pop(unpack('V',fread($fp, 4)));
$wave_head = fread($fp, 4);

if ($riff_head != 'RIFF' || $wave_head != 'WAVE') {
	fclose($fp);
	echo 'not suppprt format';
	die();
}

// READ FORMAT CHUNK
$fmt_head = fread($fp, 4);
$fmt_size = array_pop(unpack('V',fread($fp, 4)));
$fmt_format_id = array_pop(unpack('v',fread($fp, 2)));
$fmt_channel = array_pop(unpack('v',fread($fp, 2)));
$fmt_sample = array_pop(unpack('V',fread($fp, 4)));
$fmt_byte_sec = array_pop(unpack('V',fread($fp, 4)));
$fmt_block_size = array_pop(unpack('v',fread($fp, 2)));
$fmt_bit_sample = array_pop(unpack('v',fread($fp, 2)));

// READ DATA CHUNK HEADER
$data_head = fread($fp, 4);
$data_size = array_pop(unpack('V',fread($fp, 4)));
$head_size = ftell($fp);

if ($fmt_head != 'fmt ' || $data_head != 'data' || $fmt_format_id != 1) {
	fclose($fp);
	echo 'not suppprt format';
	die();
}

// CALC OFFSET
$max_frame = $data_size / $fmt_block_size / 588;

$data = array("max" => $max_frame);

header('Content-type:application/json; charset=utf8');
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);


?>
