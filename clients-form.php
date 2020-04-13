
<style type="text/css">
	.disnone{
		display: none;
	}
</style>
<?php
/**
 * Contains the form that is used when adding or editing clients.
 *
 * @package		ProjectSend
 * @subpackage	Clients
 *
 */
?> 

<script type="text/javascript">
	$(document).ready(function() {
		$("form").submit(function() {
			clean_form(this);

				is_complete(this.add_client_form_name,'<?php echo $validation_no_name; ?>');
				is_complete(this.add_client_form_user,'<?php echo $validation_no_user; ?>');
				is_complete(this.add_client_form_email,'<?php echo $validation_no_email; ?>');
				is_length(this.add_client_form_user,<?php echo MIN_USER_CHARS; ?>,<?php echo MAX_USER_CHARS; ?>,'<?php echo $validation_length_user; ?>');
				is_email(this.add_client_form_email,'<?php echo $validation_invalid_mail; ?>');
				// is_alpha_or_dot(this.add_client_form_user,'<?php // echo $validation_alpha_user; ?>');

			<?php
				/**
				 * Password validation is optional only when editing a client.
				 */
				if ($clients_form_type == 'edit_client' || $clients_form_type == 'edit_client_self') {
			?>
					// Only check password if any of the 2 fields is completed
					var password_1 = $("#add_client_form_pass").val();
					if ($.trim(password_1).length > 0) {
			<?php
				}
			?>

						is_complete(this.add_client_form_pass,'<?php echo $validation_no_pass; ?>');
						is_length(this.add_client_form_pass,<?php echo MIN_PASS_CHARS; ?>,<?php echo MAX_PASS_CHARS; ?>,'<?php echo $validation_length_pass; ?>');
						is_password(this.add_client_form_pass,'<?php $chars = addslashes($validation_valid_chars); echo $validation_valid_pass." ".$chars; ?>');

			<?php
				/** Close the jquery IF statement. */
				if ($clients_form_type == 'edit_client' || $clients_form_type == 'edit_client_self') {
			?>
					}
			<?php
				}
			?>

			// show the errors or continue if everything is ok
			if (show_form_errors() == false) { return false; }
		});
	});
</script>

<?php
$name_placeholder = __("Will be visible on the client's file list",'cftp_admin');

switch ($clients_form_type) {
	/** User is creating a new client */
	case 'new_client':
		$submit_value = __('Add client','cftp_admin');
		$disable_user = false;
		$disable_password = false;
		$require_pass = true;
		$form_action = 'clients-add.php';
		$info_box = true;
		$extra_fields = true;
		break;
	/** User is editing an existing client */
	case 'edit_client':
		$submit_value = __('Save client','cftp_admin');
		$disable_user = true;
		$disable_password = true;
		$require_pass = false;
		$form_action = 'clients-edit.php?id='.$client_id;
		$info_box = false;
		$extra_fields = true;
		break;
	/** A client is creating a new account for himself */
	case 'new_client_self':
		$submit_value = __('Register account','cftp_admin');
		$disable_user = true;
		$disable_password = false;
		$require_pass = true;
		$form_action = 'register.php';
		$info_box = true;
		$extra_fields = false;
		$name_placeholder = __("Your full name",'cftp_admin');
		break;
	/** A client is editing his profile */
	case 'edit_client_self':
		$submit_value = __('Update account','cftp_admin');
		$disable_user = true;
		$require_pass = false;
		$form_action = 'clients-edit.php?id='.$client_id;
		$info_box = false;
		$extra_fields = false;
		break;
}
?>

