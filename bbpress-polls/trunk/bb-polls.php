<?php
/*
Plugin Name: bbPress Polls
Description:  allows users to add polls to topics, with optional ajax-like actions
Plugin URI:  http://bbpress.org/plugins/topic/62
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.51

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions:   install, activate, check admin menu for options.

To Do: 
	* polls should be able to close with topic
	* allow results to display by number of votes
	* display a poll anywhere within bbpress templates
	* display all polls on a single page
	* better editing / vote count editing 
	* see who voted
	* better poll styles (colors / graphics)
*/

global $bb_polls;

add_action( 'bb_send_headers', 'bb_polls_initialize');	// bb_init
add_action( 'bb_admin-header.php','bb_polls_process_post');
add_action( 'bb_admin_menu_generator', 'bb_polls_add_admin_page' );

function bb_polls_add_admin_page() {bb_admin_add_submenu(__('bbPress Polls'), 'administrate', 'bb_polls_admin_page');}

function bb_polls_initialize() {
	global $bb_polls, $bb_polls_type, $bb_polls_label;
	if (!isset($bb_polls)) {$bb_polls = bb_get_option('bb_polls');
		if (!$bb_polls) {

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

	$bb_polls['style']=
	"#bb_polls {list-style: none; width:350px; line-height:120%; margin:5px 0; padding:5px; border:1px solid #ADADAD;  font-size:85%; color:#000; background:#eee; }
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
	$bb_polls['label_delete_text']=__("Delete");  // default "DELETE" = text to show for Delete button
	$bb_polls['label_option_text']=__("option");  // default "option" = text to show for options
	$bb_polls['label_question_text']=__("poll question");
	$bb_polls['label_results_text']=__("show poll results");
	$bb_polls['label_now_text']=__("vote now");	
	$bb_polls['label_nocheck_text']=__("You haven't selected anything!");	
	$bb_polls['label_warning_text']=__("This cannot be undone. Are you sure to delete?");

		}}						
	// if (BB_IS_ADMIN) {		// doesn't exist until 1040 :-(
	
	$bb_polls_type['minimum_view_level']="read,participate,moderate,administrate"; 
	$bb_polls_type['minimum_vote_level']="participate,moderate,administrate";
	$bb_polls_type['minimum_add_level']="participate,moderate,administrate";  
	$bb_polls_type['minimum_edit_level']="participate,moderate,administrate";
	$bb_polls_type['minimum_delete_level']="participate,moderate,administrate";
	
	$bb_polls_type['only_topic_author_can_add']="binary";
	$bb_polls_type['show_poll_on_which_pages']="first,last,both,all";	
		
	$bb_polls_type['add_within_hours']="1,2,6,12,24,48,72,999999";
	$bb_polls_type['edit_within_hours']="1,2,6,12,24,48,72,999999";

	$bb_polls_type['close_with_topic']="binary";
	$bb_polls_type['close_after_days']="1,2,7,30,365";

	$bb_polls_type['max_options']="3,5,9,15,20";
	$bb_polls_type['max_length']="50,100,200";
	$bb_polls_type['options_sort']="binary";
	
	$bb_polls_type['use_ajax']="binary";
	$bb_polls_type['test_mode']="binary";

	$bb_polls_type['style']="textarea";
						
	$bb_polls_type['poll_question']="text";
	$bb_polls_type['poll_instructions']="text";
	$bb_polls_type['label_single']="text";
	$bb_polls_type['label_multiple']="text";
	$bb_polls_type['label_poll_text']="text";
	$bb_polls_type['label_votes_text']="text";
	$bb_polls_type['label_vote_text']="text";
	$bb_polls_type['label_save_text']="text";
	$bb_polls_type['label_cancel_text']="text";
	$bb_polls_type['label_edit_text']="text";
	$bb_polls_type['label_delete_text']="text";
	$bb_polls_type['label_option_text']="text";
	$bb_polls_type['label_question_text']="text";
	$bb_polls_type['label_results_text']="text";
	$bb_polls_type['label_now_text']="text";
	$bb_polls_type['label_nocheck_text']="text";
	$bb_polls_type['label_warning_text']="text";
	
	
	$bb_polls_label['minimum_view_level']=__("At what level can users SEE polls?");
	$bb_polls_label['minimum_vote_level']=__("At what level can users VOTE on polls?");
	$bb_polls_label['minimum_add_level']=__("At what level can users ADD a poll?");
	$bb_polls_label['minimum_edit_level']=__("At what level can users EDIT a poll?");
	$bb_polls_label['minimum_delete_level']=__("At what level can users DELETE a poll?");

	$bb_polls_label['only_topic_author_can_add']=__("Only the topic starter can add a poll?");
	$bb_polls_label['show_poll_on_which_pages']=__("Show poll only on which topic pages?");
		
	$bb_polls_label['add_within_hours']=__("How many hours later can a poll be ADDED?");
	$bb_polls_label['edit_within_hours']=__("How many hours later can a poll be EDITED?");

	$bb_polls_label['close_with_topic']=__("Should polls close when a topic is closed?");
	$bb_polls_label['close_after_days']=__("If not closed with topic, after how many days?");

	$bb_polls_label['max_options']=__("How many poll question slots should be offered?");
	$bb_polls_label['max_length']=__("How many characters can the poll questions be?");
	$bb_polls_label['options_sort']=__("Sort results by number of votes?");
	
	$bb_polls_label['use_ajax']=__("Use AJAX-like actions if javascript enabled?");
	$bb_polls_label['test_mode']=__("Enable TEST MODE (multiple votes per person)?");

	$bb_polls_label['style']=__("Custom CSS style for polls:");

	$bb_polls_label['poll_question']=__("Question to ask to start poll:");
	$bb_polls_label['poll_instructions']=__("Instructions to add poll:");
	$bb_polls_label['label_single']=__("Label for single vote selections:");
	$bb_polls_label['label_multiple']=__("Label for multiple vote selections:");
	$bb_polls_label['label_poll_text']=__("Text to show if a topic title has a poll:");
	$bb_polls_label['label_votes_text']=__("Text to show for votes:");
	$bb_polls_label['label_vote_text']=__("Text to show for VOTE button:");
	$bb_polls_label['label_save_text']=__("Text to show for SAVE button:");
	$bb_polls_label['label_cancel_text']=__("Text to show for CANCEL button:");
	$bb_polls_label['label_edit_text']=__("Text to show for EDIT button:");
	$bb_polls_label['label_delete_text']=__("Text to show for DELETE button:");
	$bb_polls_label['label_option_text']=__("Text to show for each option:");
	$bb_polls_label['label_question_text']=__("Text to show for question label:");
	$bb_polls_label['label_results_text']=__("Text to show for results label:");
	$bb_polls_label['label_now_text']=__("Text to show for VOTE NOW label:");
	$bb_polls_label['label_nocheck_text']=__("No selection warning:");
	$bb_polls_label['label_warning_text']=__("Delete warning:");

	// }	

	bb_polls_add_header(); 	// add_action('bb_send_headers', 'bb_polls_add_header');
	add_action('bb_head', 'bb_polls_add_css');
	add_filter('topic_title', 'bb_polls_title');
	add_action('topicmeta','bb_polls_pre_poll',200);
}

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
} 

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

