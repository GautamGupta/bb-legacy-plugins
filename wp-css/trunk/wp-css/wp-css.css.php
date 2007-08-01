<?php
  require_once( "../../bb-load.php" );
  header("content-type:text/css");

?>/* WP-CSS - http://box87.com/misc/wp-css */

body{background-color:#F9FCFE;}

a {
	border-bottom: 1px solid #69c;
	color: #00019b;
	text-decoration: none;
}

a:visited {
	color: #006;
}

a:hover {
/*	border-bottom: 1px solid #3a75ae;*/
	color: #069;
}

.updated{
  background:#CFEBF7 url( "<?php echo bb_get_plugin_uri(); ?>wp-css/notice.gif" ) no-repeat scroll 1em 50%;
  border:1px solid #2580B2;
  margin:1em 5% 10px;
  padding:0pt 1em 0pt 3em;
}

#footer p a img{visibility:hidden;background-color#F00}

#footer{ background:url( "<?php echo bb_get_plugin_uri(); ?>wp-css/bbpress.png" ) no-repeat top center; }

.theme-list.active li{background-color:#FFFFFF;}

#footer p a img{
  background-image:url();
}

#top {
  background-color:#14568A;
  color:#C3DEF1;
  padding:0.8em 19em 0.8em 2em;
}

#top a, #top p.login a{
  border:0;
}

#top h1 a:hover, #top p.login a:hover{
  /*border-bottom: 1px solid #3a75ae;*/
}

#bb-admin-menu {
	background-color: #83B4D8;
	border-top: 3px solid #448abd;
	/*margin: -1px;*/
	padding: .2em .2em .3em 2em;
}

#bb-admin-submenu{/*margin-top:1px;*/}

h2{border-bottom:0.5em solid #E5F3FF}

#bb-admin-menu .current a, #bb-admin-submenu .current a {
	font-weight: bold;
	text-decoration: none;
}

#bb-admin-menu a {
	color: #000;
	font-size: 14px;
	font-weight: normal;
	margin: 0;
	padding: 3px 5px;
	border-bottom: none!important;
}
#bb-admin-menu .current a {border-bottom: 1px solid #0D324F!important;}

#bb-admin-menu a:hover, #bb-admin-menu .current a {
	background-color: #ddeaf4;
	color: #333;
}

#bb-admin-menu li, #bb-admin-submenu li {
	display: inline;
	line-height: 200%;
	list-style: none;
	text-align: center;
	white-space: nowrap;
}

#bb-admin-menu .current a {
	background: #0d324f;
	border-right: 2px solid #4f96c8;
	border-top: 1px solid #96c0de;
	color: #fff;
	padding-bottom: 8px;
}

#bb-admin-submenu, #minisub {
	background: #0d324f;
	border-bottom: none;
	margin: 0;
	padding: 3px 2em 0 3em;
}

#bb-admin-submenu .current a {
	background: #f9fcfe;
	border-top: 1px solid #045290;
	border-right: 2px solid #045290;
	color: #000;
}

#bb-admin-submenu a {
	border: none;
	color: #fff;
	font-size: 12px;
	padding: .3em .4em .4em;
}

#bb-admin-submenu a:hover {
	background: #ddeaf4;
	color: #393939;
}

#bb-admin-submenu li {
	line-height: 180%;
	height: 25px;
}
