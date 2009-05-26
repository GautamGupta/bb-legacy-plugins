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
	}
	document.getElementById("bbld_" + template).value = default_template;
}
function index() {
	// Tab
	document.getElementById("PostPageTab").className = "SelectedTab";
	document.getElementById("SidebarTab").className = "Tab";
	// Page
	document.getElementById("PostPage").style.display= "block";
	document.getElementById("Sidebar").style.display = "none";
}
function sidebar() {
	// Tab
	document.getElementById("PostPageTab").className = "Tab";
	document.getElementById("SidebarTab").className = "SelectedTab";
	// Page
	document.getElementById("PostPage").style.display= "none";
	document.getElementById("Sidebar").style.display = "block";
}