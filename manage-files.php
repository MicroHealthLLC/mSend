<?php

/**

 * Allows to hide, show or delete the files assigend to the

 * selected client.

 *

 * @package ProjectSend

 */

$load_scripts	= array(

						'footable',

					); 





if(!empty ($_GET['client_id']) ||!empty ($_GET['group_id']) ||!empty ($_GET['category']) ){

	$allowed_levels = array(8,9);

} else{

	$allowed_levels = array(0,7,8,9);

}

require_once('sys.includes.php');



$active_nav = 'files';

$cc_active_page = 'Manage Files';



$page_title = __('Manage Files','cftp_admin');



$current_level = get_current_user_level();



/*

 * Get the total downloads count here. The results are then

 * referenced on the results table.

 */

$downloads_information = generate_downloads_count();



/**

 * Used to distinguish the current page results.

 * Global means all files.

 * Client or group is only when looking into files

 * assigned to any of them.

 */

$results_type = 'global';



/**

 * The client's id is passed on the URI.

 * Then get_client_by_id() gets all the other account values.

 */

if (isset($_GET['client_id'])) {

	$this_id = $_GET['client_id'];

	$this_client = get_client_by_id($this_id);

	/** Add the name of the client to the page's title. */

	if(!empty($this_client)) {

		$page_title .= ' '.__('for client','cftp_admin').' '.html_entity_decode($this_client['name']);

		$search_on = 'client_id';

		$name_for_actions = $this_client['username'];

		$results_type = 'client';

	}

}



/**

 * The group's id is passed on the URI also.

 */

if (isset($_GET['group_id'])) {

	$this_id = $_GET['group_id'];





	$sql_name = $dbh->prepare("SELECT name from " . TABLE_GROUPS . " WHERE id=:id");

	$sql_name->bindParam(':id', $this_id, PDO::PARAM_INT);

	$sql_name->execute();							



	if ( $sql_name->rowCount() > 0) {

		$sql_name->setFetchMode(PDO::FETCH_ASSOC);

		while( $row_group = $sql_name->fetch() ) {

			$group_name = $row_group["name"];

		}

		/** Add the name of the client to the page's title. */

		if(!empty($group_name)) {

			$page_title .= ' '.__('for group','cftp_admin').' '.html_entity_decode($group_name);

			$search_on = 'group_id';

			$name_for_actions = html_entity_decode($group_name);

			$results_type = 'group';

		}

	}

}



/**

 * Filtering by category

 */

if (isset($_GET['category'])) {

	$this_id = $_GET['category'];

	$this_category = get_category($this_id);

	/** Add the name of the client to the page's title. */

	if(!empty($this_category)) {

		$page_title .= ' '.__('on category','cftp_admin').' '.html_entity_decode($this_category['name']);

		$name_for_actions = $this_category['name'];

		$results_type = 'category';

	}

}



include('header.php');

error_reporting(E_ALL);

?>

