<?php bb_do_action('bb_grins'); ?>
<p>
	<?php _e('Your message will be sent to'); ?>
	<?php echo $pmuser->user_login; ?>
	<input name="userid" type="hidden" id="userid" value="<?php echo $pmuser->ID; ?>" />
</p>

<p>
	<label for="topic"><?php _e('Message Title'); ?>
		<input name="title" type="text" id="title" size="50" maxlength="80" tabindex="1" />
	</label>
</p>

<p>
	<label for="post_content"><?php _e('Message:'); ?>
		<textarea name="post_content" cols="50" rows="8" id="post_content" tabindex="3"></textarea>
	</label>
</p>
<p class="submit">
  <input type="submit" id="pmformsub" name="Submit" value="<?php _e('Send Message'); ?> &raquo;" tabindex="4" />
</p>

<p><?php _e('Allowed markup: <code>a em strong code ul ol li blockquote</code>. <br />Put code in between <code>`backticks`</code>.'); ?></p>
