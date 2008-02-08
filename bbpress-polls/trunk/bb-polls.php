<?php
/*
Plugin Name: bbPress Polls
Description:  allows users to add polls to topics, with optional ajax-ish update action
Plugin URI:  http://bbpress.org/plugins/topic/62
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.31

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions:   install, activate, tinker with settings located several lines below

Version History:
0.01	: bb-polls is born - no voting yet, just create a poll for testing
0.10	: first public beta
0.11	: bug fix for polls on page 1 setting
0.12	: poll can now be on first/last/both/all pages & add text to topic titles like [poll]
0.13	: more control over who can add/vote/view/edit polls 
0.14	: colour fixes for default theme
0.15  	: cache performance fixes, extra custom label ability, more css classes, colour tweaks
0.16	: added __() for automatic translations when possible, all text is now in array near top
0.17	: trick bbpress to keep data unserialized until needed for performance (backward compatible)
0.18	: post data fix for refreshed pages (via redirect, nasty but no other way?)
0.19	: first ajax-ish behaviours added for view current voting results and then back to the form - pre-caching forms, but no submit saving ajax yet 
0.20	: more text found & moved to array for translations, float removed from default css for Right-to-Left setups, graph bars limited to min & max
0.21	: many little fixes for IE to work properly, css changes to make IE vs Firefox almost identical 
0.22	: voting is now ajax-ish - only non-ajax-ish form is the one to create a poll, might be awhile - cancel button also added to create poll form
0.23	: javascript fix for internet explorer (has to delay append action a few milliseconds or update won't appear to happen)
0.24	: bug fix for opera trying to cache javascript requests - added alert if they try to vote without selection (todo: need to alert on non-ajax) 
0.25	: experimental double-execute fix for Null
0.26	: warnings cleanup for better code
0.27	: bugfix: poll not showing for non-logged in guest and view setting set to "read"
0.28	: enhancement so admin are always offered to start a poll on any topic regardless
0.29	: enhancement so admin can always delete any poll
0.30	: enhancement so admin can edit any poll (don't change the order of questions, it's a simple edit for now). Vote count edits, etc. coming later.
0.31	: bug fix to also track/re-save change of poll type (multiple/single) on edit
	
To Do: 
	: admin menu (coming soon - edit plugin directly for now, many options)
	: administrative editing/deleting of existing polls.	
	: display a poll anywhere within bbpress templates
	: display all polls on a single page
*/
//	edit these lines below (until an Admin menu is made)

	$bb_polls['minimum_view_level']="read";   // who can view polls = read / participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_vote_level']="participate";   // who can vote on polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_add_level']="participate";   // who can add polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_edit_level']="administrate";   // who can edit polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_delete_level']="administrate";   // who can edit polls = participate / moderate / administrate  (watchout for typos)

	$bb_polls['only_topic_author_can_add']=true;   // false=anyone can add a poll to any topic /  true=only the topic starter (admin can always add)
	$bb_polls['show_poll_on_which_pages']="both";    // show poll only on pages = first / last / both / all
		
	$bb_polls['add_within_hours']=3;   // how many hours later can a poll be added 	(for users/moderator - admin can always add)
	$bb_polls['edit_within_hours']=12;   // how many hours later can poll be edited	(for users/moderator - admin can always edit)		

	$bb_polls['close_with_topic']=true;   // if topic is closed, is poll closed?						// doesn't work yet
	$bb_polls['close_after_days']=365;   // if not closed with topic, close after how many days?				// doesn't work yet

	$bb_polls['max_options']=9;     // default number of poll answer slots offered 
	$bb_polls['max_length']=100;     // how long can the poll question & answers be
	$bb_polls['options_sort']=false;	 // true=show options by most votes, false=show options in original order
	
	$bb_polls['use_ajax']=true;		// true = enables ajax-ish behaviours, still works without javascript  / false = typical page refreshes
	$bb_polls['test_mode']=false;	// if set to "true" allows multiple votes per person for testing purposes only
				
	$bb_polls['poll_question']=__("Would you like to add a poll to this topic for members to vote on?");
	$bb_polls['poll_instructions']=__("You may submit a poll question with several options for other members to vote from.");
	$bb_polls['label_single']=__("you can vote on <u>ONE</u> choice");
	$bb_polls['label_multiple']=__("you can vote on <u>MULTIPLE</u> choices");
	$bb_polls['label_poll_text']=__("poll");    // default "poll" = text to show if on topic title if it has a poll (delete text to disable) // you can even use HTML/CSS
	$bb_polls['label_votes_text']=__("votes");  // default "votes" = text to show for votes
	$bb_polls['label_vote_text']=__("Vote");  // default "VOTE" = text to show for VOTE button	
	$bb_polls['label_save_text']=__("Save");  // default "SAVE" = text to show for SAVE button
	$bb_polls['label_cancel_text']=__("Cancel");  // default "CANCEL" = text to show for CANCEL button
	$bb_polls['label_edit_text']=__("Edit");  // default "EDIT" = text to show for Edit button
	$bb_polls['label_delete_text']=__("Delete");  // default "DELETE" = text to show for Edit button
	$bb_polls['label_option_text']=__("option");  // default "option" = text to show for options
	$bb_polls['label_question_text']=__("poll question");
	$bb_polls['label_results_text']=__("show poll results");
	$bb_polls['label_now_text']=__("vote now");	
	$bb_polls['label_nocheck_text']=__("You haven't selected anything!");	
	$bb_polls['label_warning_text']=__("This cannot be undone. Are you sure to delete?");
		
	$bb_polls['style']="#bb_polls {list-style: none; width:350px; margin-left:-10px; line-height:120%; padding:5px; border:1px solid #ADADAD;  font-size:85%; color:#000; background:#eee; }
			#bb_polls .submit {cursor: pointer; cursor: hand; text-align:center; padding:2px 5px;}
			#bb_polls .nowrap {white-space:nowrap;}
			#bb_polls p {margin:15px 0;padding:0;}
			#bb_polls .poll_question, #bb_polls .poll_footer {font-weight:bold; text-align:center; color:#2E6E15;}
			#bb_polls .poll_label {font-weight:bold;}								
			#bb_polls .poll_option {margin:-12px 0 -5px 0; text-align:center;font-weight:bold; font-size:9px; line-height:5px; padding:2px 1px;  border:1px solid #303030; color:#fff; }
			#bb_polls .poll_option1 {background:red;}
			#bb_polls .poll_option2 {background:green;}
			#bb_polls .poll_option3 {background:blue;}
			#bb_polls .poll_option4 {background:orange;}
			#bb_polls .poll_option5 {background:purple;}
			#bb_polls .poll_option6 {background:pink;}
			#bb_polls .poll_option7 {background:yellow;}
			#bb_polls .poll_option8 {background:navy;}
			#bb_polls .poll_option9 {background:grey;}
		          ";			

