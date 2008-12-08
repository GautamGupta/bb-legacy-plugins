<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Search')?></h3>

<div class="indent">

<form class="SuperSearch" name="SuperSearch" onsubmit="this.submit.disabled=true;" method="get" action="<?php echo preg_replace( '|(/page/[0-9]+?)|', '', $_SERVER["REQUEST_URI"]); ?>">

	<?php SSinput('search'); SSinput('submit'); SSinput('reset'); SSinput('simple'); ?>	

	<div class="SSbreak"></div>

	<?php SSinput('exact'); SSinput('posts'); SSinput('highlight');   SSinput('case'); SSinput('regex'); ?>
	
	<div class="SSbreak"></div>

	<div  id="SSadvanced">

		<?php SSinput('users');  SSinput('located'); SSinput('forums'); ?> 

		<div class="SSbreak"></div>		

		<?php SSinput('maxcount'); SSinput('age'); SSinput('direction');  ?>

		<div class="SSbreak"></div>
	
		<?php SSinput('sort');  SSinput('order'); ?>
		
		<div class="SSbreak"></div>
	
	</div>

</form>

</div>
