/*
 AtD Javascript for
 After the Deadline - Spell Checker Plugin
 (for bbPress) by www.gaut.am
*/

function restoreTextArea()
{
    /* clear the error HTML out of the preview div */
    AtD.remove('post_content'); 

    /* swap the preview div for the textarea, notice how I have to restore the appropriate class/id/style attributes */
    jQuery('#post_content').replaceWith('<textarea name="post_content" cols="50" rows="8" id="post_content" tabindex="3">' + jQuery('#post_content').html() + '</textarea>');

    /* change the link text back to its original label */
    jQuery('#checkLink').text('Check Spelling');
};

/* where the magic happens, checks the spelling or restores the form */
function check()
{
    jQuery(function()
    {
        /* If the text of the link says edit comment, then restore the textarea so the user can edit the text */
        if (jQuery('#checkLink').text() == 'Edit Text')
        {                               
            restoreTextArea(); 
        } 
        else 
        {
            /* set the spell check link to a link that lets the user edit the text */
            jQuery('#checkLink').text('Edit Text');

            /* replace the textarea with a preview div, notice how the div has to have the same id/class/style attributes as the textarea */
            jQuery('#post_content').replaceWith('<div class="input" id="post_content">' + jQuery('#post_content').val() + '</div>');

            /* check the writing in the textarea */
            AtD.checkCrossAJAX('post_content',  
            {
                success: function(errorCount) 
                {
                   if (errorCount == 0)
                   {
                      alert("No writing errors were found");
                   }

                   /* once all errors are resolved, this function is called, it's an opportune time
                      to restore the textarea */
                   restoreTextArea();
                },

                error: function(reason)
                {
                   alert("There was an error communicating with the spell checking service.\n\n" + reason);

                   /* restore the text area since there won't be any highlighted spelling errors */
                   restoreTextArea();
                }
            });
        }
    });
}