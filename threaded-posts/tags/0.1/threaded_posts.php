<?php
/*
Plugin Name: Threaded posts
Description: Display posts as threads
Author: John Ferlito
Author URI: http://inodes.org/blog
Version: 0.1
*/

/*  Copyright 2008  John Ferlito  (email : johnf@inodes.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/* Create the thread tracking table */
function threaded_posts_activation() {
    global $bbdb, $bb_table_prefix;
    system("echo $installed > /tmp/jf_res");

    $bbdb->hide_errors();
    $installed = $bbdb->get_results("SHOW TABLES LIKE '".$bb_table_prefix."post_parent'");

    if ( !$installed ) {
        $bbdb->query("
            CREATE TABLE IF NOT EXISTS ".$bb_table_prefix."post_parent (
                `post_id` bigint(20) NOT NULL,
                `post_parent_id` bigint(20) NOT NULL,
                UNIQUE KEY id (post_id, post_parent_id)
            )
    ");
    }
    $bbdb->show_errors();
}
bb_register_plugin_activation_hook(__FILE__, 'threaded_posts_activation');
/* The above doesn't seem to work */
add_action('bb_activate_plugin_user#threaded_posts/threaded_posts.php', 'threaded_posts_activation');

function threaded_posts_filter_template ($filename, $template) {
    if ($template == 'post.php') {
        $filename = str_replace('post.php', 'post-threaded.php', $filename);

        if ( file_exists($filename) ) {
            return $filename;
        }
        else {
            return BB_PLUGIN_DIR . '/threaded_posts/post-threaded.php';
        }
    }

    return $filename;
}
add_filter('bb_template', 'threaded_posts_filter_template', 10, 2);

function threaded_posts_topic_js() {
    include(BB_PLUGIN_DIR . '/threaded_posts/reply_js.php');
}
add_action('bb_topic.php', 'threaded_posts_topic_js');

function threaded_posts_add_parent() {
    include(BB_PLUGIN_DIR . '/threaded_posts/hidden_parent.php');
}
add_action('post_form_pre_post', 'threaded_posts_add_parent');

function threaded_posts_add_post_parent($post_id) {
    global $bbdb, $_POST, $bb_table_prefix;

    // There are two bb_post.php actions, one for adding a post/reply which
    // is the on we want and the other for the post template
    // The template one doesn't set an id
    if (!$post_id) {
        return;
    }

    $post_parent_id = $_POST['post_parent_id'];

    if (!($post_parent_id > 1)) {
        return;
    }

    $defaults = array(
        'post_id'        => $post_id,
        'post_parent_id' => $post_parent_id
    );
    $fields = array_keys($defaults);
    $moo = $bbdb->insert( $bb_table_prefix .  'post_parent', compact( $fields ) );
}
add_action('bb_post.php', 'threaded_posts_add_post_parent');

function threaded_posts_post_children($post_id = 0) {
    global $bbdb, $bb_table_prefix;;

    $post_id = get_post_id( $post_id );

    #TODO This is DB inefficient we should probably do this the first time we grab the posts
    $children = $bbdb->get_results("
        SELECT
            *
        FROM
            $bbdb->posts,
            ".$bb_table_prefix."post_parent
        WHERE
            ".$bb_table_prefix."post_parent.post_parent_id = $post_id
            AND
            $bbdb->posts.post_id = ".$bb_table_prefix."post_parent.post_id
        ORDER BY
            $bbdb->posts.post_time
        ;
     ");

     return $children;
}



function bb_rating_stylesheet() {
    echo "<link rel='stylesheet' href='" . bb_path_to_url( BB_PLUGIN_DIR . '/threaded_posts/threaded_posts.css' ) . "' type='text/css' />\n";
}
add_action( 'bb_head', 'bb_rating_stylesheet' );

function no_child_posts($query) {
    # FIXME: This is a horrible horrible hack
//        print "<pre>" . $query . "</pre>";
    if  (preg_match('/SELECT *p\.\* FROM bb_posts AS p *WHERE p\.topic_id = \'([0-9]+)\' AND p.post_status = \'([0-9]+)\' *ORDER BY p\.post_time ASC LIMIT [0-9]+/', $query, $match)) {
        $query = "
            SELECT
                p.*
            FROM
            bb_posts AS p
            LEFT JOIN
                bb_post_parent AS pp
            ON
                pp.post_id = p.post_id
            WHERE
                p.topic_id = $match[1]
                AND
                p.post_status = $match[2]
                AND
                pp.post_id is NULL
            ORDER BY
                p.post_time ASC
                ";
 //       print "<pre>" . $query . "</pre>";
    }

    return $query;
}
add_filter('query', 'no_child_posts', 10, 1);

?>
