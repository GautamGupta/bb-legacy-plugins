<?php

/**
 * Template Name: bbPM - Inbox
 *
 * @package bbPM
 * @subpackage Theme
 */

global $bbPM;
if ( empty( $bbPM ) ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	require_once get_404_template();
	return;
}

$bbPM->handle_inbox();

?>

<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<?php do_action( 'bbp_template_notices' ); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<div id="bbp-new-topic" class="bbp-new-topic">
						<h1 class="entry-title"><?php bbp_title_breadcrumb(); ?></h1>
						<div class="entry-content">

							<?php the_content(); ?>

							<?php if ( is_user_logged_in() && bbp_has_topics( array(
									'post_status'  => $bbPM->pm_status_id,
									'meta_query'   => array(
										array( 
											'key'      => $bbPM->allowed_viewer_key,
											'value'    => get_current_user_id(),
											'type'     => 'UNSIGNED'
										)
									),
									'post_parent' => '0'
								) ) ): ?>

								<?php bbp_set_query_name( 'bbpm' ); ?>

								<?php get_template_part( 'bbpress/loop', 'topics' ); ?>

							<?php endif; ?>

							<?php if ( is_user_logged_in() && current_user_can( 'publish_topics' ) ) : ?>

								<div id="new-topic-<?php bbp_topic_id(); ?>" class="bbp-topic-form">

									<form id="new_post" name="new_post" method="post" action="">
										<fieldset>
											<legend>

												<?php _e( 'Send new private message', 'bbpm' ); ?>

											</legend>

											<div>
												<div class="alignright avatar">

													<?php bbp_current_user_avatar( 120 ); ?>

												</div>

												<p>
													<label for="bbp_topic_title"><?php _e( 'Message Title:', 'bbpm' ); ?></label><br />
													<input type="text" id="bbp_topic_title" value="<?php echo ( !empty( $_POST['bbp_topic_title'] ) ) ? esc_attr( $_POST['bbp_topic_title'] ) : ''; ?>" tabindex="<?php bbp_tab_index(); ?>" size="40" name="bbp_topic_title" />
												</p>

												<p>
													<label for="bbp_topic_content"><?php _e( 'Message Content:', 'bbpm' ); ?></label><br />
													<textarea id="bbp_topic_content" tabindex="<?php bbp_tab_index(); ?>" name="bbp_topic_content" cols="52" rows="6"><?php echo ( !empty( $_POST['bbp_topic_content'] ) ) ? esc_html( $_POST['bbp_topic_content'] ) : ''; ?></textarea>
												</p>

												<p class="form-allowed-tags">
													<label><?php _e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:','bbpress' ); ?></label><br />
													<code><?php bbp_allowed_tags(); ?></code>
												</p>

												<p>
													<label for="bbpm_send_to"><?php _e( 'To (Separate multiple names with commas):', 'bbpm' ); ?></label><br />
													<input type="text" value="<?php echo ( !empty( $_POST['bbpm_send_to'] ) ) ? esc_attr( $_POST['bbpm_send_to'] ) : ''; ?>" tabindex="<?php bbp_tab_index(); ?>" size="40" name="bbpm_send_to" id="bbpm_send_to" />
												</p>

												<?php if ( bbp_is_subscriptions_active() ) : ?>

													<p>
														<input name="bbp_topic_subscription" id="bbp_topic_subscription" type="checkbox" value="bbp_subscribe" tabindex="<?php bbp_tab_index(); ?>" />
														<label for="bbp_topic_subscription"><?php _e( 'Notify me of follow-up replies via email', 'bbpress' ); ?></label>
													</p>

												<?php endif; ?>

												<div class="bbp-submit-wrapper">
													<button type="submit" tabindex="<?php bbp_tab_index(); ?>" id="bbp_topic_submit" name="bbp_topic_submit"><?php _e( 'Submit', 'bbpress' ); ?></button>
												</div>
											</div>

											<input type="hidden" name="action" id="bbpm_post_action" value="bbpm-new-thread" />

											<?php

											if ( current_user_can( 'unfiltered_html' ) )
												wp_nonce_field( 'bbpm-unfiltered-html-thread_new', '_bbpm_unfiltered_html_thread' );

											?>

											<?php wp_nonce_field( 'bbpm-new-thread' ); ?>

										</fieldset>
									</form>
								</div>

							<?php else : ?>

								<div id="no-topic-<?php bbp_topic_id(); ?>" class="bbp-no-topic">
									<h2 class="entry-title"><?php _e( 'Sorry!', 'bbpress' ); ?></h2>
									<div class="entry-content"><?php is_user_logged_in() ? _e( 'You cannot send new private messages at this time.', 'bbpm' ) : _e( 'You must be logged in to send private messages.', 'bbpm' ); ?></div>
								</div>


							<?php endif; ?>

						</div>
					</div><!-- #bbp-new-topic -->

				<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
