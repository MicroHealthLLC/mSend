<?php
/**
 * Requirements of basic system files.
 *
 * @package ProjectSend
 * @subpackage Core
 */

define('ROOT_DIR', dirname(__FILE__));

/** PhpPass */
require_once(ROOT_DIR.'/includes/phpass/PasswordHash.php');

/** Basic system constants */
require_once(ROOT_DIR.'/sys.vars.php');

/** Load the database class */
require_once(ROOT_DIR.'/includes/classes/database.php');

/** Load the site options */

if ( !defined( 'IS_MAKE_CONFIG' ) ) {
	require_once(ROOT_DIR.'/includes/site.options.php');
}
/** Load the language class and translation file */
require_once(ROOT_DIR.'/includes/language.php');

/** Load the language and locales names list */
require_once(ROOT_DIR.'/includes/language-locales-names.php');

/** Text strings used on various files */
require_once(ROOT_DIR.'/includes/vars.php');

/** Basic functions to be accessed from anywhere */
require_once(ROOT_DIR.'/includes/functions.php');

/** Require the updates functions */
require_once(ROOT_DIR.'/includes/updates.functions.php');

/** Contains the session and cookies validation functions */
require_once(ROOT_DIR.'/includes/userlevel_check.php');

/** Template list generator */
require_once(ROOT_DIR.'/includes/templates.php');

/** Contains the current session information */
if ( !defined( 'IS_INSTALL' ) ) {
	require_once(ROOT_DIR.'/includes/active.session.php');
}
//echo "sssssssssssssssssss";exit();
/** Recreate the function if it doesn't exist. By Alan Reiblein */
require_once(ROOT_DIR.'/includes/timezone_identifiers_list.php');

/** Categories functions */
require_once(ROOT_DIR.'/includes/functions.categories.php');

/**
 * Always require this classes to avoid repetition of code
 * on other files.
 *
 */
$classes_files = array(
						'actions-clients.php',
						'actions-files.php',
						'actions-categories.php',
						'actions-groups.php',
						'actions-log.php',
						'actions-users.php',
						'file-upload.php',
						'form-validation.php',
						'send-email.php',
						'generate-form.php',
					);
foreach ( $classes_files as $filename ) {
	$location = ROOT_DIR . '/includes/classes/' . $filename;
	if ( file_exists( $location ) ) {
		require_once( $location );
	}
}

/**
 * Google Login
 */
require_once ROOT_DIR . '/includes/Google/Oauth2/service/Google_ServiceResource.php';
require_once ROOT_DIR . '/includes/Google/Oauth2/service/Google_Service.php';
require_once ROOT_DIR . '/includes/Google/Oauth2/service/Google_Model.php';
require_once ROOT_DIR . '/includes/Google/Oauth2/contrib/Google_Oauth2Service.php';
require_once ROOT_DIR . '/includes/Google/Oauth2/Google_Client.php';

//echo timestamp_check_for_orphan_file_deletion();

?>
