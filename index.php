<!DOCTYPE html>
<html>

<head>
  <title>The Dead Crows</title>
  <script language="javascript" type="text/javascript" src="js/jquery-2.0.2.min.js"></script>
  <script language="javascript" type="text/javascript" src="js/jquery-ui-1.10.3.custom.min.js"></script>
  <script language="javascript" type="text/javascript" src="js/jPlayer/jquery.jplayer.min.js"></script>
  <script language="javascript" type="text/javascript" src="js/jquery.Scroller-1.0.min.js"></script>
  <script language="javascript" type="text/javascript" src="js/jquery.backstretch.min.js"></script>
<script type="text/javascript">(function(){var ticker=document.createElement('script');ticker.type='text/javascript';ticker.async=true;ticker.src='//twitcker.com/ticker/thedeadcrows.js?speed=4&background=000000&tweet=ffffff&container=own-container&own-container=feed';(document.getElementsByTagName('head')[0]||document.getElementsByTagName('body')[0]).appendChild(ticker);})();</script>
  <link href="css/jPlayer4.css" rel="stylesheet" type="text/css" />
  <link href="css/style.css" rel="stylesheet" type="text/css" />
  <link href="css/custom3/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" type="text/css" />
  <script language="javascript" type="text/javascript">
    var global_lp = 0;
    var playItem = 0;
    var playList = 1;
<?php
	  $fileloc = $_SERVER['DOCUMENT_ROOT']."/";
	  include($fileloc."/getid3/getid3/getid3.php");
	  $gid3 = new getID3();
	  $path = $fileloc."/Music";
	  $dir = opendir($path);
	  $files = array();
	  $dirs = array();
	  $albums = array();
	  
	  while(($file = readdir($dir)) !== false){
	    if($file != ".." && $file != "." && strcmp(substr($file,0,1),".") != 0){
		if(is_dir($path."/".$file)){
		  $dirs[] = $file;
		  $files["$path/$file"] = array();
		} else {
		  $files["$path"][] = $file;
		}
	    }
	  }
	  
	  closedir($dir);
	  
	  foreach($files as $filepath=>$file){
	    $dir = opendir($filepath);
	    while(($file2 = readdir($dir)) !== false){
	      if($file2 != ".." && $file2 != "." && strcmp(substr($file2,0,1),".") != 0){
		  if(is_dir($filepath."/".$file2)){
		    $dirs[] = $file;
		    $files["$filepath/$file2"] = array();
		  } else {
		    $files["$filepath"][] = $file2;
		  }
	      }	      
	    }
	    closedir($dir);
	  }

	  foreach($dirs as $curdir){
	    if(file_exists("$path/$curdir/album.odie")){
	      $albumData = file_get_contents("$path/$curdir/album.odie");
	      $albumData = explode("\n",$albumData);
	      $albumName = $albumData[0];
	      $albumOrder = $albumData[1];
	      $albums[$albumOrder] = array(
		"Name"=>$albumName,
	      );
	      foreach($files["$path/$curdir"] as $file){
		$ext = substr($file,-4);
		switch($ext){
		  case ".mp3":
		    $res = $gid3->analyze("$path/$curdir/$file");
		    $use = "id3v2";
		    $tracknumber = "track_number";
		    if(!isset($res['tags']['id3v2'])){ $use = "id3v1"; $tracknumber = "track"; }
		    $tnum = $res['tags'][$use][$tracknumber][0];
		    if(isset($albums[$albumOrder]['tracks'][$tnum])){
		      $albums[$albumOrder]['tracks'][$tnum]['MP3'] = "Music/$curdir/$file";
		    } else {
		      $albums[$albumOrder]['tracks'][$tnum] = array(
			"Name" => $res['tags'][$use]['title'][0],
			"Artist" => $res['tags'][$use]['artist'][0],
			"Album" => $res['tags'][$use]['album'][0],
			"Comment" => $res['tags'][$use]['comment'][0],
			"MP3" => "Music/$curdir/$file",
		      );
		    }
		  break;
		  
		  case ".ogg":
		    $res = $gid3->analyze("$path/$curdir/$file");
		    $tnum = $res['tags']['vorbiscomment']['tracknumber'][0];
		    if(isset($albums[$albumOrder]['tracks'][$tnum])){
		      $albums[$albumOrder]['tracks'][$tnum]['OGG'] = "Music/$curdir/$file";
		    } else {
		      $albums[$albumOrder]['tracks'][$tnum] = array(
			"Name" => $res['tags']['vorbiscomment']['title'][0],
			"Artist" => $res['tags']['vorbiscomment']['artist'][0],
			"Album" => $res['tags']['vorbiscomment']['album'][0],
			"Comment" => $res['tags']['vorbiscomment']['comment'][0],
			"OGG" => "Music/$curdir/$file",
		      );
		    }
		  break;

		  case ".png": case ".PNG":
		    if(!isset($albums[$albumOrder]['Art'])){
		      $albums[$albumOrder]['Art'] = "Music/$curdir/$file";
		    }
		  break;

		  case ".zip":
		    if(!isset($albums[$albumOrder]['download'])){
		      $albums[$albumOrder]['download'] = "Music/$curdir/$file";
		    }
		  break;
		}
	      }    
	    }
	  }

	  ksort($albums);
	  for($i = 0; $i < $albums.count; $i++){
	    ksort($albums[$i]['tracks']);
	  }
	 	 	  
