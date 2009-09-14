/* Page Cornr function */

$page_cornr = jQuery.noConflict();

$page_cornr(document).ready(function(){
 
	$page_cornr("#pagecornr").hover(function() {
		$page_cornr("#pagecornr img , .bg_msg").stop()
			.animate({
				width: '307px', 
				height: '319px'
			}, 500); 
		} , function() {
		$page_cornr("#pagecornr img").stop() 
			.animate({
				width: '50px', 
				height: '52px'
			}, 220);
		$page_cornr(".bg_msg").stop() 
			.animate({
				width: '50px', 
				height: '50px'
			}, 200);
	});
 
	
});