<form action="<?php echo $form_action; ?>" name="addclient" method="post" class="form-horizontal" enctype="multipart/form-data">
	<div class="form-group">
		<label for="add_client_form_name" class="col-sm-4 control-label"><?php _e('Name','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_name" id="add_client_form_name" class="form-control required" value="<?php echo (isset($add_client_data_name)) ? html_output(stripslashes($add_client_data_name)) : ''; ?>" placeholder="<?php echo $name_placeholder; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label for="add_client_form_user" class="col-sm-4 control-label"><?php _e('Log in username','cftp_admin'); ?></label>
		<div class="col-sm-8">
				<?php if (!$disable_user) { ?>
			<input type="text" name="add_client_form_user" id="add_client_form_user" class="form-control <?php if (!$disable_user) { echo 'required'; } ?>" minlength="4" maxlength="<?php echo MAX_USER_CHARS; ?>" value="<?php echo (isset($add_client_data_user)) ? html_output(stripslashes($add_client_data_user)) : ''; ?>"
			placeholder="<?php _e("Must be alphanumeric",'cftp_admin'); ?>" />
			<?php } else { ?>
				<p class="form-control" style="background-color: #eee;" ><?php echo ($add_client_data_user);?></p>
			<?php } ?>
		</div>
	</div>
<?php if (!$disable_password) {  ?>
	<div class="form-group">
		<label for="add_client_form_pass" class="col-sm-4 control-label"><?php _e('Password','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<div class="input-group">
				<input name="add_client_form_pass" id="add_client_form_pass" class="form-control password_toggle <?php if ($require_pass) { echo 'required'; } ?>" type="password" maxlength="<?php echo MAX_PASS_CHARS; ?>" />
				<div class="input-group-btn password_toggler">
					<button type="button" class="btn pass_toggler_show"><i class="glyphicon glyphicon-eye-open"></i></button>
				</div>
			</div>
			<button type="button" name="generate_password" id="generate_password" class="btn btn-default btn-sm btn_generate_password" data-ref="add_client_form_pass" data-min="<?php echo MAX_GENERATE_PASS_CHARS; ?>" data-max="<?php echo MAX_GENERATE_PASS_CHARS; ?>"><?php _e('Generate','cftp_admin'); ?></button>
			<?php echo password_notes(); ?>
		</div>		
	</div>
<?php } ?>
	<div class="form-group">
		<label for="add_client_form_email" class="col-sm-4 control-label"><?php _e('E-mail','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_email" id="add_client_form_email" class="form-control required" value="<?php echo (isset($add_client_data_email)) ? html_output(stripslashes($add_client_data_email)) : ''; ?>" placeholder="<?php _e("Must be valid and unique",'cftp_admin'); ?>" />
		</div>
	</div>

	<div class="form-group">
		<label for="add_client_form_address" class="col-sm-4 control-label"><?php _e('Address Line 1','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_address" id="add_client_form_address" class="form-control" value="<?php echo (isset($add_client_data_addr)) ? html_output(stripslashes($add_client_data_addr)) : ''; ?>" />
		</div>
	</div>
	<!-- Address line 2 -->
    
    <div class="form-group">
		<label for="add_client_form_address_line2" class="col-sm-4 control-label"><?php _e('Address Line 2','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_address_line2" id="add_client_form_address_line2" class="form-control" value="<?php echo (isset($add_client_data_addr2)) ? html_output(stripslashes($add_client_data_addr2)) : ''; ?>" />
		</div>
	</div>
    <!-- city -->
    <div class="form-group">
		<label for="add_client_city" class="col-sm-4 control-label"><?php _e('City','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_city" id="add_client_city" class="form-control" value="<?php echo (isset($add_client_data_city)) ? html_output(stripslashes($add_client_data_city)) : ''; ?>" />
		</div>
	</div>
    <!-- State -->
    <div class="form-group">
		<label for="add_client_form_state" class="col-sm-4 control-label"><?php _e('State','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_state" id="add_client_form_state" class="form-control" value="<?php echo (isset($add_client_data_state)) ? html_output(stripslashes($add_client_data_state)) : ''; ?>" />
		</div>
	</div>
    <!-- zip code -->
    <div class="form-group">
		<label for="add_client_form_zip" class="col-sm-4 control-label"><?php _e('Zip Code','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_zip" id="add_client_form_zip" class="form-control" value="<?php echo (isset($add_client_data_zip)) ? html_output(stripslashes($add_client_data_zip)) : ''; ?>" />
		</div>
	</div>
	<div class="form-group">
		<label for="add_client_form_phone" class="col-sm-4 control-label"><?php _e('Telephone','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_client_form_phone" id="add_client_form_phone" class="form-control" value="<?php echo (isset($add_client_data_phone)) ? html_output(stripslashes($add_client_data_phone)) : ''; ?>" />
		</div>
	</div>
<div class="form-group">
	<label for="add_user_form_email_alternate1" class="col-sm-4 control-label"><?php _e('Upload profile pic','cftp_admin'); ?></label>
	<div class="col-sm-8">
		<input type="file" name="userfiles" class="required" value="" placeholder="upload file" />
	</div>
</div>
<?php if (CURRENT_USER_ID == $client_id) { ?>
	<div class="form-group">
		<div class="col-sm-8  col-sm-offset-4">
			<label>
				<input type="radio" name="add_user_signature" class="sig1" / onclick="signaturefun('1')" checked="true"> <?php _e('Upload Signature','cftp_admin'); ?>
				<input type="radio" name="add_user_signature" class="sig2" / onclick="signaturefun('2')" class='data-toggle="modal" data-target="#sig"'> <?php _e('Draw Signature','cftp_admin'); ?>
			</label>
		</div>
	</div>
	<div class="form-group disnone" id="signaturechen">
		<label  class="col-sm-4 control-label"><?php _e('Upload Signature pic','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="file" name="usersignature"  id="usersignature" class="required usersignature" value="" placeholder="upload file" />
		</div>
	</div>	
<?php }?>	
		<?php
			if ($extra_fields == true) {
		?>
				<div class="form-group">
					<label for="add_client_form_intcont" class="col-sm-4 control-label"><?php _e('Internal contact name','cftp_admin'); ?></label>
					<div class="col-sm-8">
						<input type="text" name="add_client_form_intcont" id="add_client_form_intcont" class="form-control" value="<?php echo (isset($add_client_data_intcont)) ? html_output(stripslashes($add_client_data_intcont)) : ''; ?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<label for="add_client_form_active">
							<input type="checkbox" name="add_client_form_active" id="add_client_form_active" <?php echo (isset($add_client_data_active) && $add_client_data_active == 1) ? 'checked="checked"' : ''; ?>> <?php _e('Active (client can log in)','cftp_admin'); ?>
						</label>
					</div>
				</div>
				<?php
					}
					$current_level = get_current_user_level();
			 	?>
	<div class="form-group">
		<div class="col-sm-8 col-sm-offset-4">
			<label for="add_client_form_notify">
				<input type="checkbox" name="add_client_form_notify" id="add_client_form_notify" <?php echo (isset($add_client_data_notity) && $add_client_data_notity == 1) ? 'checked="checked"' : ''; ?>> <?php _e('Notify new uploads by e-mail','cftp_admin'); ?>
			</label>
		</div>
	</div>

	<?php
		if ( $clients_form_type == 'new_client_self' ) {
			if ( defined('RECAPTCHA_AVAILABLE') ) {
	?>
				<div class="form-group">
					<label class="col-sm-4 control-label"><?php _e('Verification','cftp_admin'); ?></label>
					<div class="col-sm-8">
						<div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
					</div>
				</div>
	<?php
			}
		}
	?>

	<div class="inside_form_buttons cc-text-right">
		<?php if($current_level != 0){ ?>
		<a href="<?php echo BASE_URI; ?>clients.php" name="cancel" class="btn btn-wide btn-default"><?php _e('Cancel','cftp_admin'); ?></a>
	<?php } else{ ?>
		<a href="<?php echo BASE_URI; ?>inbox.php" name="cancel" class="btn btn-wide btn-default"><?php _e('Cancel','cftp_admin'); ?></a>
		<?php
		} ?>
		<button type="submit" name="submit" class="btn btn-wide btn-primary"><?php echo html_output($submit_value); ?></button>
	</div>

	<?php
		if ($info_box == true) {
			$msg = __('This account information will be e-mailed to the address supplied above','cftp_admin');
			echo system_message('info',$msg);
		}
	?>
</form>
<div id="sig" class="modal fade" role="dialog">
		<div class="modal-dialog">

		<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" onclick="signaturemodalclose()">&times;</button>
					<h4 class="modal-title">Draw New Signature </h4>
				</div>
				<div class="modal-body">
					<input type="hidden" id="uid" value="<?php echo $client_id;?>">
					<?php
						include('clientsignature.php');
					?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" onclick="signaturemodalclose()">Close</button>
				</div>
			</div>

		</div>
	</div>
<script type="text/javascript">
	function signaturefun(argument) {
		if(argument==1){
			$('#signaturechen').removeClass('disnone');
		}else{
			$('#signaturechen').removeClass('disnone').addClass('disnone');
			$('#sig').modal('show');
		}
	}
	function signaturemodalclose() {
		$('#sig').modal('toggle');
		$('.sig1').prop("checked", true).trigger('change');
		signaturefun(1);
	}

	$(document).ready(function() {
		signaturefun(1);
	});
</script>