<?php
	require_once('./lib/player.lib.php');
	require_once('./lib/cue.lib.php');
	$cue_folder = "/share/Music/CUE/";
	$cue_folder_list = list_folder($cue_folder);
	
	// JSON
	if (isset($_GET['mode']) && $_GET['mode'] == 'json') {
		header('Content-type:application/json; charset=utf8');
		
		// GET FILE LIST
		if (isset($_GET['dir']) && !isset($_GET['file'])) {
			// echo $cue_folder_list[$_GET['dir']];
			$file_list = all_file_list($cue_folder.$cue_folder_list[$_GET['dir']]);
			$str_cnt = strlen($cue_folder.$cue_folder_list[$_GET['dir']]);
			foreach($file_list as $item) {
				$short_file_list[] = substr($item, $str_cnt + 1);
			}
			echo json_safe_encode($short_file_list);
		
		// GET CUE FILE
		} elseif(isset($_GET['dir']) && isset($_GET['file'])) {
			$cue_file = cue_read($cue_folder.$cue_folder_list[$_GET['dir']].DIRECTORY_SEPARATOR.$_GET['file']);
			echo json_safe_encode($cue_file);
			
		}
		die();
	}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MusicPlayer</title>
<link href="/skin/css/bootstrap.min.css" rel="stylesheet">
<link href="./lib/slider-vol.css" rel="stylesheet">
<link href="./lib/slider-pos.css" rel="stylesheet">
<script src="/skin/js/jquery.min.js"></script>
<script>
<!--
$(document).ready(function(){
	
	
	// ========== MAIN ========== //
	
	// CURRENT FILE STATUS
	//var audio = new Audio();
	var dir_id;
	//var cue_audio_file = "";
	//var cue_title = "";
	var frame_start = "";
	var frame_end = "";
	
	var play_index = 0;
	var music_list = [];
	var music_index = [];
	
	var play_queue = [];
	var played_queue = [];
	
	var mp3 = "./lib/cue_mp3enc.php?";
	var wav = "./lib/cue_wav.php?";
	
	// CUE FOLDER LIST ARRAY (JSON)
	var cue_folder_list = <?php echo json_safe_encode($cue_folder_list); ?>;
	
	// SHOW MAIN FRAME
	//document.getElementById("frame").style.display="block";
	$("#frame").show();
	
	// SHOW CUE FOLDER LIST
	for(var i in cue_folder_list){
		$("#folder_list_ul").append("<li class=\"select_folder_li\"><a href=\"javascript:void(0)\" class=\"select_folder_a\" id=\"dir_" + i + "\">" + cue_folder_list[i] + "</a></li>");
	}
	
	// READ GET PARAMETER
	var get = [];
	get = read_get_param();
	
	dir_id = get["dir"];
	
	// SET STATE
	set_state(get);
	
	
	// ========== MAIN ========== //
	
	
	// ========== FUNCTIONS ========== //
	
	// ISSET
	function isset(data){
		if (data === "" || data === null || data === undefined) {
			return false;
		}else{
			return true;
		}
	}
	
	
	// ESCAPE HTML
	function escape_html(content) {
		var TABLE_FOR_ESCAPE_HTML = {
			"&": "&amp;",
			"\"": "&quot;",
			"<": "&lt;",
			">": "&gt;"
		};
		return content.replace(/[&"<>]/g, function(match) {
			return TABLE_FOR_ESCAPE_HTML[match];
		});
	}
	
	
	// READ GET PARAM
	function read_get_param() {
		var get = [];
		if (window.location.search.length > 1) {
			var query = window.location.search.substring(1);
			var args = query.split("&");
			for (i = 0; i < args.length; i++) {
				param = args[i].split("=");
				get[decodeURIComponent(param[0])] = decodeURIComponent(param[1]);
			}
			//console.log(get);
			return get;
		} else {
			return false;
		}
	}
	
	
	// SET STATE
	function set_state(get) {
		if (!isset(get["dir"])) {
		// !DIR
			if (!isset(get["ord"])) {
				// SHOW TOP PAGE
				show_top_page();
			} else {
				// PLAY ALL ALBUMS
				
			}
		} else {
		// DIR
			if (isset(get["file"])) {
				console.log("#SET FILE: " + get["file"]);
				// PLAY FILE
				show_cue_file(get["dir"], get["file"]);
			
			} else if (isset(get["list"])) {
				console.log("#SET LIST: " + get["list"]);
				// PLAY PLAYLIST
			
			} else if (isset(get["ord"])) {
				console.log("#SET SORT: " + get["ord"]);
				// PLAY DIRECTORY
			
			} else {
				// SHOW DIRECTORY
				show_directory(get["dir"]);
				console.log("#SHOW DIR");
			
			}
		}
	}
	
	
	// SHOW TOP PAGE
	function show_top_page() {
		$("#music_list").hide();
		$("#file_list").hide();
		$("#top_page").show();
	}
	
	
	// SHOW DIRECTORY
	function show_directory(id) {
		$("#music_list").hide();
		$("#file_list").show();
		$("#top_page").hide();
		
		$("#file_list_header").text("ライブラリ / " + cue_folder_list[dir_id]);
		
		$.getJSON("./", {mode:"json", dir:id}, function(json){
			$("#file_list_ul").empty();
			for(var i in json){
				$("#file_list_ul").append("<li class=\"select_file_li lst\"><a href=\"javascript:void(0)\" class=\"select_file_a\" id=\"" + escape_html(json[i]) + "\">" + escape_html(json[i]) + "</a></li>");
			}
		});
	}
	
	
	// SHOW CUE FILE
	function show_cue_file(dir, file) {
		$("#music_list").show();
		$("#file_list").hide();
		$("#top_page").hide();
		play_index = 0;
		music_list = [];
		music_index = [];
		play_queue = [];
		played_queue = [];
		read_cue_file(dir, file);
	}
	
	
	// SHOW MUSIC LIST
	function show_music_list() {
		$("#music_list").show();
		$("#file_list").hide();
		$("#top_page").hide();
		$("#music_list_header").text("PLAYING: " + music_list[play_index][1]);
		$("#music_list_ul").empty();
		for (var i = 0; i < music_list.length; i++){
			
			$("#music_list_ul").append("<li class=\"select_music_li lst\" id=\"list_" + i + "\"><a href=\"javascript:void(0)\" class=\"select_music_a\" id=\"" + escape_html(music_list[i][2]) + "\">" + escape_html(music_list[i][2]) + "</a></li>");
			
		}
	}
	
	// SET PLAY QUEUE
	function read_cue_file(dir, file){
		$.getJSON("./", {mode:"json", dir:dir, file:file}, function(cue){
			for (var i = 0; i < cue["track"]; i++){
				var queue = [];
				queue.push(cue_folder_list[dir] + "/" + file.substr(0, file.lastIndexOf("/") + 1) + cue['file']);	// [0] AUDIO FILE PATH
				queue.push(cue["title"]);						// [1] TITLE
				queue.push(cue["trk_title"][i]);				// [2] TRACK TITLE
				if (isset(cue["trk_performer"][i])) {			// [3] TRACK PERFORMER
					queue.push(cue["trk_performer"][i]);
				} else {
					queue.push(cue["performer"]);
				}
				queue.push(Number(cue["trk_index1_frm"][i]));	// [4] TRACK START FRAME
				if (i + 1 != cue['track']) {					// [5] TRACK END FRAME
					queue.push(Number(cue["trk_index1_frm"][i + 1]) - 1);
				}
				music_list.push(queue);
			}
			
			// GET MAX FRAME
			$.getJSON("./lib/wav_frame.php", {f:cue_folder_list[dir] + "/" + file.substr(0, file.lastIndexOf("/") + 1) + cue['file']}, function(frame){
				music_list[music_list.length - 1][5] = frame["max"];
				console.log("maxframe: " + frame["max"]);
				
				console.log(cue);
				console.log(music_list);
				
				show_music_list();
				
				play_music();
			});
			
			// SET GLOBAL
			//cue_audio_file = cue_folder_list[dir] + "/" + file.substr(0, file.lastIndexOf("/") + 1) + cue['file'];
			//cue_title = cue['title'];
			//console.log(cue_audio_file);
			
		});
		return false;
	}
	
	
	function play_music() {
		
		if (escape_html(music_list[play_index][2]).length > 50) {
			$("#ctl_label_title").html("<marquee scrollamount=\"2\" scrolldelay=\"100\" style=\"width:400px;\">     " + escape_html(music_list[play_index][2]) + "     </marquee>");
		} else {
			$("#ctl_label_title").text(escape_html(music_list[play_index][2]));
		}
		
		if (escape_html(music_list[play_index][1]).length > 50) {
			$("#ctl_label_album").html("<marquee scrollamount=\"2\" scrolldelay=\"100\" style=\"width:400px;\">     " + escape_html(music_list[play_index][1]) + "     </marquee>");
		} else {
			$("#ctl_label_album").text(escape_html(music_list[play_index][1]));
		}
		
		$("#ctl_pos_sld").attr("max", (music_list[play_index][5] - music_list[play_index][4]) / 75);
		$("#ctl_pos_sld").val(0);
		
		play_file(music_list[play_index][4], music_list[play_index][5], music_list[play_index][0]);
		
		show_music_icon();
		
	}
	
	function play_file(start, end, file) {
		frame_start = start;
		frame_end = end;
		$("#main_audio").attr("src", mp3 + "s=" + start + "&e=" + end + "&f=" + encodeURIComponent(file));
		$("#main_audio").trigger("play");
		//audio.src = mp3 + "s=" + start + "&e=" + end + "&f=" + encodeURIComponent(file);
		//audio.play();
		//console.log(end / 75);
	}
	
	
	function show_music_icon() {
		$(".select_music_li").css("list-style-image", "url('./lib/icons/music.png')");
		if (!$("#main_audio")[0].paused) {
			// PLAYING
			$("#list_" + play_index).css("list-style-image", "url('./lib/icons/control.png')");
		}
	}
	
	// RETURN H:M:S FORMAT SECONDS
	function format_seconds(sec){
		h=""+(sec/36000|0)+(sec/3600%10|0)
		m=""+(sec%3600/600|0)+(sec%3600/60%10|0)
		s=""+(sec%60/10|0)+(sec%60%10)
		return h+":"+m+":"+s
	}
	
	
	
	
	// ========== FUNCTIONS ========== //
	
	
	// ========== EVENTS ========== //
	
	// BROWSER MOVE EVENT
	$(window).on("popstate", function(jqevent) {
		// READ GET PARAMETER
		var get = [];
		get = read_get_param();
		
		// SET STATE
		set_state(get);
		console.log("move event");
		console.log(get);
	});
	
	
	// DIRECTORY CLICK EVENT
	$(document).on("click",".select_folder_a",function(event){
		event.preventDefault();
		event.stopPropagation();
		dir_id = $(this).attr("id").split("_")[1];
		//history.replaceState(null, null, "?dir=" + dir_id);
		history.pushState(null, null, "?dir=" + dir_id);
		show_directory(dir_id);
		return false;
		
	});
	
	
	// CUE FILE CLICK EVENT
	$(document).on("click",".select_file_a",function(event){
		event.preventDefault();
		event.stopPropagation();
		//history.replaceState(null, null, "?dir=" + dir_id + "&file=" + $(this).attr("id"));
		history.pushState(null, null, "?dir=" + dir_id + "&file=" + $(this).attr("id"));
		show_cue_file(dir_id, $(this).attr('id'));
		return false;
	});
	
	
	// MUSIC LIST CLICK EVENT
	$(document).on("click",".select_music_a",function(event){
		event.preventDefault();
		event.stopPropagation();
		play_index = $(this).parents(".select_music_li").attr("id").split("_")[1];
		
		play_music();
		
		return false;
	});
	
	
	// CLICK CONTROL BOX
	$(document).on("click","#control_mid",function(event){
		event.preventDefault();
		event.stopPropagation();
		
		show_music_list();
		show_music_icon();
		
		return false;
	});
	
	
	// PLAY INFO UPDATE
	$("#main_audio").bind("timeupdate", function() {
		//$("#ctl_pos_sld").attr("max", 5000 / 75);
		var cur_time = 0;
		if ($(this)[0].currentTime > (frame_end - frame_start) / 75) {
			cur_time = (frame_end - frame_start) / 75;
		} else {
			cur_time = $(this)[0].currentTime;
		}
		$("#ctl_pos_sld").val(cur_time);
		$("#ctl_label_time").text(format_seconds(Math.floor(cur_time)));
		$("#ctl_label_endtime").text("-" + format_seconds(Math.floor(((frame_end - frame_start) / 75) - Math.floor(cur_time))));
		
		if ($("#main_audio")[0].paused) {
			// PAUSE
			$("#ctl_btn_play").children("img").attr("src","./lib/icons/appbar.control.play.png");
		} else {
			// PLAYING
			$("#ctl_btn_play").children("img").attr("src","./lib/icons/appbar.control.pause.png");
		}
	});
	
	
	
	$("#main_audio").on("playing", function() {
		
		show_music_icon();
		//alert("play");
	});
	
	
	
	
	$("#main_audio").on("ended", function() {
		
		show_music_icon();
		
		$("#ctl_btn_next").trigger("click");
		
	});
	
	
	$("#main_audio").on("loadstart", function() {
		$("#footer_right_status").text("downloading");
	});
	
	
	$("#main_audio").on("loadeddata", function() {
		$("#footer_right_status").text("end");
	});
	
	$("#main_audio").on("progress", function() {
		$("#footer_right_status").text("progress");
	});
	
	
	
	// CHANGE VOLUME SLIDER
	$("#ctl_vol_sld").on("input change", function(){
		$("#main_audio")[0].volume = $(this).val();
		//audio.volume = $(this).val();
	});
	
	// CHANGE POSITION SLIDER
	$("#ctl_pos_sld").on("input change", function(){
		console.log($("#main_audio")[0].networkState);
		$("#main_audio")[0].currentTime = $(this).val();
		//audio.currentTime = $(this).val();
		//console.log($(this).val());
	});
	
	
	// CLICK PLAY/PAUSE BUTTON
	$(document).on("click","#ctl_btn_play",function(event){
		event.preventDefault();
		event.stopPropagation();
		
		if ($("#main_audio")[0].paused) {
			// PLAY TRIGGER
			$("#ctl_btn_play").children("img").attr("src","./lib/icons/appbar.control.pause.png");
			$("#main_audio")[0].play();
			show_music_icon();
		} else {
			// PAUSE TRIGGER
			$("#ctl_btn_play").children("img").attr("src","./lib/icons/appbar.control.play.png");
			$("#main_audio")[0].pause();
			show_music_icon();
		}
		
		return false;
	});
	
	
	// CLICK PREV BUTTON
	$(document).on("click","#ctl_btn_prev",function(event){
		event.preventDefault();
		event.stopPropagation();
		
		if (play_index > 0) {
			play_index--;
		}
		
		play_music();
		
		return false;
	});
	
	
	// CLICK NEXT BUTTON
	$(document).on("click","#ctl_btn_next",function(event){
		event.preventDefault();
		event.stopPropagation();
		
		if (play_index < music_list.length - 1) {
			play_index++;
		}
		
		play_music();
		
		return false;
	});
	
	
	
	// ========== EVENTS ========== //
	
});
// -->
</script>
<noscript>
	<p>このページではJavaScriptを使用しています。</p>
