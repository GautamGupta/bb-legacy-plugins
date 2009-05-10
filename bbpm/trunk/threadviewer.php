<?php

global $bbpm;

$messagechain = $bbpm->get_thread( $get );

?>
<ol id="thread">
<?php

foreach ( $messagechain as $the_pm ) {
	if ( !$the_pm->read && $the_pm->to->ID == bb_get_current_user_info( 'ID' ) ) {
		global $bbdb;
		$bbdb->update( $bbdb->bbpm, array( 'pm_read' => 1 ), array( 'ID' => $the_pm->ID ) );
	}

?>
<li id="pm-<?php echo $the_pm->ID; ?>"<?php alt_class( 'bbpm_thread' );

if ( $the_pm->thread_depth )
	echo ' style="margin-left: ' . ( $the_pm->thread_depth * 2.5 ) . 'em"';

?>>

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
	<div class="poststuff"><?php printf( __( 'Sent %s ago', 'bbpm' ), bb_since( $the_pm->date ) ); ?> <a href="<?php echo $the_pm->delete_link; ?>"><?php _e( 'Delete', 'bbpm' ); ?></a> <a href="<?php echo $the_pm->reply_link; ?>" class="pm-reply"><?php _e( 'Reply', 'bbpm' ); ?></a></div>
</div>
</li>
<?php
}
?>
</ol>
<script type="text/javascript">
jQuery(function($){
	$('.pm-reply').click(function(){
		$('#respond').hide('normal', function(){$(this).remove()});
		var pm = $(this).parents('li');
		$('<div id="respond"/>').appendTo(pm).hide().load($(this).attr('href') + ' #respond', function(){
			$(this).find('textarea').css({width: '99%'}).end().find('#reply').append(' ').append($('<a href="#"><small style="font-size:small"><?php echo addslashes( __( '[Cancel]', 'bbpm' ) ); ?></a></small>').click(function(){
				$('#respond').hide('normal', function(){$(this).remove()});
			})).end().show('fast');
		});
		return false;
	});
});
</script>