<?php

$filename = '/share/Music/'.urldecode($_GET["f"]);
$start_frame = $_GET["s"];
$end_frame = $_GET["e"];
$mp3_bitrate = 192;

if (strtolower(substr($filename, -4)) != ".wav") {
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
if ($end_frame < 0) {
	$end_frame = 0;
}
if ($end_frame > $max_frame) {
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
$out_head .= pack("V", 16);
$out_head .= pack("v", 1);
$out_head .= pack("v", 2);
$out_head .= pack("V", 44100);
$out_head .= pack("V", 176400);
$out_head .= pack("v", 4);
$out_head .= pack("v", 16);
$out_head .= 'data';
$out_head .= pack("V", $byte_size);

// Wav output
//header('Content-type: audio/x-wav');
//echo


// MP3 output

$descriptorspec = array(
	0 => array( "pipe", "r" ),
	1 => array( "pipe", "w" ),
	2 => array( "file", "/dev/null", "w" )
);

$process = proc_open( "/opt/bin/lame -b ".$mp3_bitrate." - -", $descriptorspec, $pipes );
stream_set_blocking($pipes[1], 0);


header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' );

// HTTP/1.1
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', FALSE );

// HTTP/1.0
header( 'Pragma: no-cache' );

header('Content-Type: audio/mpeg');

// Wav Header > lame
$wav = $out_head;
fwrite( $pipes[0], $wav );

while (!feof($pipes[1])) {
	if (ftell($fp) < $end_byte) {
		$wav = fread($fp, 588);
		fwrite( $pipes[0], $wav );
	} else {
		break;
	}
	
	$mp3 = fread( $pipes[1] , 1000);
	if ($mp3 !== false && !empty($mp3)){
            echo $mp3;
	}

}

fclose( $pipes[0] );
fclose( $pipes[1] );
proc_close( $process );


?>