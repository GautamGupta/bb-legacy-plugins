<?php
/*
Plugin Name: bbPress Polls
Description:  allows users to add polls to topics
Plugin URI:  http://CKon.wordpress.com
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.20
*/
/*
License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions:   install, activate, tinker with settings
-=notice=- default settings are now set to everyone can view, any member can vote, only moderator+ can add

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
	
To Do: 
	: fix post data refresh issue
	: admin menu (coming soon - edit plugin directly)
	: administrative editing of existing polls.
	: multi-language support 
	: display all polls on a single page
	: display a poll anywhere within bbpress templates
	: ajax-fy  - don't hold your breath, I am not a big fan of ajax - would need some help
*/
	$bb_polls['minimum_view_level']="read";   // who can view polls = read / participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_vote_level']="participate";   // who can vote on polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_add_level']="moderate";   // who can add polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_edit_level']="administrate";   // who can edit polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['only_topic_author_can_add']=true;   // false=anyone can add a poll to any topic /  true=only the topic starter
	$bb_polls['show_poll_on_which_pages']="both";    // show poll only on  first / last / both / all
		
	$bb_polls['add_within_hours']=3;   // how many hours later can a poll be added 
	$bb_polls['edit_within_hours']=12;   // how many hours later can poll be edited	(for users/moderator - admin can always edit)		

	$bb_polls['close_with_topic']=true;   // if topic is closed, is poll closed?		
	$bb_polls['close_after_days']=365;   // if not closed with topic, close after how many days?				

	$bb_polls['max_options']=9;     // sanity 		
	$bb_polls['max_length']=100;     // sanity
	$bb_polls['options_sort']=false;     // true=show options by most votes, false=show options in original order
	$bb_polls['use_ajax']=true;		// enabled ajax-ish behaviours
				
	$bb_polls['poll_question']=__("Would you like to add a poll to this topic for members to vote on?");
	$bb_polls['poll_instructions']=__("You may submit a poll question with several options for other members to vote from.");
	$bb_polls['label_single']=__("you can vote on <u>ONE</u> choice");
	$bb_polls['label_multiple']=__("you can vote on <u>MULTIPLE</u> choices");
	$bb_polls['label_poll_text']=__("poll");    // default "poll" = text to show if on topic title if it has a poll (delete text to disable) // you can even use HTML/CSS
	$bb_polls['label_votes_text']=__("votes");  // default "votes" = text to show for votes
	$bb_polls['label_vote_text']=__("VOTE NOW");  // default "VOTE" = text to show for VOTE button
	$bb_polls['label_save_text']=__("SAVE POLL");  // default "SAVE" = text to show for SAVE button
	$bb_polls['label_option_text']=__("option");  // default "option" = text to show for options
	$bb_polls['label_question_text']=__("poll question");
	$bb_polls['label_results_text']=__("show poll results");
	$bb_polls['label_now_text']=__("vote now");	
		
	$bb_polls['style']="#bb_polls {width:340px; clear:both; margin:auto -10px; padding:5px; border:1px solid #ADADAD;  font-size:85%; color:#000; background:#eee; }
			#bb_polls .submit {cursor: pointer; cursor: hand;}
			#bb_polls .nowrap {white-space:nowrap;}
			#bb_polls .poll_question, #bb_polls .poll_footer {font-weight:bold; text-align:center; color:#2E6E15;}
			#bb_polls .poll_label {font-weight:bold;}								
			#bb_polls .poll_option {text-align:center; font-size:0.5em; line-height:0.7em; padding:1px; margin:-10px 0 5px 0; border:1px solid #777; color:#eee; font-weight:bold;}
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

