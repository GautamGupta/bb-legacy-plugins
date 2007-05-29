<?php
	if (is_topic()) {
		//Ad to display on topics pages (between posts) ?>
		<div align="center" style="border:2px #9c1410 dotted; width:80%; padding: 10px; margin:10px auto;">This is an example of an ad between posts.</div>
		<?php 
	
	} elseif (is_forum()) {
		//Ad to display on forum pages ?>
		<div style="border:2px #9c1410 dotted; width:100%; padding: 10px;"><h2>This forum brought to you by:</h2>...an ad on a forum page.</div> 
		<?php 

	} elseif (is_bb_profile()) {
		//Ad to display on profile pages ?>
		<div style="border:2px #9c1410 dotted; width:100%; padding: 10px;">This is an example of an ad in a profile.</div>
		<?php 
		
	} elseif (is_front()){
		//Ad to display on the front page ?>
		<div style="border:2px #9c1410 dotted; width:100%; padding: 10px;"><span style="font-size:150%;">This bulletin board is sponsored by:</span><br>...a really great front page ad.</div>
		<?php 
	
	}else{
	//An ad that would show up if it isn't one of the pages listed above.
	//<div>Your ad here</div>
	}
?>