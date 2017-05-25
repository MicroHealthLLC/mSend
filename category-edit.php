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

$active_nav = 'category';

$page_title = __('Create New Category','cftp_admin');

include('header.php');?>
<script type="text/javascript">
$("#process_category").submit(function() {
			clean_form( this );

			is_complete( this.category_name, '<?php echo $validation_no_name; ?>' );

			// show the errors or continue if everything is ok
			if (show_form_errors() == false) { return false; }
		});
</script>
<?php
	if (isset($_GET['id'])) 
		$edit_id = $_GET['id'];
	//var_dump($edit_id);
	/** Get all the existing categories */
	$get_categories = get_categories();
	$categories	= $get_categories['categories'];
	$arranged	= $get_categories['arranged'];
	if(!empty( $_GET['action'] ) && $_GET['action'] == 'edit' ){ 
		$action				= 'edit';
		$editing			= !empty( $_POST['editing_id'] ) ? $_POST['editing_id'] : $_GET['id'];
		$form_information	= array(
			'type'	=> 'edit_category',
			'title'	=> __('Edit category','cftp_admin'),
		);
		/**
		 * Get the current information if just entering edit mode
		 */
		$category_name			= $categories[$editing]['name'];
		$category_parent		= $categories[$editing]['parent'];
		$category_description	= $categories[$editing]['description'];
	}
	/**
	 * Process the action
	 */
	if ( isset( $_POST['btn_process'] ) ) {
		/**
		 * Applies for both ADDING a new category as well
		 * as editing one but with the form already sent.
		 */
		
		$category_name			= $_POST['category_name'];
		$category_parent		= $_POST['category_parent'];
		$category_description	= $_POST['category_description'];

		$category_object = new CategoriesActions();

		$arguments = array(
							'action'		=> 'edit',
							'name'			=> $category_name,
							'parent'		=> $category_parent,
							'description'	=> $category_description,
						);

		$validate = $category_object->validate_category( $arguments );

		$arguments['action']	= 'edit';
		$redirect_status		= 'edited';
		$arguments['id']		= isset($_POST['editing_id'])?$_POST['editing_id'] :$_GET['id'];

		if ( $validate === 1 ) {
			$process = $category_object->save_category( $arguments );
			if ( $process['query'] === 1 ) {
				$redirect = true;
				$status = $redirect_status;
			}
			else {
				$msg = __('There was a problem savint to the database.','cftp_admin');
				echo system_message('error', $msg);
			}
		}
		else {
			$msg = __('Please complete all the required fields.','cftp_admin');
			echo system_message('error', $msg);
		}

		/** Redirect so the actions are reflected immediatly */
		if ( isset( $redirect ) && $redirect === true ) {
			while (ob_get_level()) ob_end_clean();
			$location = BASE_URI . 'categories.php?status=' . $status;
			header("Location: $location");
			die();
		}
	}
?>

<div id="main">
	
	<div id="content"> 
    
    <!-- Added by B) -------------------->
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
				<div class="white-box-interior">
		<h2 class="page-title txt-color-blueDark"><?php echo $page_title; ?></h2>
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
							$form_information['type']='edit_category';
							include('categories-form.php');
						}
					?>

				</div>
			</div>
		</div>
	</div>
</div>
</div>
<?php
	include('footer.php');
?>