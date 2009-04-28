<ol id="thread"><li>
<?php
bb_post_template();
?>
</li></ol>
<h2><?php _e( 'Report this post', 'bbpress-moderation-suite' ); ?></h2>
<p>
	<label for="report_reason"><?php _e( 'What is your reason for reporting this post?', 'bbpress-moderation-suite' ); ?>
		<select name="report_reason" id="report_reason" tabindex="1">
<?php foreach ( bbmodsuite_report_reasons() as $id => $reason ) { ?>
			<option value="<?php echo $id; ?>"><?php echo $reason; ?></option>
<?php } ?>
			<option value="0" selected="selected"><?php _e( 'Other', 'bbpress-moderation-suite' ); ?></option>
		</select>
	</label>
</p>
<p>
	<label for="report_content"><?php _e( 'Please give more information: (Plain text only, no HTML allowed.)', 'bbpress-moderation-suite' ); ?>
		<textarea name="report_content" cols="50" rows="8" id="report_content" tabindex="2"></textarea>
	</label>
</p>
<p class="submit">
  <input type="submit" id="postformsub" name="Submit" value="<?php echo attribute_escape( __( 'Send Report &raquo;', 'bbpress-moderation-suite' ) ); ?>" tabindex="3" />
</p>