?>
var Music = new Array();
<?php foreach($albums as $order=>$album): if($order > 0): ?>
  Music[<?=$order?>] = { name: "<?=$album['Name']?>", art:"<?=$album['Art']?>", download:"<?=$album['download']?>", tracks: [
<?php foreach($album['tracks'] as $tracknum=>$track): ?>
    { name: "<?=$track['Name']?>", mp3: "<?=$track['MP3']?>", ogg: "<?php if($track['OGG'] != ""){echo $track['OGG'];}else{echo $track['OGG'];}?>", track: "<?=$tracknum?>" },
<?php endforeach; ?>
  ]};
<?php
  endif; endforeach;
  
?>
    
    $(document).ready(function(){
      $( "#radio" ).buttonset();
      $.backstretch("img/bkg.png");
      
      $("#marquee").SetScroller({
	velocity:    60,
	direction:   'horizontal',
	startfrom:   'right',
	loop:	   'infinite',
	movetype:    'linear',
	onmouseover: 'pause',
	onmouseout:  'play',
	onstartup:   'play',
	cursor: 	   'pointer'
      }).ResetScroller();
      
      $("#jquery_jplayer").jPlayer({
	ready: function() {
	  displaySong(playItem,playList);
	  playListInit(false); // Parameter is a boolean for autoplay.
	  $("#volumeLevel").html("<span>&nbsp;100%</span>");
	},
	swfPath: "js/jPlayer",
	volume: 1,
	cssSelectorAncestor: "",
	cssSelector: { "play": "#player_play",
		      "pause": "#player_pause",
		      "stop": "#player_stop",
		      "seekBar": "#player_progress_load_bar",
		      "playBar": "#player_progress_play_bar",
		      "mute": "#player_volume_min",
		      "unmute": "#player_volume_max",
		      "volumeBar": "#player_volume_bar",
		      "volumeBarValue": "#player_volume_bar_value",
		      "currentTime": "#play_time",
		      "duration": "#total_time" },
	ended: function(e){ playListNext(); },
	volumechange: function(e){
	  volinit = $("#jquery_jplayer").jPlayer("option","volume");
	  vol = Math.round(parseFloat(volinit)*100);
	  if($("#jquery_jplayer").jPlayer("option","muted")){
	    vol = "Muted";
	  }
	  $("#volumeLevel").html("<span>&nbsp;"+vol+"</span>");
	}
      });

      $("#ctrl_prev_playlist").click( function() {
	  playPrevList();
	  return false;
      });

      $("#ctrl_next_playlist").click( function() {
	  playNextList();
	  return false;
      });

      $("#ctrl_prev").click( function() {
	      playListPrev();
	      return false;
      });

      $("#ctrl_next").click( function() {
	      playListNext();
	      return false;
      });
    
      function displaySong(songNum, albumNum) {
	$("#AlbumArt").html("<img alt=\""+ Music[albumNum].name +" album name\" src=\""+ Music[albumNum].art +"\" style=\"width:75px; height:75px; border:0;\" />");
	$("#Album").html("<span>"+ Music[albumNum].name +"</span>");
	$("#Song").html("<span>"+ Music[albumNum].tracks[songNum].name +"</span>");
      }

      function playListInit(autoplay) {
	if(autoplay) {
	  playListChange( playItem,playList );
	} else {
	  playListConfig( playItem,playList );
	}
      }

      function playListConfig( index , list) {
	$("#playlist_item_"+playList+"_"+playItem).removeClass("playlist_current");
	$("#playlist_item_"+list+"_"+index).addClass("playlist_current");
	    
	playItem = index;
	playList = list;
	displaySong( index, list );
	$("#jquery_jplayer").jPlayer("setMedia",{ mp3: Music[playList].tracks[playItem].mp3, oga: Music[playList].tracks[playItem].ogg} );
      }

      function playListChange( index, list ) {
	playListConfig( index, list );
	$("#jquery_jplayer").jPlayer("play");
      }

      function findLastPlayList(){
	lastIndex = 1;
	for(i in Music){
	  lastIndex = i;
	}
	return lastIndex;
      }

      function findNextPlayList(nextPlayList){
	while(Music[nextPlayList] == null){
	  nextPlayList++;
	}
	return nextPlayList;
      }

      function playNextList() {
	      playList = (playList+1 < Music.length) ? findNextPlayList(playList+1) : 1;
	      //$("#playListPanel").accordion("activate",playList-1);
	      playListChange(0,playList);
      }

      function playPrevList() {
	      playList = (playList-1 >= 1) ? playList-1 : findLastPlayList();
	      //$("#playListPanel").accordion("activate",playList-1);
	      playListChange(0, playList);
      }

      function playListNext() {
	      var index = (playItem+1 < Music[playList].tracks.length) ? playItem+1 : "next";
	      if(index == "next"){
		playNextList();
	      } else {
		playListChange( index , playList);
	      }
      }

      function playListPrev() {
	      var index = (playItem-1 >= 0) ? playItem-1 : "next";
	      if(index == "next"){
		playPrevList();
	      } else {
		playListChange( index , playList );
	      }
      }

      for(j in Music){
	for(i in Music[j].tracks){
	    $("#playlist_item_"+j+"_"+i).data( "item", i).data("list", j).on("click",function(){
	      index = $(this).data("item");
	      list = $(this).data("list")
	      if (playItem != index || list != playList) {
		//alert("playlist change "+index+" "+list);
		playListChange( index, list );
	      }
	    });
	}
      }
    });
  </script>

