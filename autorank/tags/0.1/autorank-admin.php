<?php

function autorank_admin() {
	if ( !empty( $_GET['updated'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	$autorank = autorank_get_settings();

	$options = autorank_admin_get_options( $autorank );

	if ( !$autorank['use_db'] ) {
		bb_admin_notice( sprintf(
			__( 'In %1$s, there is a line of code that says %2$s. Please change it to %3$s in order to enable this settings panel.', 'autorank' ),
			'<code><big>' . esc_html( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'autorank.php' ) . '</big></code>',
			'<code><big>\'use_db\' => <strong>false</strong>,</big></code>',
			'<code><big>\'use_db\' => <strong>true</strong>,</big></code>' ), 'error' );
		bb_delete_option( 'autorank' );
	}
?>

<div class="wrap">

<h2><?php _e( 'AutoRank Settings', 'autorank' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'autorank_admin' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
<?php

global $bb_hardcoded;

foreach ( $options as $option => $args ) {
	if ( !$autorank['use_db'] )
		$bb_hardcoded['autorank_' . $option] = $autorank[$option];

	bb_option_form_element( 'autorank_' . $option, $args );
}

?>

<?php

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
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update' ) {
		bb_check_admin_referer( 'autorank-update' );

		$autorank = autorank_get_settings();

		if ( !$autorank['use_db'] )
			return;

		foreach ( array( 'show_score', 'show_stats' ) as $option ) {
			if ( isset( $_POST['autorank_' . $option] ) ) {
				$autorank[$option] = !!$_POST['autorank_' . $option];
			}
		}

		foreach ( array( 'post_default_score', 'post_modifier_first', 'post_modifier_word', 'post_modifier_char' ) as $option ) {
			if ( isset( $_POST['autorank_' . $option] ) ) {
				$autorank[$option] = (double) $_POST['autorank_' . $option];
			}
		}

		if ( isset( $_POST['autorank_post_modifier_forum'] ) ) {
			$autorank['post_modifier_forum'] = array();
			foreach ( $_POST['autorank_post_modifier_forum'] as $id => $multiplier ) {
				if ( is_numeric( $multiplier ) && ( (double) $multiplier ) != 1 && get_forum_id( $id ) )
					$autorank['post_modifier_forum'][$id] = (double) $multiplier;
			}
		}

		bb_update_option( 'autorank', $autorank );

		autorank_recount();

		$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
		bb_safe_redirect( $goback );
		exit;
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
	);
}
