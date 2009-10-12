<?php
 /*
Plugin Name: Login-Free Posting
Plugin URI: 
Description: Allows bbPress users to post without logging in, much like WordPress comments.
Version: 1.0
Author: Austin Matzko
Author URI: http://ilfilosofo.com/
 */ 

/*
 * Class for handling posting by non-logged-in users
 */
class LoginLess_Posting 
{
	protected $_anon_user_id = 0; // the user ID we're using for the anonymous user in certain situations
	protected $_user_login_base = 'anonymousloginlessuser';

	public $allow_new_topics = false; // whether to allow non-logged-in users to post new topics
	public $allow_new_posts = false; // whether to allow non-logged-in users to post new posts

	public function __construct() 
	{
		$this->attach_events();

		$this->_anon_user_id = bb_get_option('loginless-anon-id');
	}

	/**
	 * Check for permission to do things.  The purpose of this method is to centralize permission issues, basically imitating bb_current_user_can() for all but those not logged in.
	 * @param string $cap The capability to check for the current user.
	 * @param array $args The array of optional arguments to more finely tune the permissions situation.
	 * @return bool Whether the current user has the capability.
	 */
	private function _current_user_can($cap = '', $args = array())
	{
		global $bb_current_user;

		$retvalue = false;

		// if a logged-in user, then we'll do what bb_current_user_can() would do
		if ( ! empty($bb_current_user) ) {
			$retvalue = call_user_func_array(array(&$bb_current_user, 'has_cap'), array_merge(array($cap), $args));
		// not logged in, which is what we're interested in
		} else {
			// let's whitelist capabilities of an anonymous user
			switch( $cap ) {
				case 'write_post' :
				case 'write_posts' :
				case 'write_topic' :
				case 'write_topics' :
					$retvalue = true;
					break;
			}
		}

		return $retvalue;
	}

	/**
	 * Extend the bbPress global $wp_users_object so that its user search methods work with non-logged-in posting.
	 */
	private function _extend_wp_users_object()
	{
		global $bbdb, $wp_auth_object, $wp_users_object;
		$cookies = $wp_auth_object->cookies;
		$wp_users_object = new LoginLess_WP_Users($bbdb);
		$wp_users_object->_anon_user_id = $this->_anon_user_id;
		$wp_auth_object = new LoginLess_WP_Auth( $bbdb, $wp_users_object, $cookies );
		$GLOBALS['bb_current_user'] =& $wp_auth_object->current;
	}

	/**
	 * Attach action events and filters.
	 */
	public function attach_events()
	{
		add_action('bb_admin_menu_generator', array(&$this, 'event_bb_admin_menu_generator') );
		add_action('bb_got_roles', array(&$this, 'event_bb_got_roles'), 5);
		add_action('bb_new_post', array(&$this, 'event_bb_new_post'));
		add_action('bb_new_topic', array(&$this, 'event_bb_new_topic'), 99, 3);
		add_action('get_post_author', array(&$this, 'event_get_post_author'), 99, 2);	
		add_action('pre_post_form', array(&$this, 'event_pre_post_form'));
		add_action('post_post_form', array(&$this, 'event_post_post_form'));
		
		add_filter('bb_current_user_can', array(&$this, 'bb_current_user_can_callback'), 99, 3);
		add_filter('bb_get_user_email', array(&$this, 'filter_bb_get_user_email'), 99);
		add_filter('bb_get_user_link', array(&$this, 'filter_bb_get_user_link'), 99);
		add_filter('get_topic_author', array(&$this, 'filter_get_topic_author'), 99, 2);
		add_filter('get_topic_last_poster', array(&$this, 'filter_get_topic_last_poster'), 99, 2);

		bb_register_plugin_activation_hook(__FILE__, array(&$this, 'event_plugin_activation')); 
	}

	/**
	 * This method is meant to act as a callback on the bb_current_user_can filter, in order to modify the behavior of bb_current_user_can() to parallel this->_current_user_can.
	 * @param bool $return Whether bb_current_user_can() thinks the current user can do the capability.
	 * @param string $cap The capability for which we're checking the current user's permissions.
	 * @param array $args Additional arguments to pass to the current user object has_cap method.
	 * @return bool Whether the user has permission to do something.
	 */
	public function bb_current_user_can_callback($return = false, $cap = '', $args = array())
	{
		// bb_current_user_can is more strict, so if it says a user can do something, we'll believe it
		if ( true == $return ) {
			return $return;
		} else {
			return $this->_current_user_can($cap, $args);
		}
	}

