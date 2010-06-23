<?php

function autorank_admin() {
	if ( !empty( $_GET['updated'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	$autorank = autorank_get_settings();

	if ( $autorank['use_db'] && ( $post_count_plus = bb_get_option( 'post_count_plus' ) ) &&
		 !empty( $post_count_plus['custom_titles'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'post_count_plus_import' ) {
		autorank_admin_import_post_count_plus();
		return;
	}

	$options = autorank_admin_get_options( $autorank );

	if ( !$autorank['use_db'] ) {
		bb_admin_notice( sprintf(
			__( 'In %1$s, there is a line of code that says %2$s. Please change it to %3$s in order to enable this settings panel.', 'autorank' ),
			'<code><big>' . esc_html( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'autorank.php' ) . '</big></code>',
			'<code><big>\'use_db\' => <strong>false</strong>,</big></code>',
			'<code><big>\'use_db\' => <strong>true</strong>,</big></code>' ), 'error' );
		bb_delete_option( 'autorank' );
	}

	if ( $autorank['use_db'] && empty( $autorank['post_count_plus_imported'] ) && ( $post_count_plus = bb_get_option( 'post_count_plus' ) ) && !empty( $post_count_plus['custom_titles'] ) ) {
		bb_admin_notice( sprintf( __( '<strong>Were you using Post Count Plus?</strong> <a href="%s">I can import your ranks from Post Count Plus.</a>', 'autorank' ),
			bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'autorank_admin', 'action' => 'post_count_plus_import' ), BB_URI_CONTEXT_BB_ADMIN ) ) );
	} ?>

<div class="wrap">

<h2><?php _e( 'AutoRank Settings', 'autorank' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'autorank_admin' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<legend><?php _e( 'Options', 'autorank' ); ?></legend>
<?php

global $bb_hardcoded;

foreach ( $options as $option => $args ) {
	if ( !$autorank['use_db'] )
		$bb_hardcoded['autorank_' . $option] = $autorank[$option];

	bb_option_form_element( 'autorank_' . $option, $args );
}

if ( bb_forums() ) {

	while ( bb_forum() ) {
		if ( !$autorank['use_db'] )
			$bb_hardcoded['autorank_post_modifier_forum'][get_forum_id()] = true;

		bb_option_form_element( 'autorank_post_modifier_forum[' . get_forum_id() . ']', array(
			'title' => sprintf( __( '"%s" forum multiplier', 'autorank' ), get_forum_name() ),
			'value' => isset( $autorank['post_modifier_forum'][get_forum_id()] ) ? $autorank['post_modifier_forum'][get_forum_id()] : 1,
			'class' => 'short'
		) );
	}
} ?>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Ranks', 'autorank' ); ?></legend>

		<div>
		<table id="ranks" class="widefat">
			<thead>
			<tr>
				<th><?php _e( 'Title', 'autorank' ); ?></th>
				<th><?php _e( 'Color', 'autorank' ); ?></th>
				<th><?php _e( 'Required Score', 'autorank' ); ?></th>
				<th><?php _e( 'Estimated Posts Required', 'autorank' ); ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th><?php _e( 'Title', 'autorank' ); ?></th>
				<th><?php _e( 'Color', 'autorank' ); ?></th>
				<th><?php _e( 'Required Score', 'autorank' ); ?></th>
				<th><?php _e( 'Estimated Posts Required', 'autorank' ); ?></th>
			</tr>
			</tfoot>

			<tbody>
<?php $average_post_score = autorank_get_average_post_score();

foreach ( $autorank['ranks'] as $score => $rank ) { ?>
			<tr>
				<td><input type="text" class="text long" name="autorank_rank_titles[]" value="<?php if ( is_array( $rank ) ) echo esc_attr( $rank[0] ); else echo esc_attr( $rank ); ?>"<?php if ( !$autorank['use_db'] ) echo ' disabled="disabled"'; ?> /></td>
				<td><input type="text" class="text short" name="autorank_rank_colors[]"<?php if ( is_array( $rank ) ) echo ' style="color: ' . esc_attr( $rank[1] ) . ';"'; ?> value="<?php if ( is_array( $rank ) ) echo esc_attr( $rank[1] ); ?>" placeholder="<?php esc_attr_e( 'Default', 'autorank' ); ?>"<?php if ( !$autorank['use_db'] ) echo ' disabled="disabled"'; ?> /></td>
				<td><input type="text" class="text short" name="autorank_rank_scores[]" value="<?php echo round( $score, 6 ); ?>"<?php if ( !$autorank['use_db'] ) echo ' disabled="disabled"'; ?> /></td>
				<td><?php echo ceil( round( $score, 6 ) / $average_post_score ); ?></td>
			</tr>
<?php } ?>
<?php if ( $autorank['use_db'] ) { ?>
			<tr>
				<td><input type="text" class="text long" name="autorank_rank_titles[]" value="" /></td>
				<td><input type="text" class="text short" name="autorank_rank_colors[]" value="" placeholder="<?php esc_attr_e( 'Default', 'autorank' ); ?>" /></td>
				<td><input type="text" class="text short" name="autorank_rank_scores[]" value="" /></td>
				<td>?</td>
			</tr>
<?php } ?>
			</tbody>
		</table>
<?php if ( $autorank['use_db'] ) { ?>
		<script type="text/javascript">//<![CDATA[
jQuery(function($) {
	var newRow = $('<tr/>').html('<?php echo addslashes( '<td><input type="text" class="text long" name="autorank_rank_titles[]"/><td><input type="text" class="text short" name="autorank_rank_colors[]" value="" placeholder="' . __( 'Default', 'autorank' ) . '"/></td><td><input type="text" class="text short" name="autorank_rank_scores[]" value=""/></td><td>?</td>' ); ?>'),
		avgScore = <?php echo $average_post_score; ?>;

	$('#ranks tbody').live('input', 'changed', function f(e){
		var unusedRows = $('#ranks tbody tr').filter(function(){
					if ($(this).find('input[name="autorank_rank_titles\[\]"]').val().length)
						return false;
					if ($(this).find('input[name="autorank_rank_colors\[\]"]').val().length)
						return false;
					if ($(this).find('input[name="autorank_rank_scores\[\]"]').val().length)
						return false;
					return true;
				}),
			pos = e.target.selectionStart;

		if (e.target.name == 'autorank_rank_scores[]') {
			if (e.target.value != e.target.value.replace(/[^0-9e\.]/, '')) {
				setTimeout(function(){
					var pos = e.target.value.substring(0, e.target.selectionStart).replace(/[^0-9\.]+/g, '').length;
					e.target.value = e.target.value.replace(/[^0-9\.]+/g, '');
					setTimeout(function(){
						e.target.selectionStart = e.target.selectionEnd = pos;

						f(e); // IRON
					}, 0);
				}, 0);
				return;
			}

			var estPosts = (e.target.value || NaN) / avgScore;
			if (String(estPosts).indexOf('e') != -1)
				$(e.target).parent().parent().children(':last').text('<?php echo addslashes( __( 'A lot', 'autorank' ) ); ?>');
			else
				$(e.target).parent().parent().children(':last').text(isNaN(estPosts) ? '?' : Math.ceil(estPosts));
		}
		if (e.target.name == 'autorank_rank_colors[]') {
			e.target.setAttribute('style', 'color: ' + e.target.value);
		}

		switch (unusedRows.length) {
			case 0:
				$('#ranks tbody').append(newRow.clone());
			case 1:
				break;
			default:
				unusedRows.each(function(){
					if (!$(this).has(e.target).length)
						$(this).remove();
				});
		}

		$.each($('#ranks tbody tr').get().sort(function(_a, _b){
			var a = +($(_a).find('input[name="autorank_rank_scores\[\]"]').val() || NaN),
				b = +($(_b).find('input[name="autorank_rank_scores\[\]"]').val() || NaN);

			if (isNaN(a)) {
				if (isNaN(b))
					return 0;
				return 1;
			}
			if (isNaN(b))
				return -1;

			return a - b;
		}), function(){
			$(this).appendTo($('#ranks tbody'));
		});

		e.target.focus();
		e.target.selectionStart = e.target.selectionEnd = pos = pos;
	});
});
		//]]></script>
<?php } ?>
		</div>
	</fieldset>

<?php if ( $autorank['use_db'] ) { ?>
	<fieldset class="submit">
		<?php bb_nonce_field( 'autorank-update' ); ?>
		<input type="hidden" name="action" value="update" />
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Save Changes' ); ?>" />
	</fieldset>
<?php } ?>

</form>

</div>

<?php }

function autorank_admin_menu_add() {
	bb_admin_add_submenu( __( 'AutoRank', 'autorank' ), 'use_keys', 'autorank_admin', 'options-general.php' );
}
add_action( 'bb_admin_menu_generator', 'autorank_admin_menu_add' );

function autorank_admin_parse() {
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
		if ( $_POST['action'] == 'update' ) {
			bb_check_admin_referer( 'autorank-update' );

			$autorank = autorank_get_settings();

			if ( !$autorank['use_db'] )
				return;

			foreach ( array( 'show_score', 'show_stats', 'show_rank', 'rank_replaces_title', 'show_rank_page' ) as $option ) {
				if ( isset( $_POST['autorank_' . $option] ) ) {
					$autorank[$option] = !!$_POST['autorank_' . $option];
				}
			}

			foreach ( array( 'post_default_score', 'post_modifier_first', 'post_modifier_word', 'post_modifier_char' ) as $option ) {
				if ( isset( $_POST['autorank_' . $option] ) ) {
					$autorank[$option] = (double) $_POST['autorank_' . $option];
				}
			}

			foreach ( array( 'text_score', 'text_reqscore' ) as $option ) {
				if ( isset( $_POST['autorank_' . $option] ) ) {
					$autorank[$option] = $_POST['autorank_' . $option];
				}
			}

			if ( isset( $_POST['autorank_post_modifier_forum'] ) ) {
				$autorank['post_modifier_forum'] = array();
				foreach ( $_POST['autorank_post_modifier_forum'] as $id => $multiplier ) {
					if ( is_numeric( $multiplier ) && ( (double) $multiplier ) != 1 && get_forum_id( $id ) )
						$autorank['post_modifier_forum'][$id] = (double) $multiplier;
				}
			}

			if ( isset( $_POST['autorank_rank_titles'] ) && isset( $_POST['autorank_rank_colors'] ) && isset( $_POST['autorank_rank_scores'] ) ) {
				$autorank['ranks'] = array();

				for ( $i = 0; $i < count( $_POST['autorank_rank_titles'] ); $i++ ) {
					if ( trim( $_POST['autorank_rank_titles'][$i] ) == '' ) {
						continue;
					}

					if ( trim( $_POST['autorank_rank_colors'][$i] ) == '' ) {
						$autorank['ranks'][(double) $_POST['autorank_rank_scores'][$i]] = trim( $_POST['autorank_rank_titles'][$i] );
					} else {
						$autorank['ranks'][(double) $_POST['autorank_rank_scores'][$i]] = array(
							trim( $_POST['autorank_rank_titles'][$i] ),
							trim( $_POST['autorank_rank_colors'][$i] ) );
					}
				}
			}

			$GLOBALS['autorank'] = $autorank;

			bb_update_option( 'autorank', $autorank );

			autorank_recount();

			$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
			bb_safe_redirect( $goback );
			exit;
		}

		if ( $_POST['action'] == 'post_count_plus_import' ) {
			bb_check_admin_referer( 'autorank-post-count-plus-import' );

			$autorank = autorank_get_settings();

			$autorank['ranks'] = array();

			foreach ( $_POST['autorank_ranks'] as $score => $rank ) {
				$autorank['ranks'][(double) $score] = $rank;
			}

			$autorank['post_count_plus_imported'] = true;

			bb_update_option( 'autorank', $autorank );

			$GLOBALS['autorank'] = $autorank;

			autorank_recount();

			$goback = remove_query_arg( 'action', add_query_arg( 'updated', 'true', wp_get_referer() ) );
			bb_safe_redirect( $goback );
			exit;
		}
	} else {
		if ( $_GET['action'] != 'post_count_plus_import' ) {
			wp_enqueue_script( 'jquery' );
		}
	}
}
add_action( 'autorank_admin_pre_head', 'autorank_admin_parse' );

function autorank_admin_get_options( $autorank ) {
	return array(
		'show_score' => array(
			'title'   => __( 'Show scores next to posts', 'autorank' ),
			'type'    => 'select',
			'value'   => $autorank['show_score'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'show_stats' => array(
			'title'   => sprintf( __( 'Show scores on <a href="%s">the statistics page</a>', 'autorank' ), bb_get_uri( 'statistics.php' ) ),
			'type'    => 'select',
			'value'   => $autorank['show_stats'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'show_rank' => array(
			'title' => __( 'Show ranks next to posts', 'autorank' ),
			'type'  => 'select',
			'value' => $autorank['show_rank'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'rank_replaces_title' => array(
			'title' => __( 'Change "Member" to the user\'s rank', 'autorank' ),
			'type'  => 'select',
			'value' => $autorank['rank_replaces_title'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'show_rank_page' => array(
			'title' => __( 'Show users the list of ranks', 'autorank' ),
			'type'  => 'select',
			'value' => $autorank['show_rank_page'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'post_default_score' => array(
			'title' => __( 'Base score', 'autorank' ),
			'value' => $autorank['post_default_score'],
			'class' => 'short'
		),

		'post_modifier_first' => array(
			'title' => __( 'New topic bonus', 'autorank' ),
			'value' => $autorank['post_modifier_first'],
			'class' => 'short'
		),

		'post_modifier_word' => array(
			'title' => __( 'Word bonus', 'autorank' ),
			'value' => $autorank['post_modifier_word'],
			'class' => 'short'
		),

		'post_modifier_char' => array(
			'title' => __( 'Letter bonus', 'autorank' ),
			'value' => $autorank['post_modifier_char'],
			'class' => 'short'
		),

		'text_score' => array(
			'title' => __( '"Score:" text', 'autorank' ),
			'value' => $autorank['text_score'],
			'note'  => __( 'Shown next to a user\'s score.', 'autorank' ),
			'class' => 'long'
		),

		'text_reqscore' => array(
			'title' => __( '"Required score:" text', 'autorank' ),
			'value' => $autorank['text_reqscore'],
			'note'  => __( 'Shown when a user\'s rank is hovered over.', 'autorank' ),
			'class' => 'long'
		),
	);
}

function autorank_admin_import_post_count_plus() {
	$autorank = autorank_get_settings();
	$post_count_plus = bb_get_option( 'post_count_plus' );
	$average_post_score = autorank_get_average_post_score();

	$ranks = array();

	for ( $i = 0; $i < count( $post_count_plus['custom_titles'] ); $i += 5 ) {
		if ( !$post_count_plus['custom_titles'][$i + 5] )
			continue; // Skip blank ranks
		if ( $post_count_plus['custom_titles'][$i + 6] == 0 && ( $post_count_plus['custom_titles'][$i + 7] || $post_count_plus['custom_titles'][$i + 8] ) )
			continue; // Skip role/age-based ranks

		$ranks[$post_count_plus['custom_titles'][$i + 6]] = array(
			$post_count_plus['custom_titles'][$i + 5],
			$post_count_plus['custom_titles'][$i + 9]
		);
	}

	if ( $autorank['ranks'] )
		bb_admin_notice( __( '<strong>Warning:</strong> You already have AutoRank ranks defined. If you accept these ranks, they will be replaced.', 'autorank' ), 'error' ); ?>
<div id="wrap">

<h2><?php _e( 'AutoRank - Post Count Plus Importer', 'autorank' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<p><?php printf( __( 'This is a list of your Post Count Plus ranks. The suggested score is based on the average score of up to 100 random posts from your forum. Currently, the average score is %s. If you have more than 100 posts in your forum and you feel that the average score is incorrect, try refreshing the page.', 'autorank' ), bb_number_format_i18n( $average_post_score, 6 ) ); ?>
<br /><br />
<?php _e( 'You can modify these ranks after you accept them.', 'autorank' ); ?></p>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'autorank_admin' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<table class="widefat">
		<thead>
		<tr>
			<th><?php _e( 'Title', 'autorank' ); ?></th>
			<th><?php _e( 'Color', 'autorank' ); ?></th>
			<th><?php _e( 'Posts', 'autorank' ); ?></th>
			<th><?php _e( 'Suggested Score', 'autorank' ); ?></th>
		</tr>
		</thead>

		<tfoot>
		<tr>
			<th><?php _e( 'Title', 'autorank' ); ?></th>
			<th><?php _e( 'Color', 'autorank' ); ?></th>
			<th><?php _e( 'Posts', 'autorank' ); ?></th>
			<th><?php _e( 'Suggested Score', 'autorank' ); ?></th>
		</tr>
		</tfoot>

		<tbody>
<?php foreach ( $ranks as $posts => $rank ) { ?>
		<tr>
			<td><?php echo esc_html( $rank[0] ); ?></td>
			<td><?php

if ( $rank[1] ) {
	echo '<span style="color: ' . esc_attr( $rank[1] ) . '">' . esc_html( $rank[1] ) . '</span>';
} else {
	echo '<em>';
	_e( 'Default', 'autorank' );
	echo '</em>';
}

				?></td>
			<td><?php echo bb_number_format_i18n( $posts ); ?></td>
			<td>
				<?php echo bb_number_format_i18n( $posts * $average_post_score, 6 ); ?>
<?php if ( $rank[1] ) { ?>
				<input type="hidden" name="autorank_ranks[<?php echo round( $posts * $average_post_score, 6 ); ?>][0]" value="<?php echo esc_attr( $rank[0] ); ?>" />
				<input type="hidden" name="autorank_ranks[<?php echo round( $posts * $average_post_score, 6 ); ?>][1]" value="<?php echo esc_attr( $rank[1] ); ?>" />
<?php } else { ?>
				<input type="hidden" name="autorank_ranks[<?php echo round( $posts * $average_post_score, 6 ); ?>]" value="<?php echo esc_attr( $rank[0] ); ?>" />
<?php } ?>
			</td>
		</tr>
<?php } ?>
		</tbody>
		</table>
	</fieldset>

	<fieldset class="submit">
		<?php bb_nonce_field( 'autorank-post-count-plus-import' ); ?>
		<input type="hidden" name="action" value="post_count_plus_import" />
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Accept Ranks', 'autorank' ); ?>" />
	</fieldset>
</form>

</div>
<?php }