<script>
  $(function() {
  $("#accordion").accordion({
	  header: ".accordHeader",
	  active: false,
	  collapsible: true,
	  icons: false,
	  heightStyle: "content",
	  clearStyle:true
	});
   $("#accordion2").accordion({
	  header: ".accordHeader",
	  active: false,
	  collapsible: true,
	  icons: false,
	  clearStyle:true
	});
  
    $( "#tabs" ).tabs();
 
    // fix the classes
    $( ".tabs-bottom .ui-tabs-nav, .tabs-bottom .ui-tabs-nav > *" )
      .removeClass( "ui-corner-all ui-corner-top" )
      .addClass( "ui-corner-bottom" );
 
    // move the nav to the bottom
    $( ".tabs-bottom .ui-tabs-nav" ).appendTo( ".tabs-bottom" );
  });
  </script>
  <style>
  /* force a height so the tabs don't jump as content height changes */
  #tabs .tabs-spacer { float: left; height: 200px; }
  .tabs-bottom .ui-tabs-nav { clear: left; padding: 0 .2em .2em .2em; }
  .tabs-bottom .ui-tabs-nav li { top: auto; bottom: 0; margin: 0 .2em 1px 0; border-bottom: auto; border-top: 0; }
  .tabs-bottom .ui-tabs-nav li.ui-tabs-active { margin-top: -1px; padding-top: 1px; }
  </style>

