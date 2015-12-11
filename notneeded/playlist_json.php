<?php
	  include($_SERVER['DOCUMENT_ROOT']."/DCRedes3/getid3/getid3/getid3.php");
	   $fileloc = $_SERVER['DOCUMENT_ROOT']."/DCRedes3";
	  $gid3 = new getID3();
	  $path = $_SERVER['DOCUMENT_ROOT']."/DCRedes3/Music";
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
		      $albums[$albumOrder]['tracks'][$tnum]['OGG'] = "/DCRedes3/Music/$curdir/$file";
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
		      $albums[$albumOrder]['Art'] = "/DCRedes3/Music/$curdir/$file";
		    }
		  break;

		  case ".zip":
		    if(!isset($albums[$albumOrder]['download'])){
		      $albums[$albumOrder]['download'] = "/DCRedes3/Music/$curdir/$file";
		    }
		  break;
		}
	      }    
	    }
	  }

	  ksort($albums);
	  for($i = 0; $i < count($albums); $i++){
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
  unset($albums);
?>