function bb_polls_save_poll_setup($topic_id=0) {
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
} 

function bb_polls_add_header() { 
	if (isset($_POST['poll_question'])) {	// save new poll setup from _post data 
		bb_polls_save_poll_setup(0);				
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
} 

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
} 

function bb_polls_admin_page() {
	global $bb_polls, $bb_polls_type, $bb_polls_label;			
	?>
		<div style="text-align:right;margin-bottom:-1.5em;">			
			[ <a href="<?php echo add_query_arg('bb_polls_reset','1',remove_query_arg('bb_polls_recount')); ?>">Reset All Settings To Defaults</a> ] 			
		</div>
		
		<h2>bbPress Polls</h2>
		
		<form method="post" name="bb_polls_form" id="bb_polls_form" action="<?php echo remove_query_arg(array('bb_polls_reset','bb_polls_recount')); ?>">
		<input type=hidden name="bb_polls" value="1">
			<table class="widefat">
				<thead>
					<tr> <th width="33%">Option</th>	<th>Setting</th> </tr>
				</thead>
				<tbody>
					<?php
					
					foreach(array_keys( $bb_polls_type) as $key) {
					
					// if ($key=="style") {echo "<div id='bb_polls_rollup' style='display:none;'>";}
					
					$bb_polls[$key]=stripslashes_deep($bb_polls[$key]);					
					$colspan= (substr($bb_polls_type[$key],0,strpos($bb_polls_type[$key].",",","))=="array") ? "2" : "1";
						?>
						<tr <?php alt_class('recount'); ?>>
							<td nowrap colspan=<?php echo $colspan; ?>>
							<label for="bb_polls_<?php echo $key; ?>">
							<b><?php  if ($bb_polls_label[$key])  {echo $bb_polls_label[$key];} else {echo ucwords(str_replace("_"," ",$key));} ?></b>
							</label>
							<?php
							if ($colspan<2) {echo "</td><td>";} else {echo "<br />";}
							switch (substr($bb_polls_type[$key],0,strpos($bb_polls_type[$key].",",","))) :
							case 'binary' :
								?><input type=radio name="<?php echo $key;  ?>" value="1" <?php echo ($bb_polls[$key]==true ? 'checked="checked"' : ''); ?> >Yes 									&nbsp; 
								     <input type=radio name="<?php echo $key;  ?>" value="0" <?php echo ($bb_polls[$key]==false ? 'checked="checked"' : ''); ?> >No <?php
							break;
							case 'numeric' :
								?><input type=text maxlength=3 name="<?php echo $key;  ?>" value="<?php echo $bb_polls[$key]; ?>"> <?php 
							break;
							case 'textarea' :								
								?><textarea rows="9" style="width:98%" name="<?php echo $key;  ?>"><?php echo $bb_polls[$key]; ?></textarea><?php 							
							break;
							default :  // type "input" and everything else we forgot
								$values=explode(",",$bb_polls_type[$key]);
								if (count($values)>2) {
								echo '<select name="'.$key.'">';
								foreach ($values as $value) {echo '<option '; echo ($bb_polls[$key]== $value ? 'selected' : ''); echo '>'.$value.'</option>'; }
								echo '</select>';
								} else {														
								?><input type=text style="width:98%" name="<?php echo $key;  ?>" value="<?php echo $bb_polls[$key]; ?>"> <?php 
								}
							endswitch;							
							?>
							</td>
						</tr>
						<?php
					} 
					// echo "</div>";
					?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" value="Save bbPress Polls Settings"></p>
		
		</form>
		<?php
}

function bb_polls_process_post() {
global $bb_polls;
	if (bb_current_user_can('administrate')) {
		if (isset($_REQUEST['bb_polls_reset'])) {
			unset($bb_polls); 		
			bb_delete_option('bb_polls');
			bb_polls_initialize();			
			bb_update_option('bb_polls',$bb_polls);
			bb_admin_notice('<b>bbPress Polls: '.__('All Settings Reset To Defaults.').'</b>'); 	// , 'error' 			
			wp_redirect(remove_query_arg(array('bb_polls_reset')));	// bug workaround, page doesn't show reset settings
		}		
		elseif (isset($_POST['submit']) && isset($_POST['bb_polls'])) {
							
			foreach(array_keys( $bb_polls) as $key) {
				if (isset($_POST[$key])) {$bb_polls[$key]=$_POST[$key];}
			}
		
			bb_update_option('bb_polls',$bb_polls);
			bb_admin_notice('<b>bbPress Polls: '.__('All Settings Saved.').'</b>');
			// unset($GLOBALS['bb_polls']); $bb_polls = bb_get_option('bb_polls');
		}
	}
}

?>