</head>

<body>
<div id="toppanel">
<div id="feed"></div>
<div id="toppanel_table">
    <div id="jplayer_area">
      <div id="jquery_jplayer"></div>
      <div id="jquery_player_area">
	  <div id="AlbumArt"></div>
	  <div id="TrackData">
	      <div id="marquee"><p>
		<span id="Album" class="spqr specColor">Loading</span> - <span id="Song">Playlists...</span>
	      </p></div>
	  </div>
	  <div id="jplayer_buttons">
	    <span><span id="play_time" class="specColor">n/a</span>/<span id="total_time" class="specColor">n/a</span></span><br />
	    <ul id="player_controls">
	      <li id="player_play" class="iconColors">play</li>
	      <li id="player_pause" class="iconColors">pause</li>
	      <li id="player_stop" class="iconColors">stop</li>
	      <li id="ctrl_prev_playlist" class="iconColors">previous playlist</li>
	      <li id="ctrl_prev" class="iconColors">previous</li>
	      <li id="ctrl_next" class="iconColors">next</li>
	      <li id="ctrl_next_playlist" class="iconColors">next playlist</li>
	      <li id="player_volume_min" class="iconColors">min volume</li>
	      <li id="player_volume_max" class="iconColors">max volume</li>
	      <li id="player_volume_bar">
		<div id="player_volume_bar_value" class="specBackColor"></div>
	      </li>
	      <li id="volumeLevel" class="specColor"></li>
	    </ul>
	    <br class="clear" />
	</div>
      </div>
    </div>
  
    <div id="sharebox">
      <span style="color:white;">Share//Choke</span><br />
      <span style="margin-bottom:2px;"><a href="http://www.facebook.com/pages/The-Dead-Crows/167571753307498"><img alt="[ f ]" src="img/social/fb.png" class="shareicon" /></a> <a href="http://www.twitter.com/thedeadcrows"><img alt="[ t ]" src="img/social/twit.png" class="shareicon" /></a> <a href="http://www.youtube.com/user/shadenhand"><img alt="[Yt]" src="img/social/yt.png" class="shareicon" /></a></span><br />
      <span><a href="mailto:lysanderdarkstar@gmail.com"><img alt="[@]" src="img/social/email.png" class="shareicon" /></a> <a href="https://plus.google.com/102528493330912113709?prsrc=3"><img alt="[+]" src="img/social/plus.png" class="shareicon" /></a> <a href="http://lysanderdarkstar.deviantart.com/"><img alt="[dA]" src="img/social/da.png" class="shareicon" /></a></span>
    </div>
  </div>
