<?php
/**
 * Show the form to request a drop-off
 * @package		ProjectSend
 * @subpackage Upload
 * @subpackage	Request a drop-off
 */
$load_scripts	= array(
'plupload',					);
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
$active_nav = 'dropoff';
$cc_active_page = 'Send File';
if (CLIENTS_CAN_UPLOAD == 1) {
	$allowed_levels[] = 0;
}
$page_title = __('Drop-off Request','cftp_admin');
include('header.php');
$auth = isset($_GET['auth']) ? htmlspecialchars($_GET['auth'],ENT_QUOTES, 'UTF-8') : '';
$this_user = CURRENT_USER_USERNAME;
/* check auth key is authorized to current user */
$this_user = CURRENT_USER_USERNAME;
$client_info = get_client_by_username($this_user);
$logged_in_email = isset($client_info['email'])?$client_info['email']:'';
$sql_auth = $dbh->prepare( 'SELECT * FROM '.TABLE_DROPOFF.' WHERE auth_key = "'.$auth.'" AND to_email="'.$logged_in_email.'"');
$sql_auth->execute();
$sql_auth = $sql_auth->fetch();
/* Get the target user ID */
$target_id = $sql_auth['from_id'];
/* Get the targe user ID inof */
$target_info = get_client_by_id($target_id);
/*echo "<pre>"; print_r($target_info); echo "</pre>"; */
?>
		<div id="main">
			<div id="content">
				<!-- Added by B) -------------------->
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
						<?php if(!empty($sql_auth) && count($sql_auth)>0)
						{
							if($sql_auth['status']==0)
							{?>
								<h2><?php echo $page_title; ?></h2>
								<p>
								<?php
								$msg = __('Click on Add files to select all the files that you want to upload, and then click continue. On the next step, you will be able to set a name and description for each uploaded file. Remember that the file may not be empty (0 KB) and the maximum allowed file size (in mb.) is ','cftp_admin') . ' <strong>'.MAX_FILESIZE.'</strong>';
								echo system_message('info', $msg);
								?>
								</p>
								<script type="text/javascript">
										$(document).ready(function() {

											$("form input[type=submit]").click(function() {
												//Seting up an event to emit an attribute clicked on clicking of submit button
												    $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
												    $(this).attr("clicked", "true");
												});

											setInterval(function(){
												// Send a keep alive action every 1 minute
												var timestamp = new Date().getTime()
												$.ajax({
													type:	'GET',
													cache:	false,
													url:	'includes/ajax-keep-alive.php',
													data:	'timestamp='+timestamp,
													success: function(result) {
														var dummy = result;
													}
												});
											},1000*60);
										});
										$(function() {
															// Setup html5 version
															$("#uploader").pluploadQueue({
																// General settings
																runtimes : 'html5,silverlight,html4',
																url : 'process-upload.php',
																chunk_size: '20mb',
																rename : true,
																dragdrop: true,

																filters : {
																	mime_types : [
																		{title : 'Files ', extensions :'ai,avi,bin,bmp,cdr,doc,docm,docx,eps,fla,flv,gif,htm,html,iso,jpeg,jpg,mp3,mp4,mpg,odt,oog,ppt,pptx,pptm,pps,ppsx,pdf,png,psd,rtf,tif,tiff,txt,wav,xls,xlsm,xlsx'}
																	],
																	// Maximum file size
																	max_file_size : '2048mb'}
															});


											var uploader = $('#uploader').pluploadQueue();
											$('form').submit(function(e) {
												var uptype = $("input[type=submit][clicked=true]").val();
												if (uptype== "Batch Upload**") {
													console.log("Batch upload clicked");
													var zipfield = '<input type="hidden" name="batching" value="1" />'
													$('form').append(zipfield);
													$('form').attr('action', 'upload-zip-dropoff.php');
												}


												if (uploader.files.length >0) {
													uploader.bind('StateChanged', function() {
														if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
															$('form')[0].submit();
														}
													});
													uploader.start();
													$("#btn-submit").hide();
                                                                                                        $("#zip-submit").hide();
													$("#uploadbtnsnotes").hide();
													$(".message_uploading").fadeIn();
													uploader.bind('FileUploaded', function (up, file, info) {
														var obj = JSON.parse(info.response);
														var fname= obj.NewFileName;
														var ext = fname.substr( (fname.lastIndexOf('.') +1) );
														if(ext =='zip'){
															var uptype = $("input[type=submit][clicked=true]").val();
															if (uptype== "Batch Upload**") {
																$("#btn-submit").show();
																$("#zip-submit").show();
																$("#uploadbtnsnotes").show();
																var batchornot = confirm("Compressed files are not allowed in Batch upload. Continue with normal upload?");
																if(batchornot == true){
																	$("form")[0].reset();
																	$('form').attr('action', 'upload-process-form-dropoff.php');
																}
															}
														}
														var new_file_field = '<input type="hidden" name="finished_files[]" value="'+obj.NewFileName+'" />'
														$('form').append(new_file_field);
													});
													return false;
												} else {
													alert('<?php _e("You must select at least one file to upload.",'cftp_admin'); ?>');
												}
												return false;
											});
											window.onbeforeunload = function (e) {
												var e = e || window.event;
												console.log('state? ' + uploader.state);
												if(uploader.state === 2) {
													<?php
														$confirmation_msg = "Are you sure? Files currently being uploaded will be discarded if you leave this page.";
													?>
													if (e) {
														e.returnValue = '<?php _e($confirmation_msg,'cftp_admin'); ?>';
													}
													return '<?php _e($confirmation_msg,'cftp_admin'); ?>';
												}
											};
										});
								</script>
								<form action="upload-process-form-dropoff.php" name="upload_by_client" id="upload_by_client" method="post" enctype="multipart/form-data">
									<input type="hidden" name="uploaded_files" id="uploaded_files" value="" />
									<input type="hidden" name="zipping" id="zipping" value="0" />
									<div id="uploader">
										<div class="message message_error">
											<p>
												<?php _e("Your browser doesn't support HTML5 or Silverlight. Please update your browser or install Silverlight to continue.",'cftp_admin'); ?>
											</p>
										</div>
									</div>
									<input type="hidden" value="<?php echo isset($auth)?$auth:''; ?>" name="auth_key" />

									<input type="hidden" value="<?php echo isset($target_id)?$target_id:''; ?>" name="target_id" />

									<input type="hidden" value="<?php echo isset($target_info['name'])?$target_info['name']:''; ?>" name="target_name" />
									<input type="hidden" value="<?php echo isset($target_info['username'])?$target_info['username']:''; ?>" name="target_name" />
									<div class="after_form_buttons cc-text-right">
				            <input type="submit" class="btn btn-wide btn-primary" value="Upload Files*" id="btn-submit">
				            <input type="submit" class="btn btn-wide btn-success" value="Batch Upload**" id="zip-submit">
									    <div id="uploadbtnsnotes">
                                                                                <p style="font-size: 11px">*Note: Nested compressed files not allowed.<br>**Note: Compressed files not allowed.</p>
                                                                            </div>
									</div>
									<div class="message message_info message_uploading">
										<p>
											<?php _e("Your files are being uploaded! Progress indicators may take a while to update, but work is still being done behind the scenes.",'cftp_admin'); ?>
										</p>
									</div>
								</form>
						<?php
							}
							else {
								echo "<h2>";echo "You have already uploaded the file(s) for this request!";	echo "</h2>";
							}
						}
						else
						{
							echo "<h2>";echo "You are not authorized to access this page!";	echo "</h2>";
						}
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		include('footer.php');
		?>