//	- stop editing here -

function bb_polls_pre_poll($topic_id,$edit_poll=0) { 
global $bb_polls,$topic,$poll_options,$page;
if ($bb_polls['minimum_view_level']=="read" || bb_current_user_can($bb_polls['minimum_view_level']) ) :   
$topic_id=bb_polls_check_cache($topic_id);
$user_id=bb_get_current_user_info( 'id' );
$administrator=bb_current_user_can('administrate');
bb_polls_add_javascript($topic_id);	// ajax-ish
if ($edit_poll || ! isset($topic->poll_options)) {	// no saved poll question with options

	if ($administrator || (bb_current_user_can($bb_polls['minimum_add_level'])  &&  ! ( $bb_polls['only_topic_author_can_add'] && $topic->topic_poster!=$user_id))) {	// 1
	if ($administrator || (! ( $bb_polls['add_within_hours'] &&  $bb_polls['add_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600))) {	// 2
	if ($administrator || (! ($bb_polls['close_with_topic'] && $topic->topic_open!=1 ))) {	// 3
	
		if (isset($_POST['poll_question'])) {	 // save new poll setup from _post data 
				bb_polls_save_poll_setup($topic_id);						
				bb_polls_show_poll_vote_form($topic_id);
		} else {
 			if (isset($_GET['start_new_poll']) && intval($_GET['start_new_poll'])) { 
 				bb_polls_show_poll_setup_form($topic_id); 
		} else {
 			if (isset($_GET['edit_poll']) && intval($_GET['edit_poll'])) { 
 				bb_polls_show_poll_setup_form($topic_id,1,1);  				
 		} else {	
			// ask if they want to start a new poll
				echo '<li id="bb_polls"><a class=nowrap onClick="if (window.bb_polls_insert_ajax) {bb_polls_start_new_poll_ajax();return false;}" href="'.add_query_arg( 'start_new_poll', '1').'">'.$bb_polls['poll_question'].'</a></li>'; 
		
		} } }	// end new poll question + end show start_new_poll form 

	} } }  // 1 2 3  checks for allowed settings to start/edit poll 

} else {		// there's a saved poll question with options

	if (isset($_POST['poll_vote'])) {	// save new poll vote from _post data 		  
			bb_polls_add_vote($user_id,$topic_id);			
			bb_polls_show_poll_results($topic_id);
	} else {
		if (isset($_GET['show_poll_results'])) {	// override to show poll results
			bb_polls_show_poll_results($topic_id); 
	} else {	 // obey per page setting	

		if ( $bb_polls['show_poll_on_which_pages']=="all" 
		||  ($page==1 && ($bb_polls['show_poll_on_which_pages']=="first" || $bb_polls['show_poll_on_which_pages']=="both" )) 
		|| ($page==get_page_number( $topic->topic_posts ) && ($bb_polls['show_poll_on_which_pages']=="last" || $bb_polls['show_poll_on_which_pages']=="both" )) 
		) { 	
			if (!$user_id || bb_polls_has_voted($user_id,$topic_id)) {
		 		bb_polls_show_poll_results($topic_id); 	// they voted, show results
			} else {		 
			
				bb_polls_show_poll_vote_form($topic_id);	// let them vote
	} } } }		
}
endif;	
remove_action('topicmeta','bb_polls_pre_poll',200);  // NullFix ?
} add_action('topicmeta','bb_polls_pre_poll',200);

function bb_polls_check_cache($topic_id) {
global $bb_polls,$topic,$poll_options;  
if (!$topic_id || $topic_id != $topic->topic_id) {if (!isset($topic)) {bb_repermalink();} $topic_id=get_topic_id(); $topic = get_topic($topic_id);  $poll_options=(isset($topic->poll_options)==true ? $topic->poll_options : null); }
if (isset($poll_options) && !is_array($poll_options)) {$poll_options=unserialize(substr($poll_options,2));}   // trick bb_press to keep poll data unserialized
return $topic_id;
}

function bb_polls_has_voted($user_id,$topic_id) {    
global $bb_polls,$topic,$poll_options;
if ($bb_polls['test_mode']) {return false;}  // for testing only, allows multiple votes by anyone
$topic_id=bb_polls_check_cache($topic_id);
if (!$user_id) {$user_id=bb_get_current_user_info( 'id' );}
$votes=''; for ($i=1; $i<=$bb_polls['max_options']; $i++) {if (isset($poll_options['poll_vote_'.$i])) {$votes.=",".$poll_options['poll_vote_'.$i].",";}}
return (strpos($votes,",".$user_id.",")===false ? false : true);
}

function bb_polls_add_vote($user_id,$topic_id) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_vote_level'])) :
$topic_id=bb_polls_check_cache($topic_id);
$voted_flag=false; 
if (!bb_polls_has_voted($user_id,$topic_id)) {		
	for ($i=0; $i<=$bb_polls['max_options']; $i++) { 
		$test=0;		
		if (isset($_POST['poll_vote_'.$i])) {$test=intval($_POST['poll_vote_'.$i]);} 
		elseif (isset($_GET['poll_vote_'.$i])) {$test=intval($_GET['poll_vote_'.$i]);}		
		if ($test>0) :					
			if (!isset($poll_options['poll_count_'.$test])) {	// initialise counters					
				$poll_options['poll_count_'.$test]=0; 
				$poll_options['poll_vote_'.$test]='';
				if (!isset($poll_options['poll_count_0'])) {$poll_options['poll_count_0']=0;} 
			}  else  { $poll_options['poll_vote_'.$test].=",";}							
			$poll_options['poll_vote_'.$test].=$user_id;	// add user's vote, single or multiple
			$poll_options['poll_count_'.$test]++;  		// update count for option
			$voted_flag=true;					// set flag to update overall count			
			if (!$poll_options['poll_multiple_choice']) {break;}	// don't allow single choice votes to count multiple choices, only first answer
		endif;			
	}		
	if ($voted_flag) {$poll_options['poll_count_0']++;}  		// update count for overall	
	bb_update_topicmeta( $topic_id, 'poll_options', '..'.serialize($poll_options));  // save and trick bb_press to not deserialize unless necessary
	return true;
}  
else {return false;}   // has voted already
endif;
}

function bb_polls_show_poll_results($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
$administrator=bb_current_user_can('administrate');
if ($bb_polls['minimum_view_level']=="read" || bb_current_user_can($bb_polls['minimum_view_level']) ) {
$topic_id=bb_polls_check_cache($topic_id);
$output='<p class=poll_question>'.$bb_polls['label_poll_text'].': '.$poll_options['poll_question'].'</p>';

if (!$poll_options['poll_multiple_choice'] && isset($poll_options['poll_count_0'])) {$real_vote_count=intval($poll_options['poll_count_0']);}
else {$real_vote_count=0; if ($poll_options['poll_multiple_choice']) {for ($i=1; $i<=$bb_polls['max_options']; $i++) {if (isset($poll_options['poll_count_'.$i])) {$real_vote_count+=intval($poll_options['poll_count_'.$i]);}}}}

for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if (isset($poll_options[$i])) { 		
		$output.= '<p class=poll_label>'.$poll_options[$i].' : ';	
		$test=(isset($poll_options['poll_count_'.$i]) ? intval($poll_options['poll_count_'.$i]) : 0);
		$output.= ' ('.$test.' '.$bb_polls['label_votes_text'].')';
		if ($test) {
			$vote_percent=(round($test/$real_vote_count,2)*100);
			$vote_width=$vote_percent; if ($vote_width < 5) {$vote_width=5;} else {if ($vote_width >98 ) {$vote_width=98;}}
			$output.= ' <div style="width:'.$vote_width.'%"class="poll_option poll_option'.$i.'"> '.$vote_percent.' % </div> ';
		}
		$output.= ' </p>';
	}
}
$test=(isset($poll_options['poll_count_0']) ? intval($poll_options['poll_count_0']) : 0);
$output.= '<p class=poll_footer>'.intval($test).' '.$bb_polls['label_votes_text'].'</p>';
if (isset($_GET['show_poll_results']) || (bb_get_current_user_info( 'id' ) && !bb_polls_has_voted(bb_get_current_user_info( 'id' ),$topic_id) )) {
$output.= '<p class=poll_footer>( <a onClick="if (window.bb_polls_insert_ajax) {bb_polls_show_poll_vote_form_ajax();return false;}" href="'.remove_query_arg( 'show_poll_results').'">'.$bb_polls['label_now_text'].'</a> )</p>';
}
$output.=bb_polls_edit_link();
$output=stripslashes($output);
if ($display) {echo '<li id="bb_polls">'.$output.'</li>';} else {return $output;}
}
}

function bb_polls_show_poll_vote_form($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_vote_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
if ($poll_options['poll_multiple_choice']==1) {$poll_type="checkbox";} else {$poll_type="radio";}
$output='<form action="'.remove_query_arg( 'show_poll_results').'" method="post" name="bb_polls" onSubmit="if (window.bb_polls_insert_ajax) {bb_polls_add_vote_ajax();return false;}">
	 <p class=poll_question>'.$bb_polls['label_poll_text'].': '.$poll_options['poll_question'].'</p>';
for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if (isset($poll_options[$i])) {
		if ($poll_options['poll_multiple_choice']==1) {$poll_name="poll_vote_".$i;} else {$poll_name="poll_vote_0";}
		$output.= '<p><input type="'.$poll_type.'" name="'.$poll_name.'" value="'.$i.'"> '.$poll_options[$i].' </p>';
	}
}
$output.= '<p class=poll_footer><input class=submit type=submit  name="poll_vote" value="'.$bb_polls['label_vote_text'].'"></p>
	<p class=poll_footer>( <a onClick="if (window.bb_polls_insert_ajax) {bb_polls_show_poll_results_ajax();return false;}"  href="'.add_query_arg( 'show_poll_results', '1').'">'.$bb_polls['label_results_text'].'</a> )</p></form>';
$output.=bb_polls_edit_link();
$output=stripslashes($output);
if ($display) {echo '<li id="bb_polls">'.$output.'</li>';} else {return $output;}
}
}

