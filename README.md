# php_realtime_music_streamer
Stream Cue+Wav File  

require lame  


# mp3enc.php  
wav to mp3  
s = start frame  
e = end frame  
f = wav file name  

(1seconds = 75frame)  

  
# wav.php  
wav to wav (split)  
s = start frame  
e = end frame  
f = wav file name  

(1seconds = 75frame)  

  
# cue.lib.php  
cue file read library  
  
example:  
  
    <?php
    	require_once('./cue.lib.php');
    	$cue_filename = '/share/music.cue';
    	$cue = cue_read($cue_filename);
    	print_r($cue);
    ?>
  


