<?php
/*
Plugin Name: My Views
Description:  My Views is a powerful addition to the default "views" in bbPress. It will let you customize output and adds several new views.
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.081
*/ 
/*
History
0.03	first public release, for 0.8.2.x only, not trunk
0.04	trunk compatibility
0.05	breakup of view modules into seperate, optional plugins
0.06	available/installed plugins improvements (totals, sorting)
0.07	additional modules (statistics, available themes)	  Statistics is not finished yet and Themes requires bbPress Theme Switcher
0.08	bug fix for passthrough adding views to 0.8.2.x , optional header & footer for internal views, optional .my_views_header class

To Do:
1.	ability to change order of list of views available, remove other default views, rename views
2.	make table sort optional
3.	translation ability

*/

/*  optional header and footer html code
global $my_views;

$my_views['header']='
<div class="post raised" >
<b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b>
<div class="boxcontent">
';

$my_views['footer']='
</div>
<b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b>
</div>
';
*/

function my_views_init() {	//	to do: make much nicer with admin interface

$my_views_prefered_order=array(
	"latest-discussions","no-replies","untagged","my-topics","my-posts","most-views","most-posts","least-views","least-posts",
	"installed-plugins","available-plugins","installed-themes","available-themes","statistics"
	);

$my_views_access_level=array();	// not implimented yet

$my_views_remove_views=array("my-example");	// remove any views by slug name, built-in or from my-views, example: "untagged"

// stop editing

if (is_callable('bb_register_view')) {global $bb_views; $my_views_new_order=$bb_views;} else {global $views; $my_views_new_order=$views;}
$my_views_prefered_order=array_reverse($my_views_prefered_order);
$my_views_new_order=array_reverse($my_views_new_order);
foreach ($my_views_prefered_order as $view) {	// change display order    	$bb_views[$slug]   vs.  $view[$slug]
	if (isset($my_views_new_order[$view])) {		
		$temp=$my_views_new_order[$view];
		unset($my_views_new_order[$view]);
		$my_views_new_order[$view]=$temp;
	}
}
$my_views_new_order=array_reverse($my_views_new_order);
if (is_callable('bb_register_view')) {global $bb_views; $bb_views=$my_views_new_order;} else {global $views; $views=$my_views_new_order;}

foreach ($my_views_remove_views as $view) {
	if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
		bb_deregister_view($view);		 
	} else {		// Build 214-875	(0.8.2.1)
		global $views;
		unset($views[$view]);    
	}
}
if (!is_callable('bb_register_view')) {return $views;}	
}
if (!is_callable('bb_register_view')) {add_filter('bb_views','my_views_init',100 );} else {add_action('bb_init', 'my_views_init',100);}

function my_views_add_view_title($title) {
if (is_view()) {$title =  get_view_name(). ' &laquo; ' . bb_get_option( 'name' ); } 
return $title;
}
add_filter( 'bb_get_title', 'my_views_add_view_title' );

function my_views_dropdown($display=1) {	/*  makes views available as dropdown list anywhere you put <?php my_views_dropdown(); ?> */
	$views_dropdown='<form name="views_dropdown" id="views_dropdown">'
		.'<select  size=1  name="views_dropdown_select" onChange="if (this.selectedIndex >0) window.location=this.options[this.selectedIndex].value">'		
		.'<option style="text-indent: 1em;padding:2px" value="#">Show me...  </option>';		
	$views=get_views(); 
	foreach ($views as $view => $title ) {$views_dropdown.='<option  style="text-indent: 1em;padding:2px" value="'.get_view_link($view).'">'.$views[$view].'</option>';}
	$views_dropdown.='</select></form>'; 
if ($display) {echo $views_dropdown;} else {return $views_dropdown;}
}

function my_views_header($bbcrumb=false) {	/*  adds proper h2 header & dropdown to view.php template(s)  put <?php my_views_header(); ?> */	
if (is_view()) : 
if ($bbcrumb) :	
?>
<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php view_name(); ?></h3>
<?php 	
global $my_views;
if (isset($my_views['header'])) {echo $my_views['header'];}
endif;	?>
<div class="my_views_header"><h2 style="float:left;width:50%;"><?php view_name(); ?></h2><div style="float:right"><?php my_views_dropdown(); ?></div></div>
<br clear=both>
<?php
endif;
}