function bb_polls_edit_link() {
global $bb_polls,$topic,$poll_options;
$administrator=bb_current_user_can('administrate');
if ($administrator) { 
$output= '<a onClick="return confirm('."'".$bb_polls['label_warning_text']."'".')"  href="'
	.add_query_arg('delete_poll','1',remove_query_arg(array('edit_poll','poll_question','show_poll_results','start_new_poll'))).'">'.$bb_polls['label_delete_text'].'</a>';
$output.=' | ';	
$output.= '<a onClick="if (window.bb_polls_insert_ajax) {bb_polls_edit_poll_ajax(); return false;}"  href="'
	.add_query_arg('edit_poll','1',remove_query_arg(array('poll_question','show_poll_results','start_new_poll'))).'">'.$bb_polls['label_edit_text'].'</a>';
return $output;
}
return '';
}

function bb_polls_delete_poll() {
$administrator=bb_current_user_can('administrate');
if ($administrator) { 
	$topic_id=bb_polls_check_cache($topic_id);		
	bb_delete_topicmeta($topic_id, 'poll_options');
}
}

function bb_polls_save_poll_setup($topic_id) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can('administrate') || bb_current_user_can($bb_polls['minimum_add_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
$poll_options['poll_question']=trim(substr(strip_tags(stripslashes($_POST['poll_question'])),0,$bb_polls['max_length']));
$poll_options['poll_multiple_choice']=intval($_POST['poll_multiple_choice']);
$options=0;
for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if ($test=trim(substr(strip_tags(stripslashes($_POST['poll_option_'.$i])),0,$bb_polls['max_length']))) {$options++; $poll_options[$options]=$test;}
} // loop
bb_update_topicmeta( $topic_id, 'poll_options', '..'.serialize($poll_options));  // save and trick bb_press to not deserialize unless necessary
// echo get_topic_id( $topic_id )." - ".$topic_id." : ".serialize($poll_options); exit();
}
}

