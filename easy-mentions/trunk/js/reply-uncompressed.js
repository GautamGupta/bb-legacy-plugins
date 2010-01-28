/**
 * Javascript file (uncompressed)
 *
 * @package Easy Mentions
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/easy-mentions/
 * @see reply.js for compressed version
 */

jQuery(document).ready(function() {
    jQuery(".reply_link").click(function () {
        var original = jQuery("#post_content").val();
        if( original != ''){
            original = original + '\n\n';
        }
        var text = original+'<em>@<a href="'+jQuery(this).parent().children('a').get(0)+'">'+jQuery(this).parent().parent().parent().children('.threadauthor').children('p').children('strong').children('a').text()+'</a></em>\n\n';
        jQuery("#post_content").val(text).focus();
        jQuery(document).scrollTop(jQuery('#postform').offset().top)
    });
});