<script type="text/javascript">

	$(document).ready(function() {



			$("#do_action").click(function(e) {

				// e.preventDefault();

				var checks = $("td input:checkbox").serializeArray();

				var actType = $('#files_actions').val();

				

				if (actType == 'show') {

					return true;

				}

				else if (checks.length == 0) {

						alert('<?php _e('Please select at least one file to proceed.','cftp_admin'); ?>');

						return false;

				}

				else {

					var action = $('#files_actions').val();

					if (action == 'delete') {

						var msg_1 = '<?php _e("You are about to delete",'cftp_admin'); ?>';

						if(checks.length > 1){

							var msg_2 = 'files permanently and for every client/group. Are you sure you want to continue?';

						}

						else{

							var msg_2 = 'file permanently and for every client/group. Are you sure you want to continue?';

						}



						if (confirm(msg_1+' '+checks.length+' '+msg_2)) {

							return true;

						} else {

							return false;

						}

					}

					else if (action == 'unassign') {

						var msg_1 = '<?php _e("You are about to unassign",'cftp_admin'); ?>';

						var msg_2 = '<?php _e("files from this account. Are you sure you want to continue?",'cftp_admin'); ?>';

						if (confirm(msg_1+' '+checks.length+' '+msg_2)) {

							return true;

						} else {

							return false;

						}

					}

				}

			});





	    $('.public_link').popover({ 

			html : true,

			content: function() {

				var id		= $(this).data('id');

				var token	= $(this).data('token');

				return '<strong><?php _e('Click to select','cftp_admin'); ?></strong><textarea class="input-large public_link_copy" rows="4"><?php echo BASE_URI; ?>download.php?id=' + id + '&token=' + token + '</textarea><small><?php _e('Send this URL to someone to download the file without registering or logging in.','cftp_admin'); ?></small><div class="close-popover"><button type="button" class="btn btn-inverse btn-sm"><?php _e('Close','cftp_admin'); ?></button></div>';

			}

		});



		$(".col_visibility").on('click', '.close-popover button', function(e) {

			var popped = $(this).parents('.col_visibility').find('.public_link');

			popped.popover('hide');

		});



		$(".col_visibility").on('click', '.public_link_copy', function(e) {

			$(this).select();

			$(this).mouseup(function() {

				$(this).unbind("mouseup");

				return false;

			});

		});

	});

</script>