function bb_polls_show_poll_setup_form($topic_id,$display=1,$edit_poll=0) {
global $bb_polls,$topic,$poll_options;
if (($edit_poll==0 && bb_current_user_can($bb_polls['minimum_add_level'])) || bb_current_user_can('administrate')) {
$topic_id=bb_polls_check_cache($topic_id);

$output='<form action="'.remove_query_arg(array('start_new_poll','edit_poll')).'" method="post"><p>'.$bb_polls['poll_instructions'].'</p>';
			
$output.='<p class=poll_label>'.$bb_polls['label_question_text'].' : <input name="poll_question" type=text style="width:98%" maxlength="'.$bb_polls['max_length'].'" value="'.$poll_options['poll_question'].'"></p>';
			
$output.='<p class=poll_label><span class=nowrap><input name="poll_multiple_choice" type="radio" value="0" ';
$output.=($poll_options['poll_multiple_choice']) ? ' ' : ' checked="checked" ';
$output.='>'.$bb_polls['label_single'].'</span> <span class=nowrap><input name="poll_multiple_choice" type="radio" value="1" ';
$output.=($poll_options['poll_multiple_choice']) ? ' checked="checked" ' : ' ';
$output.='> '.$bb_polls['label_multiple'].'</span></p>';
			
for ($i=1; $i<=$bb_polls['max_options']; $i++) {			
	if ($i==5 && $bb_polls['max_options']>4 && !$poll_options[5]) {	// more options input fields hidden until asked for
		$output.='<a href="javascript:void(0)" onClick="this.style.display='."'none'".'; document.getElementById('."'poll_more_options'".').style.display='."'block'".'">[+] '.$bb_polls['label_option_text'].'</a><div id="poll_more_options" style="display:none;">';
	}
	$output.='<p class=poll_label>'.$bb_polls['label_option_text'].' #'.$i.' : <input name="poll_option_'.$i.'" type=text style="width:98%" maxlength="'.$bb_polls['max_length'].'" value="'.$poll_options[$i].'"></p>';
} // loop 
if ($bb_polls['max_options']>4 && !$poll_options[5]) {$output.='</div>';}
		
$output.='<p class=poll_footer>
<input class=submit type=button  value="'.$bb_polls['label_cancel_text'].'" onClick="document.location='."'".remove_query_arg( 'start_new_poll')."'".'"> 
<input class=submit type=submit  value="'.$bb_polls['label_save_text'].'"></p></form>';
$output=stripslashes($output);if ($display) {echo '<li id="bb_polls" class="extra-caps-row">'.$output.'</li>';} else {return $output;}
}
}

