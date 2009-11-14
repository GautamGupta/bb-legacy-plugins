function bbld_default_templates(template) {
	var default_template;
	switch(template) {
		case "postpage_header":
			default_template = "<div id=\"discussions\"><h2>%BBLD_TITLE%</h2><table id=\"latest\"><tr><th>%BBLD_TOPIC%</th><th>%BBLD_POST%</th><th>%BBLD_LPOSTER%</th></tr>";
			break;
		case "postpage_body":
			default_template = "<tr class=\"%BBLD_CLASS%\"><td><a href=\"%BBLD_URL%\">%BBLD_TOPIC%</a></td><td class=\"num\">%BBLD_POST%</td><td class=\"num\">%BBLD_LPOSTER%</td></tr>";
			break;
		case "postpage_footer":
			default_template = "</table></div>";
			break;
		case "sidebar_title":
			default_template = "<h2>Forum Last %BBLD_LIMIT% Discussions</h2>";
			break;
		case "sidebar_display":
			default_template = "<li><a href=\"%BBLD_URL%\">%BBLD_TOPIC%</a> (%BBLD_POST%)<br /><small>Last Post By: %BBLD_LPOSTER%<br/>Inside: <a href=\"%BBLD_FURL%\">%BBLD_FORUM%</a></small></li>";
			break;
		case "sidebar_forum_title":
			default_template = "<h2>Forum List</h2>";
			break;
		case "forum_sidebar_display":
			default_template = "<li><a href=\"%BBLD_FURL%\">%BBLD_FORUM%</a> (%BBLD_TOPIC%)<br /><small>%BBLD_FDESC%</small></li>";
			break;
		case "forum_postpage_header":
			default_template = "<div id=\"discussions\"><h2>Forums</h2><table id=\"latest\"><tr><th>Main Theme</th><th>Topics</th><th>Posts</th></tr>";
			break;
		case "forum_postpage_body":
			default_template = "<tr class=\"%BBLD_CLASS%\"><td><a href=\"%BBLD_FURL%\">%BBLD_FORUM%</a></td><td class=\"num\">%BBLD_TOPIC%</td><td class=\"num\">%BBLD_POST%</td></tr>";
			break;
		case "forum_postpage_footer":
			default_template = "</table></div>";
			break;
	}
	document.getElementById("bbld_" + template).value = default_template;
}
function index() {
	// Tab
	document.getElementById("PostPageTab").className = "SelectedTab";
	document.getElementById("SidebarTab").className = "Tab";
	document.getElementById("ForumTab").className = "Tab";
	document.getElementById("ForumPostPageTab").className = "Tab";
	// Page
	document.getElementById("PostPage").style.display= "block";
	document.getElementById("Sidebar").style.display = "none";
	document.getElementById("Forum").style.display = "none";
	document.getElementById("ForumPostPage").style.display = "none";
}
function sidebar() {
	// Tab
	document.getElementById("PostPageTab").className = "Tab";
	document.getElementById("SidebarTab").className = "SelectedTab";
	document.getElementById("ForumTab").className = "Tab";
	document.getElementById("ForumPostPageTab").className = "Tab";
	// Page
	document.getElementById("PostPage").style.display= "none";
	document.getElementById("Sidebar").style.display = "block";
	document.getElementById("Forum").style.display = "none";
	document.getElementById("ForumPostPage").style.display = "none";
}
function forum() {
	// Tab
	document.getElementById("PostPageTab").className = "Tab";
	document.getElementById("SidebarTab").className = "Tab";
	document.getElementById("ForumTab").className = "SelectedTab";
	document.getElementById("ForumPostPageTab").className = "Tab";
	// Page
	document.getElementById("PostPage").style.display= "none";
	document.getElementById("Sidebar").style.display = "none";
	document.getElementById("Forum").style.display = "block";
	document.getElementById("ForumPostPage").style.display = "none";
}
function forum_page() {
	// Tab
	document.getElementById("PostPageTab").className = "Tab";
	document.getElementById("SidebarTab").className = "Tab";
	document.getElementById("ForumTab").className = "Tab";
	document.getElementById("ForumPostPageTab").className = "SelectedTab";
	// Page
	document.getElementById("PostPage").style.display= "none";
	document.getElementById("Sidebar").style.display = "none";
	document.getElementById("Forum").style.display = "none";
	document.getElementById("ForumPostPage").style.display = "block";
}