	/**
	 * Generate a nonce, based on the dummy user's ID
	 * @param string $action The action for the nonce.
	 * @return string The nonce.
	 */
	public function get_nonce($action = '')
	{
		if ( ! bb_is_user_logged_in() ) {
			$uid = $this->_anon_user_id;	
			$i = bb_nonce_tick();
			return substr(bb_hash($i . $action . $uid, 'nonce'), -12, 10);
		} else {
			return bb_create_nonce($action);
		}
	}

	/**
	 * Callback to set up the admin configuration page.
	 */
	public function event_bb_admin_menu_generator()
	{
		bb_admin_add_submenu(__('Loginless Posting', 'loginless-posting'), 'administrate', 'loginless_posting_options_page', 'options-general.php');
	}

	/**
	 * Handle events on the get_post_author action hook.
	 * @param string $name The username.
	 * @param int $user_id The user's id.
	 */
	public function event_get_post_author($name = '', $user_id = 0)
	{
		if ( $this->_anon_user_id == $user_id ) {
			$post = bb_get_post(get_post_id());
			$post = bb_append_meta($post, 'post');

			// can't use bb_get_postmeta for now thanks to a bug.  See trac #1077.
			// $_name = bb_get_postmeta( get_post_id(), '_anon_user_name' );
			$_name = $post->_anon_user_name;

			if ( ! empty( $_name ) ) {
				$name = $_name;
			}
		}
		return $name;	
	}

	/**
	 * Handle events on the 'bb_got_roles' action hook 
	 */
	public function event_bb_got_roles()
	{
		if ( ! bb_is_admin() ) {
			$this->_extend_wp_users_object();
		}
	}
	
