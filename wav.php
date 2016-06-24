<?php

$filename = '/share/Music/'.urldecode($_GET['f']);

if (!isset($_GET['s']) || $_GET['s'] === '') {
    $start_frame = 0;
} else {
	$start_frame = $_GET['s'];
}

if (!isset($_GET['e']) || $_GET['e'] === '') {
    $end_frame = -1;
} else {
	$end_frame = $_GET['e'];
}

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

if ($start_frame < 0) {
	$start_frame = 0;
}
if ($end_frame <= 0 || $end_frame > $max_frame) {
	$end_frame = $max_frame;
}
if ($start_frame > $end_frame) {
	$start_frame = 0;
}

$start_byte = ($start_frame * $fmt_block_size * 588) + $head_size;
$end_byte = ($end_frame * $fmt_block_size * 588) + $head_size;
$byte_size = $end_byte - $start_byte;

fseek($fp, $start_byte);

// OUTPUT WAV FILE
$out_head = 'RIFF';
$out_head .= pack("V", $byte_size + 36);
$out_head .= 'WAVEfmt ';
$out_head .= pack("V", $fmt_size);
$out_head .= pack("v", $fmt_format_id);
$out_head .= pack("v", $fmt_channel);
$out_head .= pack("V", $fmt_sample);
$out_head .= pack("V", $fmt_byte_sec);
$out_head .= pack("v", $fmt_block_size);
$out_head .= pack("v", $fmt_bit_sample);
$out_head .= 'data';
$out_head .= pack("V", $byte_size);

// Wav output
header('Cache-Control: no-cache');
header('Content-length: '.($byte_size + 44));
header('Content-type: audio/x-wav');

echo $out_head;

while (1) {
	if (ftell($fp) < $end_byte) {
		$wav = fread($fp, 588);
		echo $wav;
	} else {
		break;
	}
}

fclose($fp);

?>
