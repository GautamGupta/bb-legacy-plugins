<?php
/**
 * @package bbPM
 * @version 1.0.1
 * @author Nightgunner5
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3 or higher
 */

global $bbpm, $the_pm, $bb_post;

$bb_post = true; // Hax

$get = (int)$get;

$messagechain = $bbpm->get_thread( $get );
$members = $bbpm->get_thread_members( $get );
$bbpm->mark_read( $get );

$voices = array();
foreach ( $messagechain as $pm ) {
	$voices[(int)$pm->from->ID]++;
}
add_filter( 'get_post_author_id', array( &$bbpm, 'post_author_id_filter' ) );

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
<script type="text/javascript">//<![CDATA[
jQuery(function($){
	var autocompleteTimeout, ul = $('<ul/>').css({
		position: 'absolute',
		zIndex: 10000,
		backgroundColor: '#fff',
		fontSize: '1.2em',
		padding: 2,
		marginTop: -1,
		MozBorderRadius: 2,
		WebkitBorderRadius: 2,
		borderRadius: 2,
		border: '1px solid #ccc',
		borderTopWidth: '0'
	}).insertAfter('#tag').hide();
	$('#tag').attr('autocomplete', 'off').keyup(function(){
		// IE compat
		if(document.selection) {
			// The current selection
			var range = document.selection.createRange();
			// We'll use this as a 'dummy'
			var stored_range = range.duplicate();
			// Select all text
			stored_range.moveToElementText(this);
			// Now move 'dummy' end point to end point of original range
			stored_range.setEndPoint('EndToEnd', range);
			// Now we can calculate start and end points
			this.selectionStart = stored_range.text.length - range.text.length;
			this.selectionEnd = this.selectionStart + range.text.length;
		}

		try {
			clearTimeout(autocompleteTimeout);
		} catch (ex) {}

		if (!this.value.length) {
			ul.empty();
			ul.hide();
			return;
		}

		autocompleteTimeout = setTimeout(function(text, pos){
			$.post('<?php echo addslashes( bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ) ); ?>/pm.php', {
				search: text,
				pos: pos,
				thread: <?php echo $get; ?>,
				_wpnonce: '<?php echo bb_create_nonce( 'bbpm-user-search' ); ?>'
			}, function(data){
				ul.empty();
				if (data.length)
					ul.show();
				else
					ul.hide();
				$.each(data, function(i, name){
					if (name.length)
						$('<li/>').css({
							listStyle: 'none'
						}).text(name).click(function(){
							$('#tag').val($(this).text());
							ul.empty();
							ul.hide();
						}).appendTo(ul);
				});
			}, 'json');
		}, 750, this.value, this.selectionStart);
	}).blur(function(){
		ul.empty();
		ul.hide();
		try {
			clearTimeout(autocompleteTimeout);
		} catch (ex) {}
	});
});
//]]></script>
</form>
</div>

<div style="clear:both;"></div>
</div>

<ol id="thread">
<?php
foreach ( $messagechain as $i => $the_pm ) { ?>
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
		$('<div id="respond"/>').appendTo(pm).hide();
		$.get($(this).attr('href'), {}, function(page){
			page = page.substr(page.indexOf('<div id="respond">') + 18);
			page = page.substr(0, page.indexOf('</form>') + 7);
			$('#respond').html(page).find('textarea').css({width: '99%'}).end().find('#reply').append(' ').append($('<a href="#"><small style="font-size:small"><?php echo addslashes( __( '[Cancel]', 'bbpm' ) ); ?></small></a>').click(function(){
				$('#respond').hide('normal', function(){$(this).remove()});
				return false;
			})).end().show('fast');
			$('#message')[0].focus();
<?php if ( function_exists( 'bb_smilies_init' ) ) { // Compat with bbPress Smilies ?>
			bbField = undefined;
			bb_smilies_init();
			bbField.style.width = '99%';
<?php } ?>
		}, 'text');
		return false;
	});
});
</script>