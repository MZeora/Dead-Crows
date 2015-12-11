<script>
	  for(j in Music){
	    for(i in Music[j].tracks){
		$("#playlist_item_"+j+"_"+i).data( "item", i).data("list", j).hover(
		  function() {
		    if($(this).data("list") != playList || playItem != $(this).data("item")){
		      $(this).addClass("playlist_hover");
		    }
		  },function() {
		    $(this).removeClass("playlist_hover");
		  }
		);
	    }
	  }

	$("#playlist_item_"+playList+"_"+playItem).addClass("playlist_current");
</script>
<div id="playListAcc">
<?php foreach($albums as $order=>$album): if($order > 0): ?>
  <div id="album<?=$order;?>" class="accordHeader">
    <img class="AlbumArt" src="$album['Art'];?>" alt="" />
    <span class="spqr"><?=$album['Name'];?></span>
    <br style="clear:both;" />
  </div>
  <div id="list<?=$order;?>">
<?php if(!empty($album['download'])): ?>
  <span><a href="$album['download'];?>"><img src="img/dl.png" alt="Download" style="border:0;"/>&nbsp;Download this Album</a></span>
<?php endif; ?>
<?php foreach($album['tracks'] as $tracknum=>$track): $item = $tracknum-1;?>
<p id="playlist_item_<?=$order."_".$item?>">
  <span><?php if($tracknum < 10){echo "0";} echo $tracknum; ?>. <?=$track['Name']?> </span>
  [<a href="$track['MP3']?>">MP3</a>]<?php if(!empty($track['OGG'])): ?> | [<a href="$track['OGG']?>">OGG</a>]<?php endif; ?>
</p>
<?php endforeach; ?>
</div>
<?php endif; endforeach; ?>
</div>