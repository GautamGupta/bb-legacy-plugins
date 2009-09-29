<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params["serverid"] = md5(__FILE__);
$params["title"]    = "A chat using the green theme";
$params["theme"]    = "green";
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title> PHPFreeChat - Green theme</title>
    <?php $chat->printJavascript(); ?>
    <?php $chat->printStyle(); ?>
	</head>
	<body>
			<?php $chat->printChat(); ?>
				
			<?php
			  // print the current file
			  echo "<h2>The source code</h2>";
			  $filename = __FILE__;
			  echo "<p><code>".$filename."</code></p>";
			  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
			  $content = file_get_contents($filename);
			  highlight_string($content);
			  echo "</pre>";
			?>
			
	</body>
</html>