<div id="main">

  <div id="content">

    <div class="container-fluid">

      <div class="row">

        <div class="col-md-12">

          <h1 class="page-title txt-color-blueDark"><i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;<?php echo $page_title; ?></h1>

          <?php

		/**

		 * Apply the corresponding action to the selected files.

		 */

		if(isset($_POST['do_action'])) {

			/** Continue only if 1 or more files were selected. */

			if ($_POST['files_actions']=='show') {

				$log_action_number = 22;

						$this_file = new FilesActions();

						$this_file->manage_show();

						$msg = __('All hidden files were marked as visible.','cftp_admin');

						echo system_message('ok',$msg);



			}

			else if(!empty($_POST['files'])) {

				$selected_files = array_map('intval',array_unique($_POST['files']));

				$files_to_get = implode(',',$selected_files);

				/**

				 * Then get the files names to add to the log action.

				 */

				$sql_file = $dbh->prepare("SELECT id, filename FROM " . TABLE_FILES . " WHERE ( FIND_IN_SET(id, :files) AND  hidden = '0' )");

				$sql_file->bindParam(':files', $files_to_get);

				$sql_file->execute();

				$sql_file->setFetchMode(PDO::FETCH_ASSOC);



				while( $data_file = $sql_file->fetch() ) {

					$all_files[$data_file['id']] = $data_file['filename'];

				}

				

				switch($_POST['files_actions']) {

					case 'hide':

						foreach ($selected_files as $work_file) {

							$this_file = new FilesActions();

							$hide_file = $this_file->manage_hide($work_file);

						}

						$msg = __('The selected files were marked as hidden.','cftp_admin');

						echo system_message('ok',$msg);

						$log_action_number = 21;

						break;







						case 'unassign':

									foreach ($selected_files as $work_file) {

											$this_file = new FilesActions();

											$this_file->unassign($work_file);

											}

										$msg = __('The selected files were unassigned from this client.','cftp_admin');

										echo system_message('ok',$msg);

										$log_action_number = 11;

										break;

					case 'delete':

						$delete_results	= array(

												'ok'		=> 0,

												'errors'	=> 0,

											);

						foreach ($selected_files as $index => $file_id) {

							$this_file		= new FilesActions();

							$delete_status	= $this_file->delete_files($file_id);



							if ( $delete_status == true ) {

								$delete_results['ok']++;

							}

							else {

								$delete_results['errors']++;

								unset($all_files[$file_id]);

							}

						}



						if ( $delete_results['ok'] > 0 ) {

							$msg = __('The selected files were deleted.','cftp_admin');

							echo system_message('ok',$msg);

							$log_action_number = 12;

						}

						if ( $delete_results['errors'] > 0 ) {

							$msg = __('Some files could not be deleted.','cftp_admin');

							echo system_message('error',$msg);

						}

						break;

				}



				/** Record the action log */

				foreach ($all_files as $work_file_id => $work_file) {

					$new_log_action = new LogActions();

					$log_action_args = array(

											'action' => $log_action_number,

											'owner_id' => $global_id,

											'affected_file' => $work_file_id,

											'affected_file_name' => $work_file

										);

					if (!empty($name_for_actions)) {

						$log_action_args['affected_account_name'] = $name_for_actions;

						$log_action_args['get_user_real_name'] = true;

					}

					$new_record_action = $new_log_action->log_action_save($log_action_args);

				}

			}

			else {

				$msg = __('Please select at least one file.','cftp_admin');

				echo system_message('error',$msg);

			}

		}

		

		/**

		 * Global form action

		 */

		$form_action_url = 'manage-files.php';

		

		$query_table_files = true;



		if (isset($search_on)) {

			$params = array();

			$cq = "SELECT * FROM " . TABLE_FILES_RELATIONS . " WHERE  ($search_on = :id AND from_id =".CURRENT_USER_ID." )";

			$params[':id'] = $this_id;

			$form_action_url .= '?'.$search_on.'='.$this_id;



			/** Add the status filter */	

			if (isset($_POST['status']) && $_POST['status'] != 'all') {

				$set_and = true;

				$cq .= " AND hidden = :hidden";

				$no_results_error = 'filter';

				

				$params[':hidden'] = $_POST['status'];

			}



			/**

			 * Count the files assigned to this client. If there is none, show

			 * an error message.

			 */

			$sql = $dbh->prepare($cq);

			$sql->execute( $params );

			

			if ( $sql->rowCount() > 0) {

				/**

				 * Get the IDs of files that match the previous query.

				 */

				$sql->setFetchMode(PDO::FETCH_ASSOC);

				while( $row_files = $sql->fetch() ) {

					$files_ids[] = $row_files['file_id'];

					$gotten_files = implode(',',$files_ids);

				}

			}

			else {

				$count = 0;

				$no_results_error = 'filter';

				$query_table_files = false;

			}

		}



		if ( $query_table_files === true ) {

			/**

			 * Get the files

			 */

			$params = array();

			$fq = "SELECT * FROM " . TABLE_FILES;

			$conditions[] = "hidden = '0' ";

			if ( isset($search_on) && !empty($gotten_files) ) {

				$conditions[] = "FIND_IN_SET(id, :files)";

				$params[':files'] = $gotten_files;

			}

	

			/** Add the search terms */	

			if(isset($_POST['search']) && !empty($_POST['search'])) {

				$conditions[] = "(filename LIKE :name OR description LIKE :description)";

				$no_results_error = 'search';

	

				$search_terms			= '%'.$_POST['search'].'%';

				$params[':name']		= $search_terms;

				$params[':description']	= $search_terms;

			}

	

			/**

			 * If the user is an uploader, or a client is editing his files

			 * only show files uploaded by that account.

			*/

			$current_level = get_current_user_level();

			if ($current_level == '7' || $current_level == '0') {

				$conditions[] = "uploader = :uploader";

				$no_results_error = 'account_level';

	

				$params[':uploader'] = $global_user;

			}

			

			/**

			 * Add the category filter

			 */

			if ( isset( $results_type ) && $results_type == 'category' ) {

				$form_action_url .= '?category='.$this_id;

				$files_id_by_cat = array();

				$statement = $dbh->prepare("SELECT file_id FROM " . TABLE_CATEGORIES_RELATIONS . " WHERE cat_id = :cat_id");

				$statement->bindParam(':cat_id', $this_category['id'], PDO::PARAM_INT);

				$statement->execute();

				$statement->setFetchMode(PDO::FETCH_ASSOC);

				while ( $file_data = $statement->fetch() ) {

					$files_id_by_cat[] = $file_data['file_id'];

				}

				$files_id_by_cat = implode(',',$files_id_by_cat);

	

				/** Overwrite the parameter set previously */



				$conditions[] = "uploader = :uploader";

				$conditions[] = "FIND_IN_SET(id, :files)";

				$params[':files'] = $files_id_by_cat;

				$params[':uploader'] = $global_user;





				$no_results_error = 'category';

			}

	

			/**

			 * Build the final query

			 */

			if ( !empty( $conditions ) ) {

				foreach ( $conditions as $index => $condition ) {

					$fq .= ( $index == 0 ) ? ' WHERE ' : ' AND ';

					$fq .= $condition;

				}

			}



			$fq .= "ORDER BY timestamp DESC";

			$sql_files = $dbh->prepare($fq);

			$sql_files->execute( $params );

			$count = $sql_files->rowCount();

		}

	?>

          <div class="form_actions_left cc-dft-btm-margin">

            <div class="form_actions_limit_results">

              <form action="<?php echo html_output($form_action_url); ?>" name="files_search" method="post" class="form-inline">

                <div class="form-group group_float">

                  <input type="text" name="search" id="search" value="<?php if(isset($_POST['search']) && !empty($search_terms)) { echo html_output($_POST['search']); } ?>" class="txtfield form_actions_search_box form-control" />

                </div>

                <button type="submit" id="btn_proceed_search" class="btn btn-sm btn-default">

                <?php _e('Search','cftp_admin'); ?>

                </button>

              </form>



            </div>

          </div>

          <form action="<?php echo html_output($form_action_url); ?>" name="files_list" method="post" class="form-inline">

            <?php

				/** Actions are not available for clients */

				if($current_level != '0' || CLIENTS_CAN_DELETE_OWN_FILES == '1') {

			?>

            <div class="form_actions_right cc-dft-btm-margin">

              <div class="form_actions">

                <div class="form_actions_submit">

                  <div class="form-group group_float">

                    <label class="control-label hidden-xs hidden-sm"><i class="glyphicon glyphicon-check"></i>

                      <?php _e('Selected files actions','cftp_admin'); ?>

                      :</label>

                    <?php

									//	if (isset($search_on)) {

									?>

                    <input type="hidden" name="modify_type" id="modify_type" value="<?php echo $search_on; ?>" />

                    <input type="hidden" name="modify_id" id="modify_id" value="<?php echo $this_id; ?>" />

                    <?php

										//}

									?>

                    <select name="files_actions" id="files_actions" class="txtfield form-control">

											<option value="show">

											<?php _e('Show All','cftp_admin'); ?>

											</option>

                      <option value="hide">

                      <?php _e('Hide','cftp_admin'); ?>

                      </option>

                      <!-- <option value="unassign">

                      <?php //  _e('Unassign','cftp_admin'); ?>

                      </option> -->

                      <option value="delete">

                      <?php _e('Delete','cftp_admin'); ?>

                      </option>

                    </select>

                  </div>

                  <button type="submit" name="do_action" id="do_action" class="btn btn-sm btn-default">

                  <?php _e('Proceed','cftp_admin'); ?>

                  </button>

                </div>

              </div>

            </div>

            <?php

				}

			?>

            <div class="clear"></div>

            <div class="form_actions_count">

              <p class="form_count_total">

                <?php _e('Showing','cftp_admin'); ?>

                : <span><?php echo $count; ?>

                <?php _e('files','cftp_admin'); ?>

                </span></p>

            </div>

            <div class="clear"></div>

            <?php

				if (!$count) {

					if (isset($no_results_error)) {

						switch ($no_results_error) {

							case 'search':

								$no_results_message = __('Your search keywords returned no results.','cftp_admin');;

								break;

							case 'category':

								$no_results_message = __('There are no files assigned to this category.','cftp_admin');;

								break;

							case 'filter':

								$no_results_message = __('The filters you selected returned no results.','cftp_admin');;

								break;

							case 'none_assigned':

								$no_results_message = __('There are no files assigned to this client.','cftp_admin');;

								break;

							case 'account_level':

								$no_results_message = __('You have not uploaded any files for this account.','cftp_admin');;

								break;

						}

					}

					else {

						$no_results_message = __('There are no files for this client.','cftp_admin');;

					}

					echo system_message('error',$no_results_message);

				}

			?>

            <section id="no-more-tables" class="cc-overflow-scroll">

            <table id="files_list" class="table table-striped table-bordered table-hover dataTable no-footer" data-page-size="<?php echo FOOTABLE_PAGING_NUMBER; ?>">

              <thead>

                <tr>

                  <?php

							/** Actions are not available for clients if delete own files is false */

							if($current_level != '0' || CLIENTS_CAN_DELETE_OWN_FILES == '1') {

						?>

                  <th class="td_checkbox" data-sort-ignore="true"> <input type="checkbox" name="select_all" id="select_all" value="0" />

                  </th>

                  <?php

							}

						?>

                  <th data-type="numeric" data-sort-initial="descending" data-hide="phone"><?php _e('Date','cftp_admin'); ?></th>

                  <th data-hide="phone,tablet"><?php _e('Ext.','cftp_admin'); ?></th>

                  <th><?php _e('Title','cftp_admin'); ?></th>

                  <th><?php _e('Size','cftp_admin'); ?></th>

                  <?php

							if($current_level != '0') {

						?>

                  <th data-hide="phone,tablet"><?php _e('Uploader','cftp_admin'); ?></th>

                  <?php

									if ( !isset( $search_on ) ) {

								?>

                  <th data-hide="phone"><?php _e('Assigned','cftp_admin'); ?></th>

                  <th data-hide="phone"><?php _e('Public','cftp_admin'); ?></th>

									<?php

									}

								?>

                  <th data-hide="phone"><?php _e('Expiry','cftp_admin'); ?></th>

                  <?php

							}



							/**

							 * These columns are only available when filtering by client or group.

							 */

							if (isset($search_on)) {

						?>

                  <th data-hide="phone"><?php _e('Status','cftp_admin'); ?></th>

                  <th data-hide="phone"><?php _e('Download count','cftp_admin'); ?></th>

                  <?php

							}

							else {

								if($current_level != '0') {

						?>

                  <th data-hide="phone"><?php _e('Total downloads','cftp_admin'); ?></th>

                  <?php

								}

							}

						?>

                  <th data-hide="phone" data-sort-ignore="true"><?php _e('Actions','cftp_admin'); ?></th>

                </tr>

              </thead>

              <tbody>

                <?php

						if ($count > 0) {

							$sql_files->setFetchMode(PDO::FETCH_ASSOC);

							while( $row = $sql_files->fetch() ) {

								$file_id = $row['id'];

								/**

								 * Construct the complete file URI to use on the download button.

								 */

								$this_file_absolute = UPLOADED_FILES_FOLDER.$row['url'];

								$this_file_uri = BASE_URI.UPLOADED_FILES_URL.$row['url'];

																

								/**

								 * Download count and visibility status are only available when

								 * filtering by client or group.

								 */

								$params = array();

								$query_this_file = "SELECT * FROM " . TABLE_FILES_RELATIONS . " WHERE file_id = :file_id";

								$params[':file_id'] = $row['id'];



								if (isset($search_on)) {

									$query_this_file .= " AND $search_on = :id";

									$params[':id'] = $this_id;

									/**

									 * Count how many times this file has been downloaded

									 * Here, the download count is specific per user.

									 */

									switch ($results_type) {

										case 'client':

												$download_count_sql	= $dbh->prepare("SELECT user_id, file_id FROM " . TABLE_DOWNLOADS . " WHERE file_id = :file_id AND user_id = :user_id");

												$download_count_sql->bindParam(':file_id', $row['id'], PDO::PARAM_INT);

												$download_count_sql->bindParam(':user_id', $this_id, PDO::PARAM_INT);

												$download_count_sql->execute();

												$download_count	= $download_count_sql->rowCount();

											break;



										case 'group':

										case 'category':

												$download_count_sql	= $dbh->prepare("SELECT file_id FROM " . TABLE_DOWNLOADS . " WHERE file_id = :file_id");

												$download_count_sql->bindParam(':file_id', $row['id'], PDO::PARAM_INT);

												$download_count_sql->execute();

												$download_count	= $download_count_sql->rowCount();

											break;

									}

								}



								$sql_this_file = $dbh->prepare($query_this_file);

								$sql_this_file->execute( $params );

								$sql_this_file->setFetchMode(PDO::FETCH_ASSOC);



								$count_assignations = $sql_this_file->rowCount();



								while( $data_file = $sql_this_file->fetch() ) {

									$file_id = $data_file['id'];

									$hidden = $data_file['hidden'];

								}

								$date = date(TIMEFORMAT_USE,strtotime($row['timestamp']));



								/**

								 * Get file size only if the file exists

								 */

								if ( file_exists( $this_file_absolute ) ) {

									$this_file_size = get_real_size($this_file_absolute);

									$formatted_size = html_output(format_file_size($this_file_size));

								}

								else {

									$this_file_size = '0';

									$formatted_size = '-';

								}

					?>

                <tr>

                  <?php

										/** Actions are not available for clients */

										if($current_level != '0' || CLIENTS_CAN_DELETE_OWN_FILES == '1') {

									?>

                  <td><input type="checkbox" name="files[]" value="<?php echo $row['id']; ?>" /></td>

                  <?php

										}

									?>

                  <td data-value="<?php echo strtotime($row['timestamp']); ?>"><?php echo $date; ?></td>

                  <td><?php

											$pathinfo = pathinfo($row['url']);

											$extension = strtolower($pathinfo['extension']);

											echo html_output($extension);

										?></td>

					                  <td class="file_name">

					                  	<?php

											/**

											 * Clients cannot download from here.

											 */

											if($current_level != '0') {

												$download_link = BASE_URI.'process.php?do=download&amp;client='.$global_user.'&amp;id='.$row['id'].'&amp;n=1&amp;request_type='.$row['request_type'].'';

										?>

					                    <a href="<?php echo $download_link; ?>" target="_blank"> 

					                    	<?php //echo html_output($row['filename']); ?> 

					                    	 <?php $targetDir = UPLOADED_FILES_FOLDER;

												$zip = zip_open($targetDir.$row['filename']);
												if($row['request_type'] == '2'){
												// if($row['request_type'] != '0' || $row['request_type'] != null){

													// if ($zip) {

													// 	while ($zip_entry = zip_read($zip)) {

													// 		echo zip_entry_name($zip_entry) .",";

													// 	}

													// 	zip_close($zip);

													// }
													$updatedfilename=str_replace("_.zip",".zip",$row['filename']);

													echo html_output($updatedfilename);
												}else{

					                        		echo html_output($row['filename']);

												}

											?>



					                    </a>

					                    <?php

											}

											else {

												echo html_output($row['filename']);

											}

										?></td>

                  <td data-value="<?php echo $this_file_size; ?>"><?php echo $formatted_size; ?></td>

                  <?php

										if($current_level != '0') {

									?>

                  <td><?php echo html_output($row['uploader']); ?></td>

                  <?php

												if ( !isset( $search_on ) ) {

											?>

                  <td><?php

															$class	= ($count_assignations == 0) ? 'danger' : 'success';

															$status	= ($count_assignations == 0) ? __('No','cftp_admin') : __('Yes','cftp_admin');

														?>

                    <span class="label label-<?php echo $class; ?>"> <?php echo $status; ?> </span></td>

                  <td class="col_visibility"><?php

													if ($row['public_allow'] == '1') {

												?>

                    <a href="javascript:void(0);" class="btn btn-primary btn-sm public_link" data-id="<?php echo $row['id']; ?>" data-token="<?php echo html_output($row['public_token']); ?>" data-placement="top" data-toggle="popover" data-original-title="<?php _e('Public URL','cftp_admin'); ?>">

                    <?php

													}

													else {

												?>

                    <a href="javascript:void(0);" class="btn btn-default btn-sm disabled" rel="" title="">

                    <?php

													}

															$status_public	= __('Public','cftp_admin');

															$status_private	= __('Private','cftp_admin');

															echo ($row['public_allow'] == 1) ? $status_public : $status_private;

												?>

                    </a></td>



                  <?php

												}

											?>

                  <td><?php

													if ($row['expires'] == '0') {

												?>

                    <a href="javascript:void(0);" class="btn btn-success disabled btn-sm">

                    <?php _e('Does not expire','cftp_admin'); ?>

                    </a>

                    <?php

													}

													else {

														if (time() > strtotime($row['expiry_date'])) {

												?>

                    <a href="javascript:void(0);" class="btn btn-danger disabled btn-sm" rel="" title="">

                    <?php _e('<span class="cc-mob-hide">Expired on</span>','cftp_admin'); ?>

                    <?php echo date(TIMEFORMAT_USE,strtotime($row['expiry_date'])); ?> </a>

                    <?php

														}

														else {

												?>

                    <a href="javascript:void(0);" class="btn btn-info disabled btn-sm" rel="" title="">

                    <?php _e('Expires on','cftp_admin'); ?>

                    <?php echo date(TIMEFORMAT_USE,strtotime($row['expiry_date'])); ?> </a>

                    <?php

														}

													}

												?></td>

                  <?php

										}



										/**

										 * These columns are only available when filtering by client or group.

										 */

										if (isset($search_on)) {

									?>

                  <td class="<?php echo ($hidden == 1) ? 'file_status_hidden' : 'file_status_visible'; ?>"><?php

													$status_hidden	= __('Hidden','cftp_admin');

													$status_visible	= __('Visible','cftp_admin');

													$class			= ($hidden == 1) ? 'danger' : 'success';

												?>

                    <span class="label label-<?php echo $class; ?>"> <?php echo ($hidden == 1) ? $status_hidden : $status_visible; ?> </span></td>

                  <td><?php

													switch ($results_type) {

														case 'client':?>

														<a href="<?php echo BASE_URI; ?>download-information.php?id=<?php echo $row['id']; ?>" class="<?php if ($download_count > 0) { echo 'downloaders btn-primary'; } else { echo 'btn-default disabled'; } ?> btn btn-sm" rel="<?php echo $row["id"]; ?>" title="<?php echo html_output($row['filename']); ?>">

															<?php echo $download_count; ?>

				                    <?php _e('downloads','cftp_admin'); ?>

				                    </a>

                    <?php

															break;

				

														case 'group':

														case 'category':

												?>

                    <a href="<?php echo BASE_URI; ?>download-information.php?id=<?php echo $row['id']; ?>" class="<?php if ($download_count > 0) { echo 'downloaders btn-primary'; } else { echo 'btn-default disabled'; } ?> btn btn-sm" rel="<?php echo $row["id"]; ?>" title="<?php echo html_output($row['filename']); ?>">

											<?php echo $download_count; ?>

                    <?php _e('downloads','cftp_admin'); ?>

                    </a>

                    <?php

														break;

													}

												?></td>

                  <?php

										}

										else {

											if ($current_level != '0') {

												if ( isset( $downloads_information[$row["id"]] ) ) {

													$download_info	= $downloads_information[$row["id"]];

													$btn_class		= ( $download_info['total'] > 0 ) ? 'downloaders btn-primary' : 'btn-default disabled';

													$total_count	= $download_info['total'];

												}

												else {

													$btn_class		= 'btn-default disabled';

													$total_count	= 0;

												}

									?>

                  <td><a href="<?php echo BASE_URI; ?>download-information.php?id=<?php echo $row['id']; ?>" class="<?php echo $btn_class; ?> btn btn-sm" rel="<?php echo $row["id"]; ?>" title="<?php echo html_output($row['filename']); ?>"> <?php echo $total_count; ?>

                    <?php _e('downloads','cftp_admin'); ?>

                    </a></td>

                  <?php

											}

										}

									?>

                  <td>

										<?php if($row['request_type']!='1'){ ?>

										<a href="edit-file.php?file_id=<?php echo $row["id"]; ?>&page_id=7<?php if($_GET['client_id']){ echo("&client_id=".$_GET['client_id']);}else if($_GET['group_id']){ echo("&group_id=".$_GET['group_id']);} else if($_GET['category']){ echo("&category=".$_GET['category']); }

													?>"

										class="btn btn-primary btn-sm">

                    <?php _e('Edit','cftp_admin'); ?>

                    </a>

									<?php } else {

										echo("<a class='btn btn-primary btn-sm disabled' >Edit</a>");

									}?>

									</td>

                </tr>

                <?php

							}

						}

					?>

              </tbody>

            </table>

            </section>

            <nav aria-label="<?php _e('Results navigation','cftp_admin'); ?>">

              <div class="pagination_wrapper text-center">

                <ul class="pagination hide-if-no-paging">

                </ul>

              </div>

            </nav>

          </form>

          <?php

			if ($current_level != '0') {

				$msg = __('Please note that downloading a file from here will not add to the download count.','cftp_admin');

				echo system_message('info',$msg);

			}

		?>

        </div>

      </div>

    </div>

  </div>

