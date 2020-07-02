<?php



?>

    <?php
/**
 * Home page for logged in system users.
 *
 * @package   ProjectSend
 *
 */
$load_scripts = array(
            'flot',
          ); 

$allowed_levels = array(9,8,7);
require_once('sys.includes.php');
$page_title = __("Welcome to ".BRAND_NAME, 'cftp_admin');
$active_nav = 'dashboard';
$cc_active_page = 'Dashboard';

include('header.php');

define('CAN_INCLUDE_FILES', true);

echo 'haiii';
?>