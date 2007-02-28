<?php
/*
written by Rimian Perkins 
http://www.freelancewebdeveloper.net.au
This plugin comes without any warrranty.
You may alter this code and/or redistribute it if you wish under the terms of the GPL License
Enjoy!
*/

include("config.php");

session_start(); //stop this thing running twice

if(!isset($_SESSION['installed'])){
	$bbdb = new bbdb(BBDB_USER, BBDB_PASSWORD, BBDB_NAME, BBDB_HOST);
	
	$query = "CREATE TABLE IF NOT EXISTS ".$bb_table_prefix."counter (
			hits INT UNSIGNED NOT NULL DEFAULT 1
			)";
	$bbdb->query($query);
	
	$query = "INSERT INTO ".$bb_table_prefix."counter (hits) VALUES (1);";
	$bbdb->query($query);
	
	$_SESSION['installed'] = 1;
	}
?>
<html>
<head>
	<title>Ye olde counter install</title>
	<style type="text/css">
	body{
		margin: 99px; font-family: sans-serif
		}
	li{
		padding: 5px;
		}
	</style>
</head>
<body>

<b>Done!</b><br />
<br /><br />This ye olde counter was brought to you by: <a href="http://www.freelancewebdeveloper.net.au" target="_blank">http://www.freelancewebdeveloper.net.au</a>. It was written to install on <a href="http://www.bbpress.org" target="_blank">bbpress</a><br />

<ol>
	<li style="color: red;">You should now delete this file from your server. Do not run it again.</li>
	<li>Upload the file 'ye-olde-counter.php' to your 'my-plugins' folder and browse to this file. You may need to create this directory.</li>
	<li>Place this<br /><br />
		&lt;div id="counter"&gt;&lt;?php ye_olde_counter(); ?&gt;&lt;/div&gt;
		<br /><br />
		in your 'bb-tempaltes/footer.php' file at the bottom somewhere in the footer div tag</li>
	<li>Add the counter class to your stylesheet: <br /><br />#counter{text-align: center; padding-top: 20px;}<br /><br /></li>
	<li>Add the number class to your stylesheet (you'll probably change this class): <br /><br />.counter_number{color: red;}<br /><br /></li>
	<li><a href="<?php echo $bb->domain;?>">View your counter</a></li>
	<li>If you post where you got it in your forum, we would much appreciate it!</li>
</ol>





</body>
</html>