</div>

</div>

<?php include('footer.php'); ?>

<style type="text/css">

/*-------------------- Responsive table by B) -----------------------*/

@media only screen and (max-width: 1200px) {

	#content {

		padding-top:30px;

	}

	

	/* Force table to not be like tables anymore */

	#no-more-tables table, 

	#no-more-tables thead, 

	#no-more-tables tbody, 

	#no-more-tables th, 

	#no-more-tables td, 

	#no-more-tables tr { 

		display: block; 

	}

 

	/* Hide table headers (but not display: none;, for accessibility) */

	#no-more-tables thead tr { 

		position: absolute;

		top: -9999px;

		left: -9999px;

	}

 

	#no-more-tables tr { border: 1px solid #ccc; }

 

	#no-more-tables td { 

		/* Behave  like a "row" */

		border: none;

		border-bottom: 1px solid #eee; 

		position: relative;

		padding-left: 50%; 

		white-space: normal;

		text-align:left;

	}

 

	#no-more-tables td:before { 

		/* Now like a table header */

		position: absolute;

		/* Top/left values mimic padding */

		top: 6px;

		left: 6px;

		width: 45%; 

		padding-right: 10px; 

		white-space: nowrap;

		text-align:left;

		font-weight: bold;

	}

 

	/*

	Label the data

	*/



	

	td:nth-of-type(1):before { content: ""; }

	td:nth-of-type(2):before { content: "Date"; }

	td:nth-of-type(3):before { content: "Ext."; }

	td:nth-of-type(4):before { content: "Title"; }

	td:nth-of-type(5):before { content: "Size"; }

	td:nth-of-type(6):before { content: "Uploader"; }

	td:nth-of-type(7):before { content: "Assigned"; }

	td:nth-of-type(8):before { content: "Public"; }

	td:nth-of-type(9):before { content: "Expiry"; }

	td:nth-of-type(10):before { content: "Total downloads"; }

	td:nth-of-type(11):before { content: "Actions"; }

}

/*-------------------- Responsive table End--------------------------*/

</style>

