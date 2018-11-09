<?php
/**
 * Show the form to add a new system user.
 *
 * @package		ProjectSend
 * @subpackage	Users
 *
 */
$allowed_levels = array(9);
require_once('sys.includes.php');

if(!check_for_admin()) {
    return;
}

$active_nav = 'users';
$cc_active_page = 'UserAdd';

$page_title = __('Add system user','cftp_admin');

include('header.php');

/**
 * Set checkboxes as 1 to defaul them to checked when first entering
 * the form
 */
$add_user_data_active = 1;
$add_user_data_notity = 1;

if ($_POST) {
	$new_user = new UserActions();

	/**
	 * Clean the posted form values to be used on the user actions,
	 * and again on the form if validation failed.
	 */
	$add_user_data_name = encode_html($_POST['add_user_form_name']);
	$add_user_data_email = encode_html($_POST['add_user_form_email']);
	$add_user_data_level = encode_html($_POST['add_user_form_level']);
	$add_user_data_user = encode_html($_POST['add_user_form_user']);
	$add_user_data_active = (isset($_POST["add_user_form_active"])) ? 1 : 0;
        $add_user_data_notity = (isset($_POST["add_user_form_notify"])) ? 1 : 0;

	/** Arguments used on validation and user creation. */
	$new_arguments = array(
							'id' => '',
							'username' => $add_user_data_user,
							'password' => $_POST['add_user_form_pass'],
							//'password_repeat' => $_POST['add_user_form_pass2'],
							'name' => $add_user_data_name,
							'email' => $add_user_data_email,
							'role' => $add_user_data_level,
							'active' => $add_user_data_active,						
							'notify' => $add_user_data_notity,
							'type' => 'new_user'
						);

	/** Validate the information from the posted form. */
	$new_validate = $new_user->validate_user($new_arguments);
	
	/** Create the user if validation is correct. */
	if ($new_validate == 1) {
		$new_response = $new_user->create_user($new_arguments);
	}
	
}
?>

<div id="main">
  <div id="content"> 
    
    <!-- Added by B) -------------------->
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <h1 class="page-title txt-color-blueDark"><i class="fa-fw fa fa-user"></i>&nbsp;<?php echo $page_title; ?></h1>
          <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">
            <div class="white-box-interior">
              <?php
						/**
						 * If the form was submited with errors, show them here.
						 */
						$valid_me->list_errors();
					?>
              <?php
						if (isset($new_response)) {
							/**
							 * Get the process state and show the corresponding ok or error message.
							 */
							switch ($new_response['query']) {
								case 1:
									$msg = __('User added correctly.','cftp_admin');
									echo system_message('ok',$msg);
			
									/** Record the action log */
									$new_log_action = new LogActions();
									$log_action_args = array(
															'action' => 2,
															'owner_id' => $global_id,
															'affected_account' => $new_response['new_id'],
															'affected_account_name' => $add_user_data_name
														);
									$new_record_action = $new_log_action->log_action_save($log_action_args);
			
								break;
								case 0:
									$msg = __('There was an error. Please try again.','cftp_admin');
									echo system_message('error',$msg);
								break;
							}
							/**
							 * Show the ok or error message for the email notification.
							 */
							switch ($new_response['email']) {
								case 1:
									$msg = __('An e-mail notification with login information was sent to the new user.','cftp_admin');
									echo system_message('ok',$msg);
								break;
								case 0:
									$msg = __("E-mail notification couldn't be sent.",'cftp_admin');
									echo system_message('error',$msg);
								break;
							}
						}
						else {
							/**
							 * If not $new_response is set, it means we are just entering for the first time.
							 * Include the form.
							 */
							$user_form_type = 'new_user';
							include('users-form.php');
						}
					?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
	include('footer.php');
?>
