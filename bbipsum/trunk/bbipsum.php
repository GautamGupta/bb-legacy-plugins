<?php
/*
Plugin Name: bbIpsum
Plugin URI: http://nightgunner5.wordpress.com/tag/bbipsum/
Version: 0.1
Description: When you're developing the next big thing, sometimes you need a forum full of gibberish. That's why you installed this plugin in the first place.
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
*/

function bbipsum() {
	if ( !empty( $_GET['updated'] ) ) {
		bb_admin_notice( sprintf( __( '<strong>Your forum has been Ipsum-ified.</strong> Please consider doing a <a href="%s">full recount</a>, and remember to re-activate all of your plugins!', 'bbipsum' ), bb_get_uri( 'bb-admin/tools-recount.php', null, BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ) ) );
	}
	if ( !empty( $_GET['deleted'] ) ) {
		bb_admin_notice( sprintf( __( '<strong>Your forum has been cleaned of gibberish.</strong> Please consider doing a <a href="%s">full recount</a>.', 'bbipsum' ), bb_get_uri( 'bb-admin/tools-recount.php', null, BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ) ) );
	}

	require_once dirname( __FILE__ ) . '/LoremIpsum.class.php';
	$lipsum = new LoremIpsumGenerator(); ?>

<div class="wrap">

<h2><?php echo ucfirst( trim( $lipsum->getContent( 6, 'plain' ), " \t\n\r." ) ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<?php echo bbipsum_filter( $lipsum->getContent( 50, 'txt' ) ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbipsum' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">

	<fieldset>
		<div>
			<div class="inputs"><?php _e( 'When you\'re developing the next big thing, sometimes you need a forum full of gibberish. That\'s why you installed this plugin in the first place.', 'bbipsum' ); ?></div>
		</div>

		<div id="option-html">
			<label for="html"><?php _e( 'Include HTML', 'bbipsum' ); ?></label>

			<div class="inputs">
				<select class="select" name="html" id="html">
					<option value="1" checked="checked"><?php _e( 'Yes', 'bbipsum' ); ?></option>
					<option value="0"><?php _e( 'No', 'bbipsum' ); ?></option>
				</select>
				<p><?php _e( 'This will include <a href="#">links</a>, <strong>bold text</strong>, and <em>italic text</em> in the posts.', 'bbipsum' ); ?></p>
			</div>
		</div>

		<div id="option-users">
			<label for="users"><?php _e( 'Register new users', 'bbipsum' ); ?></label>

			<div class="inputs">
				<input class="text short" id="users" name="users" value="10" />
				<p><?php _e( 'Up to this many fake users may be registered. If this is set to 0, all of the new posts and topics will come from your account. If you have used this plugin previously with the same gibberish type, accounts will be reused.', 'bbipsum' ); ?></p>
			</div>
		</div>

		<div id="option-topics">
			<label for="topics"><?php _e( 'Number of topics', 'bbipsum' ); ?></label>

			<div class="inputs">
				<input class="text short" id="topics" name="topics" value="100" />
				<p><?php _e( 'This many topics will be added to your forum. If you have multiple forums, each topic will be placed randomly into one.', 'bbipsum' ); ?></p>
			</div>
		</div>

		<div id="option-posts">
			<label for="posts"><?php _e( 'Maximum posts per topic', 'bbipsum' ); ?></label>

			<div class="inputs">
				<input class="text short" id="posts" name="posts" value="25" />
				<p><?php _e( 'No more than this many posts will be entered into any given topic. Each topic will have at least one post.', 'bbipsum' ); ?></p>
			</div>
		</div>

		<div id="option-type">
			<label for="type"><?php _e( 'Gibberish type', 'bbipsum' ); ?></label>

			<div class="inputs">
				<select class="select" id="type" name="type">
					<option value="0"><?php _e( 'Lorem Ipsum', 'bbipsum' ); ?></option>
					<option value="1"><?php _e( 'asdfjkl', 'bbipsum' ); ?></option>
					<option value="2"><?php _e( 'Aroodles', 'bbipsum' ); ?></option>
					<option value="3"><?php _e( 'Pikachu', 'bbipsum' ); ?></option>
				</select>
				<p><strong><?php _e( 'Examples:', 'bbipsum' ); ?></strong><br />
					<strong><?php _e( 'Lorem Ipsum', 'bbipsum' ); ?>:</strong> <?php echo bbipsum_get_gibberish_name( 0 ); ?><br />
					<strong><?php _e( 'asdfjkl', 'bbipsum' ); ?>:</strong> <?php echo bbipsum_get_gibberish_name( 1 ); ?><br />
					<strong><?php _e( 'Aroodles', 'bbipsum' ); ?>:</strong> <?php echo bbipsum_get_gibberish_name( 2 ); ?><br />
					<strong><?php _e( 'Pikachu', 'bbipsum' ); ?>:</strong> <?php echo bbipsum_get_gibberish_name( 3 ); ?></p>
			</div>
		</div>

		<div>
			<div class="inputs"><?php _e( 'It is suggested that you deactivate all other plugins before clicking the following button to reduce database usage.', 'bbipsum' ); ?></div>
		</div>
	</fieldset>

	<fieldset class="submit">
		<?php bb_nonce_field( 'bbipsum' ); ?>
		<input type="hidden" name="action" value="post" />
		<span id="show_progress_holder"></span>
		<script type="text/javascript">
			document.getElementById( 'show_progress_holder' ).innerHTML = unescape( '%3Cinput%20type%3D%22hidden%22%20name%3D%22show_progress%22%20value%3D%22true%22%20/%3E' );
		</script>
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Ipsum-ify my forum!', 'bbipsum' ); ?>" />
	</fieldset>

</form>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbipsum' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">

	<fieldset>
		<div id="option-delete">
			<label for="delete"><?php _e( 'Delete all ipsum', 'bbipsum' ); ?></label>

			<div class="inputs">
				<input type="checkbox" class="checkbox" id="delete" name="delete" />
				<?php _e( 'I\'m sure', 'bbipsum' ); ?>
				<p><?php printf( __( 'Sometimes, you don\'t want your forum to say "%s" anymore. If you regret filling your forum with gibberish, check this box and push the button below.', 'bbipsum' ), bbipsum_get_gibberish_name( mt_rand( 0, 3 ) ) ); ?></p>
			</div>
		</div>
	</fieldset>

	<fieldset class="submit">
		<?php bb_nonce_field( 'bbipsum-delete' ); ?>
		<input type="hidden" name="action" value="delete" />
		<input class="submit" type="submit" name="submit" style="background: #c00 none; border-color: #700;" value="<?php _e( 'Delete all bbIpsum data', 'bbipsum' ); ?>" />
	</fieldset>

</form>

</div>

<?php }

function bbipsum_admin_parse() {
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
		if ( $_POST['action'] == 'post' ) {
			bb_check_admin_referer( 'bbipsum' );

			set_time_limit( 0 );
			ignore_user_abort( true );

			$ipsum_type = min( max( (int) $_POST['type'], 0 ), 3 );

			$accounts = array( bb_get_current_user_info( 'ID' ) );

			global $bbdb;
			if ( 0 < $allowed_accounts = (int) $_POST['users'] ) {
				$accounts = array();

				if ( $_accounts = $bbdb->get_col( $bbdb->prepare( "SELECT `user_id` FROM `$bbdb->usermeta` WHERE `meta_key` = 'bbipsum' AND `meta_value` = %d ORDER BY RAND() LIMIT %d", $ipsum_type, $allowed_accounts ) ) ) {
					bb_cache_users( $_accounts );
					$accounts = $_accounts;
				}

				for ( $i = count( $accounts ); $i < $allowed_accounts; $i++ ) {
					$accounts[] = -1;
				}
			}

			if ( 1 > $allowed_topics = (int) $_POST['topics'] ) {
				bb_die( __( 'You must add at least one topic with this plugin.', 'autorank' ) );
			}

			if ( 1 > $allowed_posts = (int) $_POST['posts'] ) {
				$allowed_posts = 1;
			}

			$allow_html = (boolean) (int) $_POST['html'];

			$forums = bb_get_forums();
			$f = count( $forums );

			global $wp_users_object;
			$_user = bb_get_current_user_info( 'ID' );

			$show_progress = false;

			if ( !empty( $_POST['show_progress'] ) ) {
				$show_progress = true;

				wp_enqueue_script( 'jquery' );

				bb_install_header( __( 'bbIpsum is working...', 'bbipsum' ), __( 'bbIpsum is working...', 'bbipsum' ) );

				wp_print_scripts(); ?>
	<script type="text/javascript" src="<?php echo BB_PLUGIN_URL; ?>/bbipsum/jquery-ui/jquery-ui-1.8.2.custom.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo BB_PLUGIN_URL; ?>/bbipsum/jquery-ui/jquery-ui-1.8.2.custom.css" />
	<strong><?php _e( 'Topics', 'bbipsum' ); ?></strong><br/>
	<div id="totalprogress"></div>
	<br/><br/>
	<strong><?php _e( 'Posts', 'bbipsum' ); ?></strong><br/>
	<div id="partprogress"></div>
	<script type="text/javascript">
		jQuery('#totalprogress, #partprogress').progressbar({
			value: 0
		});
	</script>
<?php
				ob_flush();
				flush();
			}

			// We don't need no Akismets... (on this pageload)
			remove_filter( 'pre_post_status', 'bb_ksd_pre_post_status' );

			// Okay, everything's ready... Let's do this!
			for ( $i = 0; $i < $allowed_topics; $i++ ) {
				$topic = false;
				for ( $j = 0, $posts_wanted = mt_rand( 1, $allowed_posts ); $j < $posts_wanted; $j++ ) {
					if ( -1 == $accounts[$cur_user = mt_rand( 0, count( $accounts ) - 1 )] ) {
						do {
							$user_login = bbipsum_get_gibberish_name( $ipsum_type );
							$user_nicename = $_user_nicename = bb_user_nicename_sanitize( $user_login );
						} while ( strlen( $_user_nicename ) < 1 );

						while ( is_numeric( $user_nicename ) || $existing_user = bb_get_user_by_nicename( $user_nicename ) )
							$user_nicename = bb_slug_increment( $_user_nicename, $existing_user->user_nicename, 50 );

						$user_email = md5( $user_login ) . '@example.com';
						$user_pass = bb_generate_password();
						$user_status = 0;

						$user = $wp_users_object->new_user( compact( 'user_login', 'user_email', 'user_nicename', 'user_status', 'user_pass' ) );
						if ( is_wp_error( $user ) ) {
							$j--;
							continue;
						}

						bb_update_usermeta( $user['ID'], $bbdb->prefix . 'capabilities', array( 'inactive' => true ) );
						bb_update_usermeta( $user['ID'], 'bbipsum', $ipsum_type );

						$accounts[$cur_user] = $user['ID'];
					}
					$cur_user = $accounts[$cur_user];

					bb_set_current_user( $cur_user );
					if ( !$topic ) {
						$topic = bb_new_topic( bbipsum_get_gibberish( $ipsum_type ), $forums[mt_rand( 0, $f )]->forum_id );
						bb_update_topicmeta( $topic, 'bbipsum', $ipsum_type );
					}

					$post_data = array(
						'post_text' => bbipsum_get_gibberish_html( $ipsum_type, $allow_html ),
						'topic_id' => $topic,
						'poster_ip' => '127.' . mt_rand( 0, 255 ) . '.' . mt_rand( 0, 255 ) . '.' . mt_rand( 0, 255 )
					);
					$post = bb_insert_post( $post_data );
					bb_update_postmeta( $post, 'bbipsum', $ipsum_type );

					if ( $show_progress ) {
						echo '<script type="text/javascript">jQuery("#partprogress").progressbar({value:' . ( ( $j + 1 ) / $posts_wanted * 100 ) . '})</script>';
						ob_flush();
						flush();
					}
				}

				if ( $show_progress ) {
					echo '<script type="text/javascript">jQuery("#totalprogress").progressbar({value:' . ( ( $i + 1 ) / $allowed_topics * 100 ) . '})</script>';
					ob_flush();
					flush();
				}
			}

			$goback = add_query_arg( 'updated', 'true', remove_query_arg( 'deleted', wp_get_referer() ) );
			if ( $show_progress ) {
				echo '<script type="text/javascript">location="' . addslashes( $goback ) . '"</script><p><a href="' . esc_url( $goback ) . '">' . __( 'Go back', 'bbipsum' ) . '</a></p>';
			} else {
				bb_safe_redirect( $goback );
			}
			exit;
		}

		if ( $_POST['action'] == 'delete' ) {
			bb_check_admin_referer( 'bbipsum-delete' );
			if ( empty( $_POST['delete'] ) ) {
				bb_safe_redirect( wp_get_referer() );
				exit;
			}

			global $bbdb;

			$bbdb->query( "DELETE FROM `$bbdb->users` WHERE `ID` IN ( SELECT `t`.* FROM ( SELECT `user_id` FROM `$bbdb->usermeta` WHERE `meta_key` = 'bbipsum' ) AS `t` )" );
			$bbdb->query( "DELETE FROM `$bbdb->usermeta` WHERE `user_id` IN ( SELECT `t`.* FROM ( SELECT `user_id` FROM `$bbdb->usermeta` WHERE `meta_key` = 'bbipsum' ) AS `t` )" );

			$bbdb->query( "DELETE FROM `$bbdb->topics` WHERE `topic_id` IN ( SELECT `t`.* FROM ( SELECT `object_id` FROM `$bbdb->meta` WHERE `meta_key` = 'bbipsum' AND `object_type` = 'bb_topic' ) AS `t` )" );
			$bbdb->query( "DELETE FROM `$bbdb->meta` WHERE `object_id` IN ( SELECT `t`.* FROM ( SELECT `object_id` FROM `$bbdb->meta` WHERE `meta_key` = 'bbipsum' AND `object_type` = 'bb_topic' ) AS `t` ) AND `object_type` = 'bb_topic'" );

			$bbdb->query( "DELETE FROM `$bbdb->posts` WHERE `post_id` IN ( SELECT `t`.* FROM ( SELECT `object_id` FROM `$bbdb->meta` WHERE `meta_key` = 'bbipsum' AND `object_type` = 'bb_post' ) AS `t` )" );
			$bbdb->query( "DELETE FROM `$bbdb->meta` WHERE `object_id` IN ( SELECT `t`.* FROM ( SELECT `object_id` FROM `$bbdb->meta` WHERE `meta_key` = 'bbipsum' AND `object_type` = 'bb_post' ) AS `t` ) AND `object_type` = 'bb_post'" );

			$goback = add_query_arg( 'deleted', 'true', remove_query_arg( 'updated', wp_get_referer() ) );
			bb_safe_redirect( $goback );
			exit;
		}
	}
}
add_action( 'bbipsum_pre_head', 'bbipsum_admin_parse' );

function bbipsum_admin_add() {
	bb_admin_add_submenu( 'bbIpsum', 'use_keys', 'bbipsum', 'tools-recount.php' );
}
add_action( 'bb_admin_menu_generator', 'bbipsum_admin_add' );

function bbipsum_get_gibberish_name( $ipsum_type ) {
	switch ( $ipsum_type ) {
		case 1:
			return bbipsum_asdfjkl_generate( mt_rand( 5, 20 ) );

		case 2:
			return bbipsum_aroodles_generate( mt_rand( 10, 30 ) );

		case 3:
			return bbipsum_pikachu_generate( mt_rand( 10, 30 ) );

		case 0:
		default:
			require_once dirname( __FILE__ ) . '/LoremIpsum.class.php';
			$lipsum = new LoremIpsumGenerator();

			return ucfirst( str_replace( ',', '', trim( $lipsum->getContent( mt_rand( 2, 4 ), 'plain', false ), " \t\n\r." ) ) );
	}
}

function bbipsum_get_gibberish( $ipsum_type ) {
	switch ( $ipsum_type ) {
		case 1:
			return bbipsum_asdfjkl_generate( mt_rand( 5, 40 ) );

		case 2:
			return bbipsum_aroodles_generate( mt_rand( 10, 60 ) );

		case 3:
			return bbipsum_pikachu_generate( mt_rand( 10, 60 ) );

		case 0:
		default:
			require_once dirname( __FILE__ ) . '/LoremIpsum.class.php';
			$lipsum = new LoremIpsumGenerator();

			return ucfirst( str_replace( ',', '', trim( $lipsum->getContent( mt_rand( 2, 8 ), 'plain', false ), " \t\n\r." ) ) );
	}
}

function bbipsum_get_gibberish_html( $ipsum_type, $html = true ) {
	switch ( $ipsum_type ) {
		case 1:
			return bbipsum_filter( bbipsum_asdfjkl_generate( mt_rand( 500, 5000 ) ), $html );

		case 2:
			return bbipsum_filter( bbipsum_aroodles_generate( mt_rand( 500, 5000 ), true ), $html );

		case 3:
			return bbipsum_filter( bbipsum_pikachu_generate( mt_rand( 500, 5000 ), true ), $html );

		case 0:
		default:
			require_once dirname( __FILE__ ) . '/LoremIpsum.class.php';
			$lipsum = new LoremIpsumGenerator();

			return bbipsum_filter( $lipsum->getContent( mt_rand( 100, 1000 ), 'txt' ), $html );
	}
}

function bbipsum_split( $str ) {
	$ret = array();

	for ( $i = 0, $l = function_exists( 'mb_strlen' ) ? mb_strlen( $str ) : strlen( $str ); $i < $l; $i++ ) {
		$ret[] = function_exists( 'mb_substr' ) ? mb_substr( $str, $i, 1) : substr( $str, $i, 1 );
	}

	return $ret;
}

function bbipsum_asdfjkl_generate( $len ) {
	$asdfjkl = bbipsum_split( __( 'abcdefghijklmnopqrstuvwxyz0123456789 ', 'bbipsum' ) );

	$ret = '';
	for ( $i = 0; $i < $len; $i++ ) {
		$ret .= $asdfjkl[mt_rand( 0, count( $asdfjkl ) - 1 )];
	}

	return $ret;
}

function bbipsum_pikachu_expand( $word ) {
	if ( is_array( $word ) )
		$word = $word[1];

	$ret = '';
	$i = mt_rand( 1, 3 );

	while ( $i-- ) {
		$ret .= $word;
	}

	return $ret;
}

function bbipsum_pikachu_generate( $len, $punctuate = false ) {
	$words = array( 'Pikachu', 'Ch(u)', '(Pika)', '(Pi)', 'Pi(ka)', 'kachu', '(Chu)' );
	$w = count( $words ) - 1;
	$separators = array( ' ', '-' );
	if ( $punctuate )
		$separators = array_merge( $separators, array( '. ', '? ', '! ' ) );
	$s = count( $separators ) - 1;
	$ret = '';

	while ( strlen( $ret ) < $len ) {
		$word = $words[mt_rand( 0, $w )];
		if ( $word == 'kachu' && !$ret )
			continue;

		$ret .= $separators[mt_rand( 0, $s )] . preg_replace_callback( '/\(([^\)]*)\)/', 'bbipsum_pikachu_expand', $word );
	}

	return trim( $ret, implode( '', $separators ) );
}

// Algorithm by Sam P.
function bbipsum_aroodles_generate( $len, $punctuate = false ) {
	$consonants = bbipsum_split( _c( 'bcdfghjklmnpstvwxyz|consonants', 'bbipsum' ) );
	$c = count( $consonants ) - 1;
	$vowels = bbipsum_split( _c( 'aeiou|vowels', 'bbipsum' ) );
	$v = count( $vowels ) - 1;
	$separators = array( ' ', '-' );
	if ( $punctuate )
		$separators = array_merge( $separators, array( '. ', '? ', '! ' ) );
	$s = count( $separators ) - 1;
	$ret = '';

	while ( strlen( $ret ) < $len ) {
		$ret .= $separators[mt_rand( 0, $s )];

		// Two consonants
		$ret .= mt_rand( 0, 1 ) ? $consonants[mt_rand( 0, $c )] : strtoupper( $consonants[mt_rand( 0, $c )] );
		$ret .= $consonants[mt_rand( 0, $c )];

		// Two to ten vowel [sic]
		$ret .= str_repeat( $vowels[mt_rand( 0, $v )], mt_rand( 2, 10 ) );

		// One consonant
		$ret .= $consonants[mt_rand( 0, $c )];
	}

	return ucfirst( ltrim( $ret, implode( '', $separators ) ) );
}

function bbipsum_rand_punctuation( $allow_none = false ) {
	if ( $allow_none && !mt_rand( 0, 2 ) )
		return '';

	if ( mt_rand( 0, 3 ) )
		return '.';
	if ( mt_rand( 0, 1 ) )
		return '?';
	return '!';
}

function bbipsum_rand_formatting( $sentence ) {
	$words = explode( ' ', $sentence );
	$tags = array( 'strong', 'em', 'a href="#"' );
	$opened = array();
	$ret = '';

	for ( $i = 0, $l = count( $words ); $i < $l; $i++ ) {
		switch ( mt_rand( 0, 10 ) ) {
			case 0:
				if ( $opened ) {
					$ret .= ' ' . $words[$i] . '</' . array_pop( $opened ) . '>';
					break;
				}
			case 1:
				$tag = $tags[mt_rand( 0, count( $tags ) - 1 )];

				$ret .= ' <' . $tag . '>' . $words[$i] . '</' . reset( explode( ' ', $tag ) ) . '>';
				break;
			case 2:
				$tag = $tags[mt_rand( 0, count( $tags ) - 1 )];

				$ret .= ' <' . $tag . '>' . $words[$i];
				$opened[] = reset( explode( ' ', $tag ) );
				break;
			default:
				$ret .= ' ' . $words[$i];
		}
	}

	while ( $opened ) {
		$ret .= '</' . array_pop( $opened ) . '>';
	}

	return trim( $ret );
}

function bbipsum_filter( $_ipsum, $full_html = false ) {
	if ( $full_html ) {
		$sentences = array_values( array_filter( explode( '. ', strip_tags( str_replace( '</p><p>', ' ', bbipsum_filter( $_ipsum . "\n" ) ) ) . ' ' ) ) );

		$sentences = array_map( 'bbipsum_rand_formatting', $sentences );

		$ret = '';
		for ( $i = 0, $l = count( $sentences ); $i < $l; $i++ ) {
			$list_type = 'o';

			switch ( mt_rand( 0, 5 ) ) {
				case 0:
					$ret .= '<blockquote>';
					$_i = mt_rand( 1, min( $l - $i, 5 ) );
					$ret .= bbipsum_filter( strip_tags( implode( '. ', array_slice( $sentences, $i, $_i ) ) ), true );
					$i += $_i;
					$ret .= '</blockquote>';
					break;

				case 1:
					$ret .= "\n\n`\n";
					$ret .= $sentences[$i] . "\n";
					$ret .= $sentences[$i + 1];
					$ret .= "\n`\n\n";
					$i++;
					break;

				case 2:
				case 3:
					$ret .= "\n\n";
					for ( $j = min( $l, $i + mt_rand( 1, 5 ) ); $i < $j; $i++ ) {
						$ret .= $sentences[$i] . bbipsum_rand_punctuation() . ' ';
					}
					$ret .= "\n\n";
					break;

				case 4:
					$list_type = 'u';
				case 5:
					$ret .= '<' . $list_type . 'l>';
					for ( $j = min( $l, $i + mt_rand( 1, 5 ) ); $i < $j; $i++ ) {
						$ret .= '<li>' . $sentences[$i] . bbipsum_rand_punctuation( true ) . '</li>';
					}
					$ret .= '</' . $list_type . 'l>';
					break;
			}
		}

		return $ret;
	}

	if ( strpos( $_ipsum, "\n" !== false ) ) {
		return '<p>' . implode( '</p><p>', array_map( 'bbipsum_filter', array_filter( explode( "\n", $_ipsum ), 'trim' ) ) ) . '</p>';
	}

	$ipsum = explode( '.', $_ipsum );
	$ret = '';

	foreach ( $ipsum as $i ) {
		if ( trim( $i ) )
			$ret .= ucfirst( trim( $i ) ) . '. ';
	}

	return trim( $ret );
}