function bb_polls_title( $title ) {
	global $bb_polls, $topic;
	if ($bb_polls['label_poll_text'] && isset($topic->poll_options) && !is_topic())  {return '['.$bb_polls['label_poll_text'].'] '.$title;}		
	return $title;
} add_filter('topic_title', 'bb_polls_title');

function bb_polls_add_header() { 
	if (isset($_POST['poll_question'])) {	// save new poll setup from _post data 
		bb_polls_save_poll_setup();				
		// header("HTTP/1.1 307 Temporary redirect");
		wp_redirect($GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
		// exit();  // not sure why but this makes it fail?
	}	
	if (isset($_POST['poll_vote'])) {	// save new poll vote from _post data 		  
		bb_polls_add_vote(bb_get_current_user_info( 'id' ),'');		
		// header("HTTP/1.1 307 Temporary redirect");
		wp_redirect($GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
		// exit();  // not sure why but this makes it fail?
	}
	if (isset($_GET['delete_poll']) && intval($_GET['delete_poll'])) { 	
		bb_polls_delete_poll();
		wp_redirect(remove_query_arg(array('start_new_poll','delete_poll')));	// I *really* don't like this technique but it's the only way to clear post data?
	}				
	if (isset($_GET['show_poll_results_ajax'])) {
		$topic_id=intval($_GET['show_poll_results_ajax']);
		header("Content-Type: application/x-javascript");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_results($topic_id,0)).'")';
		exit();
	}
	if (isset($_GET['show_poll_vote_form_ajax'])) {
		$topic_id=intval($_GET['show_poll_vote_form_ajax']);
		header("Content-Type: application/x-javascript");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_vote_form($topic_id,0)).'")';
		exit();
	}
	if (isset($_GET['add_vote_ajax'])) {
		$topic_id=intval($_GET['add_vote_ajax']);
		bb_polls_add_vote(bb_get_current_user_info( 'id' ),$topic_id);
		header("Content-Type: application/x-javascript");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_results($topic_id,0)).'")';
		exit();
	}			
} add_action('bb_send_headers', 'bb_polls_add_header');