function bb_polls_pre_poll($topic_id) {
global $bb_polls,$topic,$poll_options,$page;
if ($bb_polls['minimum_view_level']=="read" || bb_current_user_can($bb_polls['minimum_view_level']) ) :   
$topic_id=bb_polls_check_cache($topic_id);
$user_id=bb_get_current_user_info( 'id' );
bb_polls_add_javascript($topic_id);	// ajax-ish
if ( ! $topic->poll_options) {	// no saved poll question with options

	if ( bb_current_user_can($bb_polls['minimum_add_level'])  &&  ! ( $bb_polls['only_topic_author_can_add'] && $topic->topic_poster!=$user_id)) {	// 1
	if (! ( $bb_polls['add_within_hours'] &&  $bb_polls['add_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600)) {	// 2
	if (! ($bb_polls['close_with_topic'] && $topic->topic_open!=1 )) {	// 3
	
		if ($_POST['poll_question']) {	// save new poll setup from _post data 
				bb_polls_save_poll_setup($topic_id);						
				bb_polls_show_poll_vote_form($topic_id);
		} else {
 			if ($_GET['start_new_poll']) { 
 				bb_polls_show_poll_setup_form($topic_id); 
 		} else {	
			// ask if they want to start a new poll
				echo '<div id="bb_polls"><a onClick="bb_polls_start_new_poll_ajax();return false;" href="'.add_query_arg( 'start_new_poll', '1').'">'.$bb_polls['poll_question'].'</a></div>'; 
		
		} }	// end new poll question + end show start_new_poll form 

	} } }  // 1 2 3  checks for allowed settings to start/edit poll 

} else {		// there's a saved poll question with options

	if ($_POST['poll_vote']) {	// save new poll vote from _post data 		  
			bb_polls_add_vote($user_id,$topic_id);			
			bb_polls_show_poll_results($topic_id);
	} else {
		if ($_GET['show_poll_results']) {	// override to show poll results
			bb_polls_show_poll_results($topic_id); 
	} else {	 // obey per page setting		
		if ( $bb_polls['show_poll_on_which_pages']=="all" 
		||  ($page==1 && ($bb_polls['show_poll_on_which_pages']=="first" || $bb_polls['show_poll_on_which_pages']=="both" )) 
		|| ($page==get_page_number( $topic->topic_posts ) && ($bb_polls['show_poll_on_which_pages']=="last" || $bb_polls['show_poll_on_which_pages']=="both" )) 
		) { 	
			if (bb_polls_has_voted($user_id,$topic_id)) {
		 		bb_polls_show_poll_results($topic_id); 	// they voted, show results
			} else {		 
				bb_polls_show_poll_vote_form($topic_id);	// let them vote
	} } } }		
}
endif;	
} add_action('topicmeta','bb_polls_pre_poll');

function bb_polls_check_cache($topic_id) {
global $bb_polls,$topic,$poll_options;  
if (!$topic_id || $topic_id != $topic->topic_id) {$topic_id=get_topic_id( $topic_id );  $topic = get_topic($topic_id); $poll_options=$topic->poll_options; }
if (isset($poll_options) && !is_array($poll_options)) {$poll_options=unserialize(substr($poll_options,2));}   // trick bb_press to keep poll data unserialized
return $topic_id;
}

function bb_polls_has_voted($user_id,$topic_id) {    // return false;  // for testing only
global $bb_polls,$topic,$poll_options;
$topic_id=bb_polls_check_cache($topic_id);
if (!$user_id) {$user_id=bb_get_current_user_info( 'id' );}
for ($i=1; $i<=$bb_polls['max_options']; $i++) {$votes.=",".$poll_options['poll_vote_'.$i].",";}
return (strpos($votes,",".$user_id.",")===false) ? false : true;
}

function bb_polls_add_vote($user_id,$topic_id) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_vote_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
$voted_flag=false; 
if (!bb_polls_has_voted($user_id,$topic_id)) {		
	for ($i=0; $i<=$bb_polls['max_options']; $i++) {	
		if ($test=$_POST['poll_vote_'.$i]) {				
			if (!$poll_options['poll_count_'.$test]) {	// initialise counters					
				$poll_options['poll_count_'.$test]=0; 
				if (!$poll_options['poll_count_0']) {$poll_options['poll_count_0']=0;} 
			} else { $poll_options['poll_vote_'.$test].=",";}							
			$poll_options['poll_vote_'.$test].=$user_id;	// add user's vote, single or multiple
			$poll_options['poll_count_'.$test]++;  		// update count for option
			$voted_flag=true;					// set flag to update overall count			
		}			
	}		
	if ($voted_flag) {$poll_options['poll_count_0']++;}  		// update count for overall	
	bb_update_topicmeta( $topic_id, 'poll_options', '..'.serialize($poll_options));  // save and trick bb_press to not deserialize unless necessary
	return true;
}  
else {return false;}   // has voted already
}
}