</noscript>
<style>
.row-fluid{
	display: -webkit-box;
	display: -webkit-flex;
	display: -ms-flexbox;
	display: flex;
}
.ctrl-box{
	/* box-shadow */
	box-shadow:0px 0px 6px 2px #819476 inset;
	-moz-box-shadow:0px 0px 6px 2px #819476 inset;
	-webkit-box-shadow:0px 0px 6px 2px #819476 inset;

	/* border-radius */
	border-radius:6px;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;

	/* border */
	border:1px solid #97978b;
}
.center {
	display: -webkit-flex;
	display: flex;
	-webkit-align-items: center;
	align-items: center;
	-webkit-justify-content: center;
	justify-content: center;
}
.frame-box{
	/* box-shadow */
	box-shadow:0px 0px 6px 2px #819476;
	-moz-box-shadow:0px 0px 6px 2px #819476;
	-webkit-box-shadow:0px 0px 6px 2px #819476;
	/* border-radius */
	border-radius:6px;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	/* border */
	border:1px solid #6c6c6a;
}
li.lst:nth-child(odd){
	background:#f0f5fb;
}

.txt-overflow{
	width:250px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.btn_main {
	width: 40px;
	height: 40px;
	line-height: 40px;
	background: #FFF;
	background: -moz-linear-gradient(top,#FFF 0%,#DDD);
	background: -webkit-gradient(linear, left top, left bottom, from(#FFF), to(#DDD));
	border: 1px solid #2a82a3;
	border-radius: 20px;
	-moz-border-radius: 20px;
	-webkit-border-radius: 20px;
	color: #2a82a3;
	text-align: center;
	font-weight: bold;
	font-size: 120%;
	transition: background-color 0.5s ease-in;
	-webkit-transition: background-color 0.5s ease-in;
}
.btn_sub {
	width: 30px;
	height: 30px;
	line-height: 30px;
	background: #FFF;
	background: -moz-linear-gradient(top,#FFF 0%,#DDD);
	background: -webkit-gradient(linear, left top, left bottom, from(#FFF), to(#DDD));
	border: 1px solid #2a82a3;
	border-radius: 15px;
	-moz-border-radius: 15px;
	-webkit-border-radius: 15px;
	color: #2a82a3;
	text-align: center;
	font-weight: bold;
	font-size: 120%;
	transition: background-color 0.5s ease-in;
	-webkit-transition: background-color 0.5s ease-in;
}
</style>
</head>
<body>
<div id="frame" class="container-fluid" style="display:none; margin:20px 0px;">	<!-- FRAME -->
<div class="frame-box"><!-- SUBFRAME -->
<div id="top" style="background:linear-gradient(#c4c4c2, #959595); margin:0px;padding:0px;">			<!-- TOP -->
<div id="header" class="row-fluid" style="height:30px;">						<!-- HEADER -->

<div id="header_left" class="col-sm-3" style="text-align:left;">		<!-- HEADER_LEFT -->
<a href="javascript:void(0)" onClick="alert('は？');">ファイル(F)</a>

</div>															<!-- HEADER_LEFT -->

<div id="header_mid" class="col-sm-6" style="text-align:center;">	<!-- HEADER_MID -->
<span><b>pjTunes</b></span>
</div>															<!-- HEADER_MID -->

<div id="header_right" class="col-sm-3" style="text-align:right;">		<!-- HEADER_RIGHT -->
<a href="javascript:void(0)" onclick="window.open('about:blank','_self').close()">X</a>
</div>															<!-- HEADER_RIGHT -->

</div>									<!-- HEADER -->
<div id="control" class="row-fluid" style="height:80px;">			<!-- CONTROL -->
<audio id="main_audio"></audio>

<div id="control_left" class="col-sm-3" style="height:80px;">	<!-- CONTROL_LEFT -->
<form class="center" style="margin:0px; padding:0px; height:70px; text-align:center;">
<a class="btn_sub" id="ctl_btn_prev" href="javascript:void(0)"><img src="./lib/icons/appbar.control.rewind.variant.png" style="position:relative; width:30px; top:-2px; left:-2px;" /></a> 
<a class="btn_main" id="ctl_btn_play" href="javascript:void(0)"><img src="./lib/icons/appbar.control.play.png" style="position:relative; width:50px; top:-6px; left:-6px;" /></a> 
<a class="btn_sub" id="ctl_btn_next" href="javascript:void(0)"><img src="./lib/icons/appbar.control.fastforward.variant.png" style="position:relative; width:30px; top:-2px; left:0px;" /></a> 
<img src="./lib/icons/appbar.sound.1.png" style="width:30px;" />
<input type="range" class="vol" id="ctl_vol_sld" min="0" max="1.0" step="0.01" value="0.5" style="width:100px; display:inline;"></input>
<img src="./lib/icons/appbar.sound.3.png" style="width:30px;" />
</form>
</div>										<!-- CONTROL_LEFT -->

<div id="control_mid" class="col-sm-6">		<!-- CONTROL_MID -->
<div class="ctrl-box" style="height:70px; background:linear-gradient(#fefde8, #d8dcc3);">
<div class="center-block" style="text-align:center;">
<span class="txt-overflow" id="ctl_label_title">Title</span><br />
<span class="txt-overflow" id="ctl_label_album">Album</span>
<form style="margin:0px; padding:0px;">
<span id="ctl_label_time">0</span>
<input type="range" class="pos" id="ctl_pos_sld" min="0" max="1.0" step="0.1" value="0.0" style="margin 0px 10px; width:200px; display:inline;"></input>
<span id="ctl_label_endtime">0</span>
</form>
</div>
</div>
</div>										<!-- CONTROL_MID -->

<div id="control_right" class="col-sm-3" style="height:80px;">	<!-- CONTROL_RIGHT -->


</div>										<!-- CONTROL_RIGHT -->

</div>									<!-- CONTROL -->
</div>									<!-- TOP -->

<div id="body" class="row-fluid">				<!-- BODY -->
<div id="body_left" class="col-sm-2" style="border:solid 1px #3f4044; background-color:#d3d7e2;">					<!-- BODY_LEFT -->

<div id="folder_list">					<!-- FOLDER_LIST -->
<p style="margin:5px 0px;"><b>ライブラリ</b></p>
<ul id="folder_list_ul" style="list-style-image:url(./lib/icons/folder-open-document-music.png); margin:0px 0px 10px 20px; padding:0px;">

</ul>
</div>									<!-- FOLDER_LIST -->

</div>									<!-- BODY_LEFT -->
<div id="body_right" class="col-sm-10" style="border:solid 1px #3f4044;">					<!-- BODY_RIGHT -->

<div id="music_list" style="display:none;">	<!-- MUSIC_LIST -->

<div id="music_list_header" style="border-bottom:solid 1px #3f4044; margin-top:10px; padding-bottom:10px;">
</div>

<ul id="music_list_ul" style="margin:10px 0px 10px 20px; padding:0px;">

</ul>
</div>									<!-- MUSIC_LIST -->

<div id="file_list" style="display:none;">	<!-- FILE_LIST -->

<div id="file_list_header" style="border-bottom:solid 1px #3f4044; margin-top:10px; padding-bottom:10px;">
</div>

<ul id="file_list_ul" style="list-style-image:url(./lib/icons/document-music.png); margin:10px 0px 10px 20px; padding:0px;">

</ul>
</div>									<!-- FILE_LIST -->

<div id="top_page" style="display:none;">	<!-- TOP_PAGE -->
<h3>pjTunes</h3><hr />
左のカテゴリから選択しる<br />
</div>										<!-- TOP_PAGE -->

</div>									<!-- BODY_RIGHT -->
</div>									<!-- BODY -->
<div id="footer" class="row-fluid" style="background:linear-gradient(#c4c4c2, #959595); height:40px;">	<!-- FOOTER -->
<div id="footer_left" class="col-sm-3">		<!-- FOOTER_LEFT -->




</div>										<!-- FOOTER_LEFT -->
<div id="footer_mid" class="col-sm-6" style="text-align:center;">	<!-- FOOTER_MID -->
<div class="center-block center" style="text-align:center; height:40px;">
<span><?php
$si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
$base = 1024;
$path = '/share/MD0_DATA/';
$total_bytes = disk_total_space($path);
$free_bytes = disk_free_space($path);
$used_bytes = $total_bytes - $free_bytes;
$class = min((int)log($total_bytes , $base) , count($si_prefix) - 1);
echo sprintf('%1.2f' , $total_bytes / pow($base,$class)) . $si_prefix[$class] . "中、";
$class = min((int)log($used_bytes , $base) , count($si_prefix) - 1);
echo sprintf('%1.2f' , $used_bytes / pow($base,$class)) . $si_prefix[$class] . "使用 / ";
$class = min((int)log($free_bytes , $base) , count($si_prefix) - 1);
echo sprintf('%1.2f' , $free_bytes / pow($base,$class)) . $si_prefix[$class] . "空き";
echo " / HDD使用率:" . round($used_bytes / $total_bytes * 100, 2) . "%";
?>
</span>
</div>
</div>																<!-- FOOTER_MID -->
<div id="footer_right" class="col-sm-3">							<!-- FOOTER_RIGHT -->

<span id="footer_right_status"></span>

</div>																<!-- FOOTER_RIGHT -->
</div>									<!-- FOOTER -->
</div>									<!-- SUBFRAME -->
</div>									<!-- FRAME -->
<script src="/skin/js/bootstrap.min.js"></script>
</body>
</html>