function my_views_footer() {
global $my_views;
if (isset($my_views['footer'])) {echo $my_views['footer'];}
}

function my_views_table_sort() {	// makes the views table sortable via client-side javascript - eventually should be external javascript .js
if (is_view()) :
?>

<script type="text/javascript"> 
addEvent(window, "load", sortables_init);

var SORT_COLUMN_INDEX;

function sortables_init() {
    if (!document.getElementsByTagName) return;
    tbls = document.getElementsByTagName("table");
    for (ti=0;ti<tbls.length;ti++) {
        thisTbl = tbls[ti];
//        if (((' '+thisTbl.className+' ').indexOf("sortable") != -1) && (thisTbl.id)) {
            // initTable(thisTbl.id);
            ts_makeSortable(thisTbl);
//       }
    }
}

function ts_makeSortable(table) {
    if (table.rows && table.rows.length > 0) {
        var firstRow = table.rows[0];
    }
    if (!firstRow) return;
    
    // We have a first row: assume it's the header, and make its contents clickable links
    for (var i=0;i<firstRow.cells.length;i++) {
        var cell = firstRow.cells[i];
        var txt = ts_getInnerText(cell);
        cell.innerHTML = '<a href="#" class="sortheader" '+ 
        'onclick="ts_resortTable(this, '+i+');return false;">' + 
        txt+'<span class="sortarrow">&nbsp;&uarr;&darr;</span></a>';
    }
}

function ts_getInnerText(el) {
	if (typeof el == "string") return el;
	if (typeof el == "undefined") { return el };
	if (el.innerText) return el.innerText;	//Not needed but it is faster
	var str = "";
	
	var cs = el.childNodes;
	var l = cs.length;
	for (var i = 0; i < l; i++) {
		switch (cs[i].nodeType) {
			case 1: //ELEMENT_NODE
				str += ts_getInnerText(cs[i]);
				break;
			case 3:	//TEXT_NODE
				str += cs[i].nodeValue;
				break;
		}
	}
	return str;
}

function ts_resortTable(lnk,clid) {
    // get the span
    var span;
    for (var ci=0;ci<lnk.childNodes.length;ci++) {
        if (lnk.childNodes[ci].tagName && lnk.childNodes[ci].tagName.toLowerCase() == 'span') span = lnk.childNodes[ci];
    }
    var spantext = ts_getInnerText(span);
    var td = lnk.parentNode;
    var column = clid || td.cellIndex;
    var table = getParent(td,'TABLE');
    
    // Work out a type for the column
    if (table.rows.length <= 1) return;
    var itm = ts_getInnerText(table.rows[1].cells[column]);
    sortfn = ts_sort_caseinsensitive;
    if (itm.match(/^\d\d[\/-]\d\d[\/-]\d\d\d\d$/)) sortfn = ts_sort_date;
    if (itm.match(/^\d\d[\/-]\d\d[\/-]\d\d$/)) sortfn = ts_sort_date;
    if (itm.match(/^[£$]/)) sortfn = ts_sort_currency;
    if (itm.match(/^[\d\.]+$/)) sortfn = ts_sort_numeric;
    if ((itm.match(/^[\d\,]+$/)) || itm==" n/a ") sortfn = ts_sort_recursive_comma;
    if (itm=="n/a") sortfn = ts_sort_recursive_comma;

    // if (itm.match(/^(.*\s)?([-+\u00A3\u20AC]?\d+)(\d{3}\b)/)) {alert("comma"); sortfn = ts_sort_currency;}  
    SORT_COLUMN_INDEX = column;
    var firstRow = new Array();
    var newRows = new Array();
    for (i=0;i<table.rows[0].length;i++) { firstRow[i] = table.rows[0][i]; }
    for (j=1;j<table.rows.length;j++) { newRows[j-1] = table.rows[j]; }

    newRows.sort(sortfn);

    if (span.getAttribute("sortdir") == 'down') {
        ARROW = '&nbsp;&nbsp;&uarr;';
        newRows.reverse();
        span.setAttribute('sortdir','up');
    } else {
        ARROW = '&nbsp;&nbsp;&darr;';
        span.setAttribute('sortdir','down');
    }
    
    // We appendChild rows that already exist to the tbody, so it moves them rather than creating new ones
    // don't do sortbottom rows
    for (i=0;i<newRows.length;i++) { if (!newRows[i].className || (newRows[i].className && (newRows[i].className.indexOf('sortbottom') == -1))) table.tBodies[0].appendChild(newRows[i]);}
    // do sortbottom rows only
    for (i=0;i<newRows.length;i++) { if (newRows[i].className && (newRows[i].className.indexOf('sortbottom') != -1)) table.tBodies[0].appendChild(newRows[i]);}
    
    // Assign updated classes to the rows when the sort's finished    
    for (i=0;i<table.rows.length;i++) {     	
    	if (table.rows[i].className!='sortbottom') {
    	if (i%2 == 0) table.rows[i].className='alt';
    	else table.rows[i].className='';    	
    	}
    }
    
    // Delete any other arrows there may be showing
    var allspans = document.getElementsByTagName("span");
    for (var ci=0;ci<allspans.length;ci++) {
        if (allspans[ci].className == 'sortarrow') {
            if (getParent(allspans[ci],"table") == getParent(lnk,"table")) { // in the same table as us?
                allspans[ci].innerHTML = '&nbsp;&nbsp;&nbsp;';
            }
        }
    }
        
    span.innerHTML = ARROW;
}

function getParent(el, pTagName) {
	if (el == null) return null;
	else if (el.nodeType == 1 && el.tagName.toLowerCase() == pTagName.toLowerCase())	// Gecko bug, supposed to be uppercase
		return el;
	else
		return getParent(el.parentNode, pTagName);
}
function ts_sort_date(a,b) {
    // y2k notes: two digit years less than 50 are treated as 20XX, greater than 50 are treated as 19XX
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]);
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]);
    if (aa.length == 10) {
        dt1 = aa.substr(6,4)+aa.substr(3,2)+aa.substr(0,2);
    } else {
        yr = aa.substr(6,2);
        if (parseInt(yr) < 50) { yr = '20'+yr; } else { yr = '19'+yr; }
        dt1 = yr+aa.substr(3,2)+aa.substr(0,2);
    }
    if (bb.length == 10) {
        dt2 = bb.substr(6,4)+bb.substr(3,2)+bb.substr(0,2);
    } else {
        yr = bb.substr(6,2);
        if (parseInt(yr) < 50) { yr = '20'+yr; } else { yr = '19'+yr; }
        dt2 = yr+bb.substr(3,2)+bb.substr(0,2);
    }
    if (dt1==dt2) return 0;
    if (dt1<dt2) return -1;
    return 1;
}