function bb_polls_show_poll_results($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
if ($bb_polls['minimum_view_level']=="read" || bb_current_user_can($bb_polls['minimum_view_level']) ) {
$topic_id=bb_polls_check_cache($topic_id);
$output='<p class=poll_question>'.$bb_polls['label_poll_text'].': '.$poll_options['poll_question'].'</p>';

for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if ($test=$poll_options[$i]) { 		
		$output.= '<p class=poll_label>'.$test.' : ';	
		$test=intval($poll_options['poll_count_'.$i]);	
		$output.= ' ('.$test.' '.$bb_polls['label_votes_text'].')';
		if ($test) {
			$vote_percent=(round($test/$poll_options['poll_count_0'],2)*100);
			$vote_width=$vote_percent; if ($vote_width < 5) {$vote_width=5;} else {if ($vote_width >98 ) {$vote_width=98;}}
			$output.= ' <div style="width:'.$vote_width.'%"class="poll_option poll_option'.$i.'"> '.$vote_percent.' % </div> ';
		}
		$output.= ' </p>';
	}
}
$output.= '<p class=poll_footer>'.intval($poll_options['poll_count_0']).' '.$bb_polls['label_votes_text'].'</p>';
if ($_GET['show_poll_results'] || (bb_get_current_user_info( 'id' ) && !bb_polls_has_voted(bb_get_current_user_info( 'id' ),$topic_id) )) {
$output.= '<p class=poll_footer>( <a onClick="bb_polls_show_poll_vote_form_ajax();return false;" href="'.remove_query_arg( 'show_poll_results').'">'.$bb_polls['label_now_text'].'</a> )</p>';
}
if ($display) {echo '<div id="bb_polls">'.$output.'</div>';} else {return $output;}
}
}

function bb_polls_show_poll_vote_form($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_vote_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
if ($poll_options['poll_multiple_choice']==1) {$poll_type="checkbox";} else {$poll_type="radio";}
$output='<form action="'.remove_query_arg( 'show_poll_results').'" method="post" name="bb_polls">
	 <p class=poll_question>'.$bb_polls['label_poll_text'].': '.$poll_options['poll_question'].'</p>';
for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if ($test=$poll_options[$i]) {
		if ($poll_options['poll_multiple_choice']==1) {$poll_name="poll_vote_".$i;} else {$poll_name="poll_vote_0";}
		$output.= '<p><input type="'.$poll_type.'" name="'.$poll_name.'" value="'.$i.'"> '.$test.' </p>';
	}
}
$output.= '<p class=poll_footer><input class=submit type=submit  name="poll_vote" value="'.$bb_polls['label_vote_text'].'"></p>
	<p class=poll_footer>( <a onClick="bb_polls_show_poll_results_ajax();return false;"  href="'.add_query_arg( 'show_poll_results', '1').'">'.$bb_polls['label_results_text'].'</a> )</p></form>';
if ($display) {echo '<div id="bb_polls">'.$output.'</div>';} else {return $output;}
}
}

function bb_polls_save_poll_setup($topic_id) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_add_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
$poll_options['poll_question']=$_POST['poll_question'];
$poll_options['poll_multiple_choice']=$_POST['poll_multiple_choice'];
$options=0;
for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if ($test=trim(substr($_POST['poll_option_'.$i],0,$bb_polls['max_length']))) {$options++; $poll_options[$options]=$test;}
} // loop
bb_update_topicmeta( $topic_id, 'poll_options', '..'.serialize($poll_options));  // save and trick bb_press to not deserialize unless necessary
}
}

function bb_polls_show_poll_setup_form($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_add_level'])) {
$topic_id=bb_polls_check_cache($topic_id);

$output='<form action="'.remove_query_arg( 'start_new_poll').'" method="post"><p>'.$bb_polls['poll_instructions'].'</p>';
			
$output.='<p class=poll_label>'.$bb_polls['label_question_text'].' : <input name="poll_question" type=text style="width:98%" maxlength="'.$bb_polls['max_length'].'" value=""></p>';
			
$output.='<p class=poll_label><span class=nowrap><input name="poll_multiple_choice" type="radio" value="0" checked> '.$bb_polls['label_single'].'</span> &nbsp; <span class=nowrap><input name="poll_multiple_choice" type="radio" value="1"> '.$bb_polls['label_multiple'].'</span></p>';
			
for ($i=1; $i<=$bb_polls['max_options']; $i++) {			
	if ($i==5 && $bb_polls['max_options']>5) {	// more options input fields hidden until asked for
		$output.='<a href="javascript:void(0)" onclick="this.style.display='."'none'".'; document.getElementById('."'poll_more_options'".').style.display='."'block'".'">[+] '.$bb_polls['label_option_text'].'</a><div id="poll_more_options" style="display:none;">';
	}
	$output.='<p class=poll_label>'.$bb_polls['label_option_text'].' #'.$i.' : <input name="poll_option_'.$i.'" type=text style="width:98%" maxlength="'.$bb_polls['max_length'].'" value=""></p>';
} // loop 
if ($bb_polls['max_options']>5) {$output.='</div>';}
		
$output.='<p class=poll_footer><input class=submit type=submit  value="'.$bb_polls['label_save_text'].'"></p></form>';
if ($display) {echo '<div id="bb_polls" class="extra-caps-row">'.$output.'</div>';} else {return $output;}
}
}

