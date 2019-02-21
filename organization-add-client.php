<?php
/**
 * Show the form to add a new group.
 *
 * @package		ProjectSend
 * @subpackage	Groups
 *
 */
$load_scripts	= array(
						'chosen',
					); 

$allowed_levels = array(9,8);
require_once('sys.includes.php');

$active_nav = 'organizations';

$page_title = __('Add Clients Organization','cftp_admin');

include('header.php');

if ($_POST) {
	$new_group = new GroupActions();

	/**
	 * Clean the posted form values to be used on the groups actions,
	 * and again on the form if validation failed.
	 */
	$add_group_data_name = encode_html($_POST['add_group_form_name']);
	$add_group_data_description = encode_html($_POST['add_group_form_description']);
	$add_group_data_members = $_POST['add_group_form_members'];
	$add_type = 0;

	/** Arguments used on validation and group creation. */
	$new_arguments = array(
							'id' => '',
							'name' => $add_group_data_name,
							'description' => $add_group_data_description,
							'organization_type'=>$add_type,
							'members' => $add_group_data_members
							
						);

	/** Validate the information from the posted form. */
	$new_validate = $new_group->validate_group($new_arguments);
	
	/** Create the group if validation is correct. */
	if ($new_validate == 1) {
		$new_response = $new_group->create_group($new_arguments);
	}
	
}
?>

<div id="main">
	
	
	<div class="container">
		<div class="row">
			<div class="col-md-12">
        <h2><?php echo $page_title; ?></h2>
				<div class="row">
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
							 * Get the process state and show the corresponding ok or error messages.
							 */
							switch ($new_response['query']) {
								case 1:
									$msg = __('Organization added correctly.','cftp_admin');
									echo system_message('ok',$msg);
			
									/** Record the action log */
									$new_log_action = new LogActions();
									$log_action_args = array(
															'action' => 23,
															'owner_id' => $global_id,
															'affected_account' => $new_response['new_id'],
															'affected_account_name' => $add_group_data_name
														);
									$new_record_action = $new_log_action->log_action_save($log_action_args);
								break;
								case 0:
									$msg = __('There was an error. Please try again.','cftp_admin');
									echo system_message('error',$msg);
								break;
							}
						}
						else {
							/**
							 * If not $new_response is set, it means we are just entering for the first time.
							 * Include the form.
							 */
							$organization_form_type = 'new_organization';
							include('organization-form-client.php');
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
