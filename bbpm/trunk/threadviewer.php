<?php

global $bbpm;

$messagechain = $bbpm->get_thread( $get );
$members = $bbpm->get_thread_members( $get );
$bbpm->mark_read( $get );

$voices = array();
foreach ( $messagechain as $pm ) {
	$voices[(int)$pm->from->ID]++;
}

?>
<div class="infobox" role="main">
<div id="topic-info">
<span id="topic_labels"></span>
<h2 class="topictitle"><?php echo esc_html( $bbpm->get_thread_title( $get ) ); ?></h2>
<span id="topic_posts">(<?php printf( _n( '%s post', '%s posts', count( $messagechain ) ), bb_number_format_i18n( count( $messagechain ) ) ); ?>)</span>
<span id="topic_voices">(<?php printf( _n( '%s voice', '%s voices', count( $voices ) ), bb_number_format_i18n( count( $voices ) ) ); ?>)</span>

<ul class="topicmeta">
	<li><?php printf( __( 'Started %1$s ago by %2$s' ), bb_since( $messagechain[0]->date ), '<a href="' . get_user_profile_link( $messagechain[0]->from->ID ) . '">' . get_user_display_name( $messagechain[0]->from->ID ) . '</a>' ); ?></li>
<?php if ( 1 < count( $messagechain ) ) : ?>
	<li><?php printf( __( '<a href="%1$s">Latest reply</a> from %2$s' ), esc_attr( '#pm-' . $messagechain[count( $messagechain ) - 1]->ID ), '<a href="' . get_user_profile_link( $messagechain[count( $messagechain ) - 1]->from->ID ) . '">' . get_user_display_name( $messagechain[count( $messagechain ) - 1]->from->ID ) . '</a>' ); ?></li>
<?php endif; ?>
</ul>
</div>

<div id="topic-tags">
<p><?php _e( 'Members', 'bbpm' ); ?>:</p>

<ul>
<?php

foreach ( $members as $member ) {
	if ( isset( $voices[$member] ) )
		echo '<li><a href="' . get_user_profile_link( $member ) . '">' . get_user_display_name( $member ) . '</a></li>';
	else
		echo '<li><em><a href="' . get_user_profile_link( $member ) . '">' . get_user_display_name( $member ) . '</a></em></li>';
}

?>
</ul>

<form id="tag-form" action="<?php echo BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/pm.php'; ?>" method="post">
<p>
<input type="text" id="tag" name="tag"/>
<input type="hidden" id="pm_thread" name="pm_thread" value="<?php echo $get; ?>"/>
<?php bb_nonce_field( 'bbpm-add-member-' . $get ); ?>
<input id="tagformsub" type="submit" value="<?php _e( 'Add &raquo;' ); ?>"/>
</p>
</form>
</div>

<div style="clear:both;"></div>
</div>

<ol id="thread">
<?php

foreach ( $messagechain as $the_pm ) { ?>
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
	echo '<a href="' . esc_url( $the_pm->from->user_url ) . '">';

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
	<div class="poststuff"><?php printf( __( 'Sent %s ago', 'bbpm' ), bb_since( $the_pm->date ) ); ?> <a href="<?php echo $the_pm->read_link; ?>">#</a> <a href="<?php echo $the_pm->reply_link; ?>" class="pm-reply"><?php _e( 'Reply', 'bbpm' ); ?></a></div>
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
			$(this).find('textarea').css({width: '99%'}).end().find('#reply').append(' ').append($('<a href="#"><small style="font-size:small"><?php echo addslashes( __( '[Cancel]', 'bbpm' ) ); ?></small></a>').click(function(){
				$('#respond').hide('normal', function(){$(this).remove()});
				return false;
			})).end().show('fast');
		});
		return false;
	});
});
</script>