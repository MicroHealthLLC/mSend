
<style type="text/css">
	.disnone{
		display: none;
	}
</style>
<?php
/**
 * Contains the form that is used when adding or editing users.
 *
 * @package		ProjectSend
 * @subpackage	Users
 *
 */
?>

<script type="text/javascript">
	$(document).ready(function() {
		$("form").submit(function() {
		    
			clean_form(this);

			is_complete(this.add_user_form_name,'<?php echo $validation_no_name; ?>');
			is_complete(this.add_user_form_user,'<?php echo $validation_no_user; ?>');
 			is_complete(this.add_user_form_email,'<?php echo $validation_no_email; ?>');
			is_complete(this.add_user_form_level,'<?php echo $validation_no_level; ?>');
			is_length(this.add_user_form_user,<?php echo MIN_USER_CHARS; ?>,<?php echo MAX_USER_CHARS; ?>,'<?php echo $validation_length_user; ?>');
			is_email(this.add_user_form_email,'<?php echo $validation_invalid_mail; ?>');
			is_alpha_or_dot(this.add_user_form_user,'<?php echo $validation_alpha_user; ?>');
			
			<?php
				/**
				 * Password validation is optional only when editing a user.
				 */
				if ($user_form_type == 'edit_user' || $user_form_type == 'edit_user_self') {
			?>
					// Only check password if any of the 2 fields is completed
					var password_1 = $("#add_user_form_pass").val();
					if ($.trim(password_1).length > 0) {
			<?php
				}
			?>

						is_complete(this.add_user_form_pass,'<?php echo $validation_no_pass; ?>');
						is_length(this.add_user_form_pass,<?php echo MIN_PASS_CHARS; ?>,<?php echo MAX_PASS_CHARS; ?>,'<?php echo $validation_length_pass; ?>');
						is_password(this.add_user_form_pass,'<?php $chars = addslashes($validation_valid_chars); echo $validation_valid_pass." ".$chars; ?>');

			<?php
				/** Close the jquery IF statement. */
				if ($user_form_type == 'edit_user' || $user_form_type == 'edit_user_self') {
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
switch ($user_form_type) {
	case 'new_user':
		$submit_value = __('Add user','cftp_admin');
		$disable_user = false;
		$disable_password = false;
		$require_pass = true;
		$form_action = 'users-add.php';
		$extra_fields = true;
		$nUser = true;
		break;
	case 'edit_user':
		$submit_value = __('Save user','cftp_admin');
		$disable_user = true;
		$disable_password = true;
		$require_pass = false;
		$form_action = 'users-edit.php?id='.$user_id_mic;
		$extra_fields = true;
		break;
	case 'edit_user_self':
		$submit_value = __('Update account','cftp_admin');
		$disable_user = true;
		$disable_password = false;
		$require_pass = false;
		$form_action = 'users-edit.php?id='.$user_id_mic;
		$extra_fields = false;
		break;
}
?>
<form action="<?php echo html_output($form_action); ?>" name="adduser" method="post" class="form-horizontal" enctype="multipart/form-data">
	<div class="form-group">
		<label for="add_user_form_name" class="col-sm-4 control-label"><?php _e('Name','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_user_form_name" id="add_user_form_name" class="form-control required" value="<?php echo (isset($add_user_data_name)) ? html_output(stripslashes($add_user_data_name)) : ''; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label for="add_user_form_user" class="col-sm-4 control-label"><?php _e('Log in username','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<?php if (!$disable_user) { ?>
			<input type="text" name="add_user_form_user" id="add_user_form_user" class="form-control <?php if (!$disable_user) { echo 'required'; } ?>" maxlength="<?php echo MAX_USER_CHARS; ?>"
			value="<?php echo (isset($add_user_data_user)) ? html_output(stripslashes($add_user_data_user)) : ''; ?>"  placeholder="<?php _e("Must be alphanumeric",'cftp_admin'); ?>" />
		<?php } else { ?>
			<p class="form-control" style="background-color: #eee;" ><?php echo ($add_user_data_user);?></p>
		<?php } ?>
		</div>
	</div>

<?php if (!$disable_password) {  ?>
	<div class="form-group">
		<label for="add_user_form_pass" class="col-sm-4 control-label"><?php _e('Password','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<div class="input-group">
				<input name="add_user_form_pass" id="add_user_form_pass" class="form-control <?php if ($require_pass) { echo 'required'; } ?> password_toggle" type="password" maxlength="<?php echo MAX_PASS_CHARS; ?>" />
				<div class="input-group-btn password_toggler">
					<button type="button" class="btn pass_toggler_show"><i class="glyphicon glyphicon-eye-open"></i></button>
				</div>
			</div>
			<button type="button" name="generate_password" id="generate_password" class="btn btn-default btn-sm btn_generate_password" data-ref="add_user_form_pass" data-min="<?php echo MAX_GENERATE_PASS_CHARS; ?>" data-max="<?php echo MAX_GENERATE_PASS_CHARS; ?>"><?php _e('Generate','cftp_admin'); ?></button>
			<?php echo password_notes(); ?>
		</div>
	</div>
	<?php } ?>

	<div class="form-group">
		<label for="add_user_form_email" class="col-sm-4 control-label"><?php _e('E-mail','cftp_admin'); ?></label>
		<div class="col-sm-8">
			<input type="text" name="add_user_form_email" id="add_user_form_email" class="form-control required" value="<?php echo (isset($add_user_data_email)) ? html_output(stripslashes($add_user_data_email)) : ''; ?>" placeholder="<?php _e("Must be valid and unique",'cftp_admin'); ?>" />
		</div>
	</div>
<?php
/* 
	-Added alternative email option for users
	-Maximum limit of emails are 5. 
	-Added emails display from table. Balance will list as input box to enter new email.	
*/
	if(!empty($alternate_email_array)){
		$count_alternate_email_array = count($alternate_email_array);
		$maximum_alternate_emails = 5;
		$balance_email = ($maximum_alternate_emails-$count_alternate_email_array);
		foreach($alternate_email_array as $alternate_email){
	
?>
		<div class="form-group">
			<label for="add_user_form_email_alternate1" class="col-sm-4 control-label"><?php _e('Alternate E-mails','cftp_admin'); ?></label>
			<div class="col-sm-8">
				<input type="text" name="add_user_form_email_alternate[]" id="add_user_form_email_alternate1" class="form-control required" value="<?php echo (isset($alternate_email)) ? html_output(stripslashes($alternate_email)) : ''; ?>" placeholder="<?php _e("Must be valid email",'cftp_admin'); ?>" />
			</div>
		</div>
	<?php
		}
		if(!empty($balance_email)){
			for ($i = 1; $i <= $balance_email; $i++) {
	?>
		<div class="form-group">
			<label for="add_user_form_email_alternate1" class="col-sm-4 control-label"><?php _e('Alternate E-mails','cftp_admin'); ?></label>
			<div class="col-sm-8">
				<input type="text" name="add_user_form_email_alternate[]" id="add_user_form_email_alternate1" class="form-control required" value="" placeholder="<?php _e("Must be valid email",'cftp_admin'); ?>" />
			</div>
		</div>
	<?php
			}
		}
	}?>
<div class="form-group">
	<label  class="col-sm-4 control-label"><?php _e('Upload profile pic','cftp_admin'); ?></label>
	<div class="col-sm-8">
		<input type="file" name="userfiles" accept=".png,.jpg,.jpeg,.gif" class="required" value="" placeholder="upload file" />
	</div>
</div>	
<?php if (CURRENT_USER_ID == $user_id_mic) { ?>
<div class="form-group">
	<div class="col-sm-8  col-sm-offset-4">
		<label>
			<input type="radio" name="add_user_signature" class="sig1" / onclick="signaturefun('1')" checked="true"> <?php _e('Upload Signature','cftp_admin'); ?>
			<input type="radio" name="add_user_signature" class="sig2" / onclick="signaturefun('2')" class='data-toggle="modal" data-target="#sig"'> <?php _e('Draw Signature','cftp_admin'); ?>
			<input type="hidden" name="sigtype" class="sigtype" id="sigtype" / >
	</div>
</div>
<div class="form-group disnone" id="signaturechen">
	<label  class="col-sm-4 control-label"><?php _e('Upload Signature pic','cftp_admin'); ?></label>
	<div class="col-sm-8">
		<input type="file" name="usersignature"  id="usersignature" class="required usersignature" value="" accept=".png,.jpg,.jpeg,.gif" placeholder="upload file" / onchange="normalsignature()">
	</div>
</div>	
<?php }?>		
	<?php
		if ($extra_fields == true) {
		?>
			<div class="form-group">
				<label for="add_user_form_level" class="col-sm-4 control-label"><?php _e('Role','cftp_admin'); ?></label>
				<div class="col-sm-8">
					<select name="add_user_form_level" id="add_user_form_level" class="form-control">
						<option value="9" <?php echo (isset($add_user_data_level) && $add_user_data_level == '9') ? 'selected="selected"' : ''; ?>><?php echo USER_ROLE_LVL_9; ?></option>
						<option value="8" <?php echo (isset($add_user_data_level) && $add_user_data_level == '8') ? 'selected="selected"' : ''; ?>><?php echo USER_ROLE_LVL_8; ?></option>
						<option value="7" <?php echo (isset($add_user_data_level) && $add_user_data_level == '7') ? 'selected="selected"' : ''; ?>><?php echo USER_ROLE_LVL_7; ?></option>
					</select>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-8 col-sm-offset-4">
					<label for="add_user_form_active">
						<input type="checkbox" name="add_user_form_active" id="add_user_form_active" <?php echo (isset($add_user_data_active) && $add_user_data_active == 1) ? 'checked="checked"' : ''; ?> /> <?php _e('Active (user can log in)','cftp_admin'); ?>
					</label>
				</div>
			</div>
			
		<?php
			}
		?>
	<div class="form-group">
		<div class="col-sm-8 col-sm-offset-4">
			<label for="add_user_form_notify">
 			    <input type="checkbox" name="add_user_form_notify" id="add_user_form_notify" <?php echo (isset($add_user_data_notity) && $add_user_data_notity == 1) ? 'checked="checked"' : ''; ?>> <?php _e('Notify new uploads by e-mail','cftp_admin'); ?>
			</label>
		</div>
	</div>

	<div class="inside_form_buttons cc-text-right">
		<button type="submit" name="submit" class="btn btn-wide btn-primary"><?php echo $submit_value; ?></button>
	</div>

	<?php
		if ($user_form_type == 'new_user') {
			$msg = __('This account information will be e-mailed to the address supplied above','cftp_admin');
			echo system_message('info',$msg);
		}
	?>
</form>

<script type="text/javascript">

function normalsignature(){
    $('#sigtype').val('');
}
	$(document).ready(function() {
		signaturefun(1);
	});
</script>