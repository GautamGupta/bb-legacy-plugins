=== bbPages 0.0.1 ===
Tested up to: 0.9.2
Licence: GPL

Allows you to create static pages within your bbPress forum.

== Description ==

This plugin allows you to create, edit and delete static pages within your bbPress forum, using simple managment panel, and then display them on your forum :). You can see demo of this plugins here: http://forums.astateofmind.eu/

== Installation ==

1. Unpackage all files to your `/my-plugins/` directory.
2. Move 'bb-pages' directory o to your `/my-plugins/` directory.
3. Move 'page.php' from `root` folder to your bbpress root directory (where your bb-config.php is located)
4. Move 'page.php' from `template` folder to your template folder (e.g. 'kakumei')
5. Go to your administration panel and activate plugin.
6. Go to the 'Manage Pages' tab and click 'Create new page' - fill it with data and save changes.
7. You're ready :).

== Frequently Asked Questions ==

Question: Does the plugin support MultiViews and pretty permalinks?
Answer: Nope, not yet, but I'm working on it. If you know how to make this "support" possible, please contact me :).

Q: How do I create a link to my page?
A: Notice the ID number of your page, then use the following "syntax": `http://domain.com/page.php?page_id=X`, where 'X' is your page ID.

Q: How do I change how my page looks like?
A: Look for 'page.php' within your template folder. You can change the look there. Look below for plugin API.

== Plugin API ==

- < ?php isset_id(); ? > - returns true if $_GET['page_id'] equals 1 or more; return false if $_GET['page_id'] equals 0 or none;
- < ?php page_exist(); ? > - returns true if page with $_GET['page_id'] exist in database;
- < ?php get_page_title(); ? > - returns page_title from database, use 'echo' to display it;
- < ?php get_page_content(); ? > - returns page_content from database, use 'echo' to display it;
- < ?php get_page_slug(); ? > - returns page_slug from database, use 'echo' to display it;

== TO DO ==

Below you can see a list of things I'm going to do when people will enjoy this plugin enough to donate some dollars for plugin development.

- Add MultiViews support (pretty permalinks);
- Add dynamic <title> tag in HEAD section for better SEO;
- Add "Textile" support along with WYSIWYG editor;
- Create plugin API, so users could page function e.g. <?php get_page("1"); ?> to create link to page with id=1;
- Create function displaying all pages links (like wp_list_pages(); );
- Create dynamic, AJAX-powered-change-pages-order function ;); 
- Create more friendly managment panel (probably when bbPress 1.0 will be ready);

== Plugin History ==

0.0.1 - Initial release (August 2, 2008) - basic features: add, edit, delete and display pages.
