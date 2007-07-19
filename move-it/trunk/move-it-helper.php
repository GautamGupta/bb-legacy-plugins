<?php  // version 0.14
require('./bb-load.php');

if(bb_current_user_can( 'moderate' )) :    

$topics = $bbdb->get_results("SELECT topic_id,topic_title,forum_id FROM $bbdb->topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT 700");
foreach ($topics as $topic) {
echo  "moveit_add('".$topic->topic_id."','".addslashes(substr(get_forum_name($topic->forum_id).": ".$topic->topic_title,0,80))."');";
}

endif;
?>
