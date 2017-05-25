<?php
/**
 * Load the i18n class and the corresponding language files
 *
 * @package		ProjectSend
 * @subpackage	Language
 */

/**
 * Current system language defined in the configuration file
 * Loaded language falls back to this value if neither of the
 * following 2 options is valid.
 *
 * @see sys.config.sample.php
 */
$lang = SITE_LANG;

/**
 * If a user selected a language on the log in form, use it
 */
if ( isset( $_SESSION['lang'] ) ) {
	$lang_sess = $_SESSION['lang'];
	$lang_file		= ROOT_DIR . '/lang/' . $lang_sess . '.mo';
	if ( file_exists( $lang_file ) ) {
		$lang = $lang_sess;
	}
}
/**
 * If not, check if the admin selected the option to use
 * the browser's language (if available)
 */
else {
	switch ( USE_BROWSER_LANG ) {
		case '0':
		default:
			break;
		case '1':
			$browser_lang	= substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			$lang_file		= ROOT_DIR . '/lang/' . $browser_lang . '.mo';
			if ( file_exists( $lang_file ) ) {
				$lang = $browser_lang;
			}
			break;
	}
}

define('LOADED_LANG', $lang);
define('I18N_DEFAULT_DOMAIN', 'cftp_admin');
require_once(ROOT_DIR.'/includes/classes/i18n.php');
I18n::LoadDomain(ROOT_DIR."/lang/{$lang}.mo", 'cftp_admin' );
?>