<?php
/*
Plugin Name: bb-emoticons
Plugin URI: none
Description: Simple and plain smiley function for bbPress
Author: Arnan de Gans
Version: 1.0
Author URI: http://meandmymac.net
*/
function convert_smilies($text) {
	$smilies = array(
		':mrgreen:' => 'icon_mrgreen.gif',
		':neutral:' => 'icon_neutral.gif',
		':twisted:' => 'icon_twisted.gif',
		':arrow:' => 'icon_arrow.gif',
		':evil:' => 'icon_evil.gif',
		':idea:' => 'icon_idea.gif',
		':oops:' => 'icon_redface.gif',
		':roll:' => 'icon_rolleyes.gif',
		':cry:' => 'icon_cry.gif',
		':lol:' => 'icon_lol.gif',
		':mad:' => 'icon_mad.gif',
		'8-)' => 'icon_cool.gif',
		'8-O' => 'icon_eek.gif',
		':-(' => 'icon_sad.gif',
		':-)' => 'icon_smile.gif',
		':-?' => 'icon_confused.gif',
		':-D' => 'icon_biggrin.gif',
		':-P' => 'icon_razz.gif',
		':-o' => 'icon_surprised.gif',
		':-x' => 'icon_mad.gif',
		':-|' => 'icon_neutral.gif',
		';-)' => 'icon_wink.gif',
		'8)' => 'icon_cool.gif',
		':(' => 'icon_sad.gif',
		':)' => 'icon_smile.gif',
		':?' => 'icon_confused.gif',
		':D' => 'icon_biggrin.gif',
		':P' => 'icon_razz.gif',
		':o' => 'icon_surprised.gif',
		':x' => 'icon_mad.gif',
		':|' => 'icon_neutral.gif',
		';)' => 'icon_wink.gif',
		':!:' => 'icon_exclaim.gif',
		':?:' => 'icon_question.gif',
	);

	foreach($smilies as $smiley => $img) {
		$wp_smiliessearch[] = $smiley;
		$smiley_masked = htmlspecialchars( trim($smiley) , ENT_QUOTES);
		$wp_smiliesreplace[] = " <img src='/bb-images/smilies/$img' alt='$smiley_masked' class='wp-smiley' /> ";
	}
	$output = '';

	$textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$stop = count($textarr);
	
	for ($i = 0; $i < $stop; $i++) {
		$content = $textarr[$i];
		if ((strlen($content) > 0) && ('<' != $content{0})) { 
			$content = str_replace($wp_smiliessearch, $wp_smiliesreplace, $content);
		}
		$output .= $content;
	}
	return $output;
}

function bb_allowed_emoticons() {
	$smilies = array(
		':mrgreen: ' => 'icon_mrgreen.gif',
		':neutral: ' => 'icon_neutral.gif',
		':twisted: ' => 'icon_twisted.gif',
		':arrow: ' => 'icon_arrow.gif',
		':evil: ' => 'icon_evil.gif',
		':idea: ' => 'icon_idea.gif',
		':oops: ' => 'icon_redface.gif',
		':roll: ' => 'icon_rolleyes.gif',
		':cry: ' => 'icon_cry.gif',
		':lol: ' => 'icon_lol.gif',
		':mad: ' => 'icon_mad.gif',
		'8) ' => 'icon_cool.gif',
		':( ' => 'icon_sad.gif',
		':) ' => 'icon_smile.gif',
		':? ' => 'icon_confused.gif',
		':D ' => 'icon_biggrin.gif',
		':P ' => 'icon_razz.gif',
		':o ' => 'icon_surprised.gif',
		':x ' => 'icon_mad.gif',
		':| ' => 'icon_neutral.gif',
		';) ' => 'icon_wink.gif',
		':!: ' => 'icon_exclaim.gif',
		':?: ' => 'icon_question.gif',
	);
	?>
<script language="Javascript">
smilies_textarea = document.getElementsByTagName('textarea')[0];

function add2text(textToAdd) {
	smilies_textarea.value += textToAdd;
    smilies_textarea.focus();
}
</script>
<?php
	foreach($smilies as $code => $icon ) {
		echo "<a onClick=\"add2text('$code')\"><img src=\"/bb-images/smilies/$icon\" alt=\"$code\" title=\"$code\" /></a> ";
	}
}

$smiliesadd_filter('post_text', 'convert_smilies');
?>