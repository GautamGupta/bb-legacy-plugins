<?php

status_header( 200 );

// Plugin compatibility
remove_filter('topic_title', 'utplugin_show_unread');
remove_filter('topic_link', 'utplugin_link_latest');

global $bbpm;

$url    = array_values( array_filter( explode( '/', substr( rtrim( $_SERVER['REQUEST_URI'], '/' ), strlen( bb_get_option( 'path' ) . 'pm' ) ) ) ) );
$get    = $url[0];
$action = $url[1];
$args   = array_slice( $url, 2 );

bb_enqueue_script( 'jquery' );

bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; 

<?php switch ( $get ) {
	case 'new': ?><a href="<?php echo $bbpm->get_link(); ?>"><?php _e( 'Private Messages', 'bbpm' ); ?></a> &raquo; <?php _e( 'New', 'bbpm' ); ?></h3>
<form class="postform pm-form" method="post" action="<?php echo BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/pm.php'; ?>">
<fieldset>
<p>
	<label for="title"><?php _e( 'PM title: (be brief and descriptive)', 'bbpm' ); ?><br/></label>
	<input name="title" type="text" id="title" size="50" maxlength="250" tabindex="1" />
</p>
<p>
	<label for="to"><?php _e( 'Send PM to:', 'bbpm' ); ?><br/></label>
	<input name="to" type="text" id="to" size="50" maxlength="100" tabindex="2"<?php if ( $action ) echo ' value="' . urldecode( $action ) . '"'; ?> />
</p>
<?php do_action( 'post_form_pre_post' ); ?>
<p>
	<label for="message"><?php _e( 'Message:', 'bbpm' ); ?><br/></label>
	<textarea name="message" cols="50" rows="8" id="message" tabindex="3"></textarea>
</p>
<p class="submit">
  <input type="submit" id="postformsub" name="Submit" value="<?php echo attribute_escape( __( 'Send Message &raquo;', 'bbpm' ) ); ?>" tabindex="4" />
</p>

<p><?php _e('Allowed markup:'); ?> <code><?php allowed_markup(); ?></code>. <br /><?php _e('You can also put code in between backtick ( <code>`</code> ) characters.'); ?></p>

<?php bb_nonce_field( 'bbpm-new' ); ?>

</fieldset>
</form>

<?php break;
	default:
	if ( !(int)$get || !$bbpm->can_read_message( $get ) ) {
_e( 'Private Messages', 'bbpm' ); ?></h3>

<h2><?php _e('Inbox', 'bbpm'); ?></h2>
<table id="latest">
<tr>
	<th><?php _e('Subject', 'bbpm'); ?> &#8212; <a href="<?php $bbpm->new_pm_link(); ?>"><?php _e( 'New &raquo;', 'bbpm' ); ?></a></th>
	<th><?php _e('From', 'bbpm'); ?></th>
	<th><?php _e('Freshness'); ?></th>
	<th><?php _e('Actions'); ?></th>
</tr>

<?php
while ($bbpm->have_pm()) {
	$the_pm =& $bbpm->the_pm;
?>
<tr<?php alt_class( 'pm_inbox' ); ?>>
	<td><a href="<?php echo $the_pm->read_link; ?>"><?php echo $the_pm->title; ?></a></td>
	<td><a href="<?php echo get_user_profile_link( $the_pm->from->ID ); ?>"><?php echo apply_filters( 'post_author', apply_filters( 'get_post_author', empty( $the_pm->from->display_name ) ? $the_pm->from->data->user_login : $the_pm->from->display_name, $the_pm->from->ID ) ); ?></a></td>
	<td><?php echo bb_since( $the_pm->date ); ?></td>
	<td><a href="<?php echo $the_pm->delete_link; ?>"><?php _e( 'Delete', 'bbpm' ); ?></a></td>
</tr>
<?php } ?>
</table>

<h2><?php _e('Outbox', 'bbpm'); ?></h2>
<table id="forumlist">
<tr>
	<th><?php _e('Subject', 'bbpm'); ?></th>
	<th><?php _e('To', 'bbpm'); ?></th>
	<th><?php _e('Freshness'); ?></th>
	<th><?php _e('Actions'); ?></th>
</tr>

<?php
while ( $bbpm->sent_pm() ) {
	$the_pm =& $bbpm->the_pm;
?>
<tr<?php alt_class( 'pm_outbox' ); ?>>
	<td><a href="<?php echo $the_pm->read_link; ?>"><?php echo $the_pm->title; ?></a></td>
	<td><a href="<?php echo get_user_profile_link( $the_pm->to->ID ); ?>"><?php echo apply_filters( 'post_author', apply_filters( 'get_post_author', empty( $the_pm->to->display_name ) ? $the_pm->to->data->user_login : $the_pm->to->display_name, $the_pm->to->ID ) ); ?></a></td>
	<td><?php echo bb_since( $the_pm->date ); ?></td>
	<td><a href="<?php echo $the_pm->delete_link; ?>"><?php _e( 'Delete', 'bbpm' ); ?></a></td>
</tr>
<?php } ?>
</table>

<?php } else {
	$the_pm =& new bbPM_Message( $get );
	switch ( $action ) {
		case 'reply':
?><a href="<?php echo $bbpm->get_link(); ?>"><?php _e( 'Private Messages', 'bbpm' ); ?></a> &raquo; <a href="<?php echo $the_pm->read_link; ?>"><?php _e( 'Read', 'bbpm' ); ?></a> &raquo; Reply</h3>
<ol id="thread">
<li>
<div class="threadauthor">
	<?php

if ( $the_pm->from->user_url )
	echo '<a href="' . attribute_escape( $the_pm->from->user_url ) . '">';

echo bb_get_avatar( $the_pm->from->ID, 48 );

if ( $the_pm->from->user_url )
	echo '</a>';

?>
	<p>
		<strong><?php 

if ( $the_pm->from->user_url )
	echo '<a href="' . attribute_escape( $the_pm->from->user_url ) . '">';

echo apply_filters( 'post_author', apply_filters( 'get_post_author', empty( $the_pm->from->display_name ) ? $the_pm->from->data->user_login : $the_pm->from->display_name, $the_pm->from->ID ) );

if ( $the_pm->from->user_url )
	echo '</a>';


?></strong><br />
		<small><?php

$title = get_user_title( $the_pm->from->ID );
echo apply_filters( 'post_author_title_link', apply_filters( 'get_post_author_title_link', '<a href="' . get_user_profile_link( $the_pm->from->ID ) . '">' . $title . '</a>', 0 ), 0 );

?></small>
	</p>
</div>
<div class="threadpost">
	<div class="post"><?php echo apply_filters( 'post_text', apply_filters( 'get_post_text', $the_pm->text ) ); ?></div>
	<div class="poststuff"><?php printf( __( 'Sent %s ago', 'bbpm' ), bb_since( $the_pm->date ) ); ?> <a href="<?php echo $the_pm->delete_link; ?>"><?php _e( 'Delete', 'bbpm' ); ?></a> <a href="<?php echo $the_pm->reply_link; ?>" class="reply"><?php _e( 'Reply', 'bbpm' ); ?></a></div>
</div>
</li>
</ol>
<div id="respond">
<h2 id="reply">Reply</h2>
<form class="postform pm-form" method="post" action="<?php echo BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/pm.php'; ?>">
<fieldset>
<?php do_action( 'post_form_pre_post' ); ?>
<p>
	<label for="message"><?php _e( 'Message:', 'bbpm' ); ?><br/></label>
	<textarea name="message" cols="50" rows="8" id="message" tabindex="3"></textarea>
</p>
<p class="submit">
	<input type="submit" id="postformsub" name="Submit" value="<?php echo attribute_escape( __( 'Send Message &raquo;', 'bbpm' ) ); ?>" tabindex="4" />
</p>

<p><?php _e('Allowed markup:'); ?> <code><?php allowed_markup(); ?></code>. <br /><?php _e('You can also put code in between backtick ( <code>`</code> ) characters.'); ?></p>

<?php bb_nonce_field( 'bbpm-reply-' . $the_pm->ID ); ?>

<input type="hidden" value="<?php echo $the_pm->ID; ?>" name="reply_to" id="reply_to" />
</fieldset>
</form>
</div>
<?php	break;
		default:
?><a href="<?php echo $bbpm->get_link(); ?>"><?php _e( 'Private Messages', 'bbpm' ); ?></a> &raquo; <?php _e( 'Read', 'bbpm' ); ?></h3>
<?php require_once dirname( __FILE__ ) . '/threadviewer.php';
		}
	}
} ?>

<?php bb_get_footer();?>