function bb_polls_add_javascript($topic_id) {
global $bb_polls;
if ($bb_polls['use_ajax']) :
echo '<scr'.'ipt type="text/javascript" DEFER>
var dhead = document.getElementsByTagName("head")[0];
var bb_polls_script = null;
var bb_polls_htmldata = null;

function append_dhead(bb_polls_src) {
if (bb_polls_script) {dhead.removeChild(bb_polls_script);}
d = new Date();  bb_polls_src=bb_polls_src+"&"+d.getTime();
bb_polls_script = document.createElement("script");
bb_polls_script.src = bb_polls_src;
bb_polls_script.type = "text/javascript";
bb_polls_script.charset = "utf-8";
setTimeout("bb_polls_IE_fix()",20);
}
function bb_polls_IE_fix() {dhead.appendChild(bb_polls_script);}

function bb_polls_insert_ajax(htmldata) {
bb_polls_htmldata = unescape(htmldata);
setTimeout("bb_polls_insert_ajax_delayed()",20);
}
function bb_polls_insert_ajax_delayed() {document.getElementById("bb_polls").innerHTML=bb_polls_htmldata;}

function bb_polls_show_poll_results_ajax() {
append_dhead("'.add_query_arg( 'show_poll_results_ajax', get_topic_id( $topic_id )).'");
}

function bb_polls_show_poll_vote_form_ajax() {
var bb_polls_poll_vote_form = "'.mysql_escape_string(bb_polls_show_poll_vote_form(get_topic_id( $topic_id ),0)).'";
if (bb_polls_poll_vote_form) {bb_polls_insert_ajax(bb_polls_poll_vote_form);}
else {
append_dhead("'.add_query_arg( 'show_poll_vote_form_ajax', get_topic_id( $topic_id )).'");
}
}

function bb_polls_start_new_poll_ajax() {
var bb_polls_poll_setup_form = "'.mysql_escape_string(bb_polls_show_poll_setup_form(get_topic_id( $topic_id ),0,1)).'";
if (bb_polls_poll_setup_form) {bb_polls_insert_ajax(bb_polls_poll_setup_form);}
}

function bb_polls_edit_poll_ajax() {
var bb_polls_poll_setup_form = "'.mysql_escape_string(bb_polls_show_poll_setup_form(get_topic_id( $topic_id ),0,1)).'";
if (bb_polls_poll_setup_form) {bb_polls_insert_ajax(bb_polls_poll_setup_form);}
}

function bb_polls_add_vote_ajax() {
vote="";
if (document.bb_polls.poll_vote_0) {
for (i = 0; i < document.bb_polls.poll_vote_0.length; i++) {
if (document.bb_polls.poll_vote_0[i].checked) {vote=vote+"&poll_vote_0="+document.bb_polls.poll_vote_0[i].value; break;}
} }
for (i=1; i<='.$bb_polls['max_options'].'; i++) {
	test=eval("document.bb_polls.poll_vote_"+i);
	if (test && test.checked) {vote=vote+"&poll_vote_"+i+"="+i;}
}
if (vote.length) {append_dhead("'.add_query_arg( 'add_vote_ajax', get_topic_id( $topic_id )).'"+vote);}
else {alert("'.$bb_polls['label_nocheck_text'].'"); return false;}
}
</scr'.'ipt>';
endif;
}

function bb_polls_add_css() { 
global $bb_polls;
if (is_topic()) {echo '<style type="text/css">'.$bb_polls['style'].'</style>'; }
} add_action('bb_head', 'bb_polls_add_css');
?>