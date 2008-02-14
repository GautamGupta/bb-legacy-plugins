// JavaScript Document
function Ajax()
{
	var xmlhttp=false;
 	try 
	{
 		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 	} catch (e) {
 		try 
		{
 			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
 		} catch (E) {
 			xmlhttp = false;
 		}
  	}

	if (!xmlhttp && typeof XMLHttpRequest!='undefined') 
	{
 		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}
function sendPostToPreview(path)
{
	var data = document.getElementById('post_content');
	var target = document.getElementById('live_comment_preview_main_content');
	var url = path+"/process_preview.php";
	var ajax = Ajax();
	target.innerHTML = '<div id="live_comment_preview_loading"><div align="center"><img src="'+path+'/assets/rotation.gif" alt="Loading" width="32" height="32" /></div></div>';
	ajax.open("POST", url , true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
			target.innerHTML = ajax.responseText
		}
	}
	ajax.send("data="+data.value);
}