</div>
<div id="container">
  <img alt="topper" src="img/topper.png" id="topper" />
  <div id="content">
    <div id="tabs">
  <ul>
    <li><a href="#tabs-1">NEWS</a></li>
    <li><a href="#tabs-2">ALBUMS</a></li>
    <li><a href="#tabs-3">STORIES</a></li>
  </ul>
  <div id="tabs-1">
    <?php
	$requestURL="http://www.blogger.com/feeds/4433405937086771963/posts/default";
	$xml=simplexml_load_file($requestURL);
	?>
	<?php if(isset($xml)): ?>
	<!--
	  <div style="float:left; padding-right: 10px; border-right:1px solid white; margin-top:80px;">
	    <ul id="sliding-navigation">
	      <li class="sliding-element"><a href="#"><?=$xml->entry[0]->title;?></a></li>
	      <li class="sliding-element"><a href="#"><?=$xml->entry[1]->title;?></a></li>
	      <li class="sliding-element"><a href="#"><?=$xml->entry[2]->title;?></a></li>
	    </ul>
	  </div>
	-->  
	  <?php for($i =0; $i < 3; $i++):?>
	    <p class="newsTitle"><?=$xml->entry[$i]->title;?></p>
	    <div class="newsItem">
	      <p>Posted on: 
		<span style="font-weight:bold;">
	  	<?=$xml->entry[$i]->published;?>
		</span>
		<br />
		<?=$xml->entry[$i]->content;?>
		<br /><br />
	      </p>
	   </div>
	  <?php endfor; ?>
	  <? endif;?>
      </div>


  <div id="tabs-2">
   <div id="accordion">
   <?php foreach($albums as $order=>$album): if($order > 0): ?>
  <div id="album<?=$order;?>" class="accordHeader" style="height:55px;">
    <img class="AlbumArt" src="<?=$album['Art'];?>" style="width:50px; height:50px;"  alt="" />
    <span style="font-size:large;""><?=$album['Name'];?></span>
    <br style="clear:both;" />
  </div>
  <div id="list<?=$order;?>">
<?php if(!empty($album['download'])): ?>
  <span><a href="<?php echo $album['download']; ?>" ><span class="ui-icon ui-icon-arrowthickstop-1-s" style="display:inline-block"></span><span style="font-size:x-small;">Download this album</span></a></span>
<?php endif; ?>
<?php foreach($album['tracks'] as $tracknum=>$track): $item = $tracknum-1;?>
<p id="playlist_item_<?=$order."_".$item?>">
  <span><?php if($tracknum < 10){echo "0";} echo $tracknum; ?>. <?=$track['Name']?> </span>
  [<a href=" <?= $track['MP3']?>">MP3</a>]<?php if(!empty($track['OGG'])): ?> | [<a href=<?=$track['OGG']?>>OGG</a>]<?php endif; ?>
</p>
<?php endforeach; ?>
</div>
<?php endif; endforeach; unset($albums);?>
</div>
  </div>
  <div id="tabs-3"> 
   <div id="accordion2">
  <?php
	$requestURL="http://www.blogger.com/feeds/4898480439796898507/posts/default";
	$xml=simplexml_load_file($requestURL);
	?>
	<?php if(isset($xml)): ?>
	<!--
	  <div style="float:left; padding-right: 10px; border-right:1px solid white; margin-top:80px;">
	    <ul id="sliding-navigation">
	      <li class="sliding-element"><a href="#"><?=$xml->entry[0]->title;?></a></li>
	    </ul>
	  </div>
	-->  
	  <?php for($i =0; $i < 3; $i++):?>
	    
	      <span class="accordHeader"><?=$xml->entry[$i]->title;?></span>
		<p><?=$xml->entry[$i]->content;?></p>
		<br /><br />
	      
	   
	  <?php endfor; ?>
	  <? endif;?>
	  </div>
  </div>
</div>
  </div>
</div>
<div id="footer">
  <div id="footerleft">
    <img class="ccicons" src="img/creativecommons/chooser_cc.png" alt="Creative Commons" />
    <img class="ccicons" src="img/creativecommons/chooser_sa.png" alt="Share Alike" />
    <img class="ccicons" src="img/creativecommons/chooser_by.png" alt="By Attribution" />
    <img class="ccicons" src="img/creativecommons/chooser_nc.png" alt="Noncommerical" />
    <span>&copy; The Dead Crows 2013; Creative Commons 3.0 Share Alike, By Attribution, Noncommerical Lincense.</span>  
  </div>
  <div id="footerright">
    Code Provided by <a href="http://mzeora.com">Michael N. Esposito II</a>
  </div>
</div>
</body>
</html>
