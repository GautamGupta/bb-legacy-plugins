<?php $name = isset($_GET['name']) ? attribute_escape( $_GET['name'] ) : ''; ?>
<p>
	<label for="topic">Plugin Name (required)<br />
		<input name="topic" type="text" id="topic" size="50" maxlength="80" tabindex="1" value="<?php echo $name; ?>" />
	</label>
</p>

<?php do_action( 'post_form_pre_post' ); ?>

<p>
	<label for="post_content">Plugin Description (required)
		<textarea name="post_content" cols="50" rows="8" id="post_content" tabindex="3"></textarea>
	</label>
</p>

<p>
	<label for="request_url">Plugin URL<br />
		<input name="request_url" type="text" id="request_url" size="50" maxlength="80" tabindex="4" />
	</label>
</p>

<p class="submit">
	<input type="hidden" name="action" value="submit-request" />
	<?php bb_nonce_field( 'submit-request', '_svn_request_nonce' ); ?>
	<input type="submit" id="postformsub" name="Submit" value="<?php echo attribute_escape( __('Send Post &raquo;') ); ?>" tabindex="5" />
</p>