function bb_polls_add_header() { 
	if ($_POST['poll_question']) {	// save new poll setup from _post data 
		bb_polls_save_poll_setup('');				
		wp_redirect($GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
	}
	if ($_POST['poll_vote']) {	// save new poll vote from _post data 		  
		bb_polls_add_vote(bb_get_current_user_info( 'id' ),'');		
		wp_redirect($GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
	}			
	if ($topic_id=$_GET['show_poll_results_ajax']) {
		echo 'document.getElementById("bb_polls").innerHTML="'.mysql_real_escape_string(bb_polls_show_poll_results($topic_id,0)).'"';
		exit();
	}
	if ($topic_id=$_GET['show_poll_vote_form_ajax']) {
		echo 'document.getElementById("bb_polls").innerHTML="'.mysql_real_escape_string(bb_polls_show_poll_vote_form($topic_id,0)).'"';
		exit();
	}
			
} add_action('bb_send_headers', 'bb_polls_add_header');

function bb_polls_add_css() { 
global $bb_polls;
if (is_topic()) {echo '<style type="text/css">'.$bb_polls['style'].'</style>'; }
} add_action('bb_head', 'bb_polls_add_css');

function bb_polls_title( $title ) {
	global $bb_polls, $topic;
	if ($bb_polls['label_poll_text'] && $topic->poll_options && !is_topic())  {return '['.$bb_polls['label_poll_text'].'] '.$title;}		
	return $title;
} add_filter('topic_title', 'bb_polls_title');


function bb_polls_add_javascript($topic_id) {
global $bb_polls;
if ($bb_polls['use_ajax']) {
echo '<scr'.'ipt type="text/javascript">
function bb_polls_show_poll_results_ajax() {
// if(!bb_polls_script.src) {
var bb_polls_script1 = document.createElement("script");
bb_polls_script1.src = "'.add_query_arg( 'show_poll_results_ajax', get_topic_id( $topic_id )).'";
bb_polls_script1.type = "text/javascript";
bb_polls_script1.charset = "utf-8";
document.getElementsByTagName("head")[0].appendChild(bb_polls_script1);
// }
return false;
}

function bb_polls_show_poll_vote_form_ajax() {
// if(!bb_polls_script.src) {
var bb_polls_poll_vote_form = "'.mysql_real_escape_string(bb_polls_show_poll_vote_form(get_topic_id( $topic_id ),0)).'";
if (bb_polls_poll_vote_form) {document.getElementById("bb_polls").innerHTML=bb_polls_poll_vote_form;}
else {
var bb_polls_script2 = document.createElement("script");
bb_polls_script2.src = "'.add_query_arg( 'show_poll_vote_form_ajax', get_topic_id( $topic_id )).'";
bb_polls_script2.type = "text/javascript";
bb_polls_script2.charset = "utf-8";
document.getElementsByTagName("head")[0].appendChild(bb_polls_script2);
// }
}
return false;
}

function bb_polls_start_new_poll_ajax() {
var bb_polls_poll_setup_form = "'.mysql_real_escape_string(bb_polls_show_poll_setup_form(get_topic_id( $topic_id ),0)).'";
if (bb_polls_poll_setup_form) {document.getElementById("bb_polls").innerHTML=bb_polls_poll_setup_form;}
}

function bb_polls_add(add_value,add_text) {     	                 
newOption = document.createElement("option");                
newOption.text = add_text;
newOption.value = add_value;
selectElement=document.getElementById("toid");
try {selectElement.add(newOption,null);}
catch (e) {selectElement.add(newOption,selectElement.length);}
}
</scr'.'ipt>';
}
}

?>