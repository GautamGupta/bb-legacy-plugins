<?php

class SupportForumStatusInterpreter {

  public function getAllStatuses() {
    global $support_forum;
  	$statuses = array('normal', 'sticky', 'closed');
  	if(isset($support_forum)) {
  		$statuses[] = 'resolved';
  		$statuses[] = 'not-resolved';
  		$statuses[] = 'non-issue';
  	}
  	return $statuses;
  }

  public function getStatus($location, $topic) {
    global $support_forum;
    
		$support_forum_active = isset($support_forum);
		$current_forum_is_support_enabled = false;
		if ($support_forum_active) {
    	$enabled = bb_get_option('support_forum_enabled');
    	$current_forum_is_support_enabled = $support_forum_active && isset($enabled) && 
    		count($enabled) > 0 && in_array($topic->forum_id, $enabled);
  	}

    if (isset($topic->topic_resolved) && $current_forum_is_support_enabled) {
      return $this->resolve_support_status($topic->topic_resolved);
    }
  
    if ($this->is_closed_topic($topic)) {
        return "closed";
    }

    if ($this->is_sticky_topic($location, $topic)) {
        return "sticky";
    }
    
    if ($current_forum_is_support_enabled) {
      return $this->resolve_support_status(bb_get_option('support_forum_default_status'));
    }

    return "normal";
  }
    
  private function resolve_support_status($status) {
    if ("yes" == $status) {
      return 'resolved';
    } else if ("no" == $status) {
      return 'not-resolved';
    } else if ("mu" == $status) {
      return 'non-issue';
  	}

    return $status;
  }

  private function is_sticky_topic($location, $topic) {
    return ('front-page' == $location) ? ( '2' === $topic->topic_sticky ) :
      ( '1' === $topic->topic_sticky || '2' === $topic->topic_sticky );
  }

  private function is_closed_topic($topic) {
    return ( '0' === $topic->topic_open );
  }
}

?>