function ts_sort_currency(a,b) { 
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]).replace(/[^0-9.]/g,'');
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]).replace(/[^0-9.]/g,'');
    return parseFloat(aa) - parseFloat(bb);
}

function ts_sort_recursive_comma(a,b) { 
    aa = parseInt(ts_getInnerText(a.cells[SORT_COLUMN_INDEX]).replace(/[^0-9]/g,''));
    if (isNaN(aa)) aa = 0;
    bb = parseInt(ts_getInnerText(b.cells[SORT_COLUMN_INDEX]).replace(/[^0-9]/g,''));
    if (isNaN(bb)) bb = 0;
    return aa - bb;
}

function ts_sort_numeric(a,b) { 
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]).replace(/[^0-9.]/g,'');
    if (isNaN(aa)) aa = 0;
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]).replace(/[^0-9.]/g,'');
    if (isNaN(bb)) bb = 0;
    return aa-bb;
}

function ts_sort_caseinsensitive(a,b) {
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]).toLowerCase();
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]).toLowerCase();
    if (aa==bb) return 0;
    if (aa<bb) return -1;
    return 1;
}

function ts_sort_default(a,b) {
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]);
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]);
    if (aa==bb) return 0;
    if (aa<bb) return -1;
    return 1;
}

function addEvent(elm, evType, fn, useCapture)
// addEvent and removeEvent
// cross-browser event handling for IE5+,  NS6 and Mozilla
// By Scott Andrew
{
  if (elm.addEventListener){
    elm.addEventListener(evType, fn, useCapture);
    return true;
  } else if (elm.attachEvent){
    var r = elm.attachEvent("on"+evType, fn);
    return r;
  } else {
    alert("Handler could not be removed");
  }
} 
</script>

<?php 
endif;  // is_view
} 
add_action('bb_foot', 'my_views_table_sort');

?>