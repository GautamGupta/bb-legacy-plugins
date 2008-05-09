
This plugin requires small modification the bb-settings.php file.

Code

// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(BB_PATH . BB_INC . 'locale.php');
$bb_locale = new BB_Locale();

$bb_roles  = new BB_Roles();
do_action('bb_got_roles', '');

should be replaced with

$bb_roles  = new BB_Roles();
do_action('bb_got_roles', '');

// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(BB_PATH . BB_INC . 'locale.php');
$bb_locale = new BB_Locale();