	/**
	 * Callback function for bb_new_post action.
	 * @param int $post_id The id of the new post.
	 */
	public function event_bb_new_post($post_id = 0)
	{
		global $bb;
		$post = bb_get_post( $post_id );

		// if the "author" is the anonymous id, then let's save the name in the post meta 
		if ( $this->_anon_user_id == $post->poster_id ) {
			$email = wp_specialchars(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
			$name = wp_specialchars(filter_var($_POST['author'], FILTER_SANITIZE_STRING));
			$url = filter_var($_POST['url'], FILTER_SANITIZE_URL);

			$cookiehash = md5(bb_get_option( 'uri' ));
			$comment_cookie_lifetime = apply_filters('comment_cookie_lifetime', 30000000);
			setcookie('comment_author_' . $cookiehash, $name, time() + $comment_cookie_lifetime, $bb->cookiepath, $bb->cookiedomain);
			setcookie('comment_author_email_' . $cookiehash, $email, time() + $comment_cookie_lifetime, $bb->cookiepath, $bb->cookiedomain);
			setcookie('comment_author_url_' . $cookiehash, esc_url($url), time() + $comment_cookie_lifetime, $bb->cookiepath, $bb->cookiedomain);
	
			// associate the user of this post with correct username / email
			bb_update_postmeta( $post_id, '_anon_user_name', $name );
			bb_update_postmeta( $post_id, '_anon_user_email', $email );
			bb_update_postmeta( $post_id, '_anon_user_url', $url );
		}
	}

	/** 
	 * Callback function for bb_new_topic action.
	 * @param int $topic_id The id of the new / updated topic.
	 */
	public function event_bb_new_topic($topic_id = 0) 
	{
		$topic = get_topic( $topic_id, false ); // false for no caching

		// if using the anonymous user id, then let's save the name in the meta table
		if ( $this->_anon_user_id == $topic->topic_poster ) {
			$email = wp_specialchars(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
			$name = wp_specialchars(filter_var($_POST['author'], FILTER_SANITIZE_STRING));
			
			// let's associate that email address with the posted user's name
			bb_update_topicmeta( $topic_id, '_anon_user_name-' . $email, $name );
		}
	}

	public function event_plugin_activation()
	{
		// get the anonymous user, if it exists	
		$anon_id = bb_get_option('loginless-anon-id');
		if ( empty( $anon_id ) ) {
			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}
			$user_id = bb_new_user( $this->_user_login_base, $this->_user_login_base . '@' . $sitename, bb_get_option('url'));	
			if ( ! is_wp_error($user_id) && ! empty($user_id) ) {
				$this->_anon_user_id = intval($user_id);
				return bb_update_option('loginless-anon-id', intval($user_id));
			}
		}
	}

	public function event_pre_post_form() 
	{
		$req = intval(bb_get_option('require_name_email'));

		$cookiehash = md5(bb_get_option( 'uri' ));
		$comment_author = isset($_COOKIE['comment_author_'.$cookiehash]) ? $_COOKIE['comment_author_'.$cookiehash] : '';
		$comment_author_email = isset($_COOKIE['comment_author_email_'.$cookiehash]) ? $_COOKIE['comment_author_email_'.$cookiehash] : '';
		$comment_author_url = isset($_COOKIE['comment_author_url_'.$cookiehash]) ? $_COOKIE['comment_author_url_'.$cookiehash] : '';

		$plugin_options = (array) bb_get_option('loginless_posting_settings');

		ob_start(); // we have to use output buffering to replace the post form nonce. yay!
		if ( empty( $plugin_options['disable-auto-fields'] ) ) : 
			?>
			<p>
				<input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" size="22" <?php if ($req) echo "aria-required='true'"; ?> />
				<label for="author"><small><?php 
					printf(	
						__('Name %s', 'loginless-posting'),
						( $req ? '(required)' : '' ) 
					); 
				?></small></label>
			</p>
				
			<p>
				<input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" size="22" <?php if ($req) echo "aria-required='true'"; ?> />
				<label for="email"><small><?php
					printf(
						__('Mail (will not be published) %s', 'loginless-posting'),
						( $req ? '(required)' : '' )
					);
				?></small></label>
			</p>

			<p>
				<input type="text" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" size="22" />
				<label for="url"><small><?php _e('Website', 'loginless-posting'); ?></small></label>
			</p>

			<?php
		endif; // if we haven't disabled automatic name, email, url fields
	}

	public function event_post_post_form() 
	{
		$content = ob_get_clean();
		// need to move "<form..>" to the beginning of $content
		if ( preg_match('#(<form[^>]*>)#s', $content, $matches) ) {
			$content = str_replace($matches[0], '', $content);
			$content = $matches[0] . "\n" . '<input type="hidden" name="loginless-form-submission" id="loginless-form-submission" value="1" />' . "\n" . $content;
		}
		if ( bb_is_topic() ) {
			$new_field = $this->get_nonce('create-post_' . get_topic_id());
		} else {
			$new_field = $this->get_nonce('create-topic');
		}
		if ( ! empty( $new_field ) ) {
			$content = preg_replace('#(_wpnonce[^>]*value=")([^"]*)#', '${1}' . $new_field, $content);
		}
		echo $content;
	}
	/**
	 * Callback method on the get_topic_author filter.
	 * @param string $name The name
	 * @param int $user_id The id of the user.
	 * @return string The name of the user.
	 */
	public function filter_get_topic_author($name = '', $user_id = 0)
	{
		if ( $this->_anon_user_id == $user_id ) {
			$topic = get_topic(get_topic_id());			
			$_name = bb_get_topicmeta( $topic->topic_id, '_anon_user_name-' . $topic->topic_poster_name );
			if ( ! empty( $_name ) ) {
				$name = $_name;
			}
		}
		return $name;
	}

	/**
	 * Callback method on the 'get_topic_last_poster' filter.
	 * @param string $name The name.
	 * @param int $uesr_id The id of the user.
	 * @return string The name of the user.
	 */
	public function filter_get_topic_last_poster($name = '', $user_id = 0)
	{
		if ( $this->_anon_user_id == $user_id ) {
			$topic = get_topic(get_topic_id());			
			$_name = bb_get_postmeta( $topic->topic_last_post_id, '_anon_user_name' );
			if ( ! empty( $_name ) ) {
				$name = $_name;
			}
		}
		return $name;
	}

	/**
	 * Callback filter for bb_get_user_email
	 * @param string $email The email of the user
	 * @param int $user_id The id of the user
	 * @return string The email of the user.
	 */
	public function filter_bb_get_user_email($email = '', $user_id = 0)
	{
		// let's try to get the posted email
		$post_id = get_post_id();
		if ( ! empty( $post_id ) ) {
			$_email = bb_get_post_meta( '_anon_user_email', $post_id );
			if ( ! empty( $_email ) ) {
				$email = $_email;
			}
		}
		return $email;
	}

	/**
	 * Callback filter for bb_get_user_link
	 * @param string $link The link of the user
	 * @param int $user_id The id of the user
	 * @return string The link of the user.
	 */
	public function filter_bb_get_user_link($link = '', $user_id = 0)
	{
		// let's try to get the posted link
		$post_id = get_post_id();
		if ( ! empty( $post_id ) ) {
			$_link = bb_get_post_meta( '_anon_user_url', $post_id );
			if ( ! empty( $_link ) ) {
				$link = $_link;
			}
		}
		return $link;
	}

	/**
	 * Prints the admin options page
	 */
	public function print_options_page()
	{
		$plugin_options = (array) bb_get_option('loginless_posting_settings');
		if ( ! empty( $_POST['loginless-posting-update'] ) ) {
			bb_check_admin_referer( 'loginless-posting-update' );
			$disabled = intval($_POST['disable-auto-fields']);
			$plugin_options['disable-auto-fields'] = $disabled;
			if ( bb_update_option('loginless_posting_settings', $plugin_options) ) :
				bb_admin_notice(__('Settings updated', 'loginless-posting'));
			else :
				bb_admin_notice(__('Settings not changed', 'loginless-posting'));
			endif;
		}

		?>
		<h2><?php _e('Loginless Posting Settings', 'loginless-posting'); ?></h2>
		<?php do_action( 'bb_admin_notices' ); ?>

		<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'loginless_posting_options_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
			<input type="hidden" name="loginless-posting-update" value="1" />

			<fieldset>
				<div id="option-disable-auto-fields">
					<div class="label">
						<?php _e('Disable Automatic Form Fields', 'loginless-posting'); ?>
					</div>

					<div class="inputs">
						<label class="checkboxs">
							<?php $disabled_check = ( isset($plugin_options['disable-auto-fields']) && 1 == $plugin_options['disable-auto-fields'] ) ? ' checked="checked"' : '';
							?>
							<input <?php echo $disabled_check; ?> type="checkbox" value="1" id="disable-auto-fields" name="disable-auto-fields" class="checkbox"/> <?php _e('Don&rsquo;t automatically add the author, email, and URL fields to the post form.  Checking this allows you to use your own markup for those fields in the <code>post-form.php</code> template file.'); ?>
						</label>
					</div>
				</div>

			</fieldset>

			<fieldset class="submit">
				<?php bb_nonce_field( 'loginless-posting-update' ); ?>
				<input type="hidden" name="action" value="update-akismet-settings" />
				<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
			</fieldset>
		</form>
		<?php
	}
}

class LoginLess_WP_Users extends WP_Users {
	/**
	 * Get a user.  Use parent method except for anonymous users.
	 * @param mixed $user_id Something identifying the user, such as username or ID, as specified in the $args.
	 * @param array $args Arguments
	 */
	public function get_user( $user_id = 0, $args = null )
	{
		// only mess around with this in the situation of a non-logged-in user posting a topic or post
		if ( ! empty($_POST['loginless-form-submission']) && in_array($user_id, array(0, $this->_anon_user_id)) ) {
			$email = wp_specialchars($_POST['email']);
			$name = wp_specialchars($_POST['author']);
			if ( empty( $email ) || empty( $name ) || ! is_email( $email ) ) {
				bb_die( __('Error: please fill the required fields (name, email).') );
			}
			$user = parent::get_user($this->_anon_user_id);
			$user->user_login = $email;
			return $user;
		// or if somebody's requesting the anonymous user, let's try getting it by post instead
		} elseif ( $user_id == $this->_anon_user_id ) {
			$post_id = get_post_id();
			if ( ! empty( $post_id ) ) {
				$user = parent::get_user($this->_anon_user_id);
				$user->user_url = bb_get_post_meta( '_anon_user_url', $post_id );
				return $user;
			}
		}
		return parent::get_user($user_id, $args);
	}
	
}

class LoginLess_WP_Auth extends WP_Auth {
	public function validate_auth_cookie( $cookie = null, $scheme = 'auth' )
	{
		// only mess around with this in the situation of a non-logged-in user posting a topic or post
		if ( ! empty($_POST['loginless-form-submission']) 
			&& empty( $cookie ) 
			&& 'logged_in' == $scheme 
			&& ( $_db = loginless_posting_get_backtrace_functions() ) 
			&& in_array('bb_auth', array_keys($_db) )
			&& false !== strpos($_db['bb_auth']['file'], 'bb-post.php' ) ) {
				return true;
		} else {
			return parent::validate_auth_cookie($cookie, $scheme);
		}
	}
}

function loginless_posting_get_backtrace_functions()
{
	$_db_funcs = array();
	foreach( (array) debug_backtrace() as $bt ) {
		if ( ! empty( $bt['function'] ) ) {
			$_db_funcs[$bt['function']] = $bt;
		}
	}
	return $_db_funcs;
}

$loginless_posting_factory = new LoginLess_Posting;

function loginless_posting_options_page()
{
	global $loginless_posting_factory;
	return $loginless_posting_factory->print_options_page();
}
