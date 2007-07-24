Plugin Name: Show Top Posters
Plugin URI: http://www.gospelrhys.co.uk/bbpress-plugin-show-top-posters
Description:  Readers with the most postes are displayed on your bbpress forum, with their names (linked to their website if they provided one). Based on the Show Top Commenters plugin for Wordpress by Nate Sanden
Version: 1.0
Author: Rhys Wynne
Author URI: http://www.gospelrhys.co.uk


Installation Instructions: To install, create a “my-plugins” directory within the bbpress root directory. Extract the “top-posters.php” file into the directory you have created. You can then go to the “Site Management” Directory within the bbpress Administration, and click “activate”.

Wherever you want your “Top Posters” List Displayed, add the following code to your template.

    <ul><?php show_top_posters(); ?></ul>



Options

The options are available within an array in the plugin, you need to edit the top-posters.php file to change them.
   
"reset": reset hourly, daily, weekly, monthly, yearly, all
"limit": maximum number of commentator's to show
"filter_urls": commma seperated list of full or partial URL's (www.badsite.com,etc)
"none_text": if there are no commentators, what should we display?
"make_links": link the commentator's name to thier website? (1) or Profile? (2)
"pretty_permalinks": do you have "pretty permalinks" for your profile (1)? (http://www.example.com/profile/username) or not (2)? (http://www.example.com/profile.php?id=1) 
"name_limit": maximum number of characters a commentator's name can be, before we cut it off with an ellipse
"start_html": Beginning of each entry on the list (default <li>)
"end_html": End of each entry on the list (default </li>)
"show_posts": Shows the amount of posts people have made or just list their name.


Help Forum: http://www.gospelrhys.co.uk/forum 



Shameless Begging: If you like this plugin, I would appreciate a linkback to http://www.gospelrhys.co.uk, and I'm sure Nate would like a link to either http://www.savingadvice.com or http://www.pfadvice.com on your blog.

