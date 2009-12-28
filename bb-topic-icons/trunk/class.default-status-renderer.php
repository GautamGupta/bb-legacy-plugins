<?php

class DefaultStatusRenderer implements StatusRenderer {
    public $status_map;
    
    function __construct() {
    	$this->status_map = array(
    		"normal" => "normal-post",
    		"hot" => "hot-post",
    		"sticky" => "sticky-post",
    		"closed" => "closed-post",
    	);
    }
    
    public function renderStatus($status) {
    	return $this->status_map[$status];
    }
}

?>
