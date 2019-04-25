<?php
/**
 * Allows to hide, show or delete the files assigend to the
 * selected client.
 *
 * @package ProjectSend
 */
$load_scripts   = array(
                        'footable',
                    );
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
$active_nav = 'files';
$cc_active_page = 'Draft Files';
$page_title = __('Draft Files','cftp_admin');
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
Fetch all categories
*/
    $statement = $dbh->prepare("SELECT * FROM " . TABLE_CATEGORIES);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $categories = $statement->fetchAll();
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
?>
<form id="dynamic-draft-form" action="upload-process-form.php" method="post" >
<input type="hidden" name="add[]" id="draft" value=""></input></form>
<script type="text/javascript">
    $(document).ready(function() {
        $("#do_action").click(function() {
            var checks = $("td input:checkbox").serializeArray();
            if (checks.length == 0) {
                alert('<?php _e('Please select at least one file to proceed.','cftp_admin'); ?>');
                return false;
            }
            else {
                var action = $('#files_actions').val();
                if (action == 'delete') {
                    var msg_1 = '<?php _e("You are about to delete",'cftp_admin'); ?>';
                    var msg_2 = '<?php _e("files permanently and for every client/group. Are you sure you want to continue?",'cftp_admin'); ?>';
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
        <?php
            if ($results_type != 'client') {

            }
        ?>
        $('.public_link').popover({ 
            html : true,
            content: function() {
                var id      = $(this).data('id');
                var token   = $(this).data('token');
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
  <!-- MAIN CONTENT -->
  <div id="content"> 
    <!-- Added by B) -------------------->
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <h2 class="page-title txt-color-blueDark"><?php echo $page_title; ?></h2>
          <?php
        /**
         * Apply the corresponding action to the selected files.
         */
        if(isset($_POST['do_action'])) {
            /** Continue only if 1 or more files were selected. */
            if(!empty($_POST['orphans'])) {
            $selected_orphans =$_POST['orphans'];
          }

            if(!empty($_POST['files']) ||!empty($_POST['orphans']) ) {
                $selected_files = array_map('intval',array_unique($_POST['files']));
                $files_to_get = implode(',',$selected_files);
                $sql_file = $dbh->prepare("SELECT id, filename FROM " . TABLE_FILES . " WHERE FIND_IN_SET(id, :files)");
                $sql_file->bindParam(':files', $files_to_get);
                $sql_file->execute();
                $sql_file->setFetchMode(PDO::FETCH_ASSOC);
                while( $data_file = $sql_file->fetch() ) {
                    $all_files[$data_file['id']] = $data_file['filename'];
                }
                switch($_POST['files_actions']) {
                      case 'delete':
                        $delete_results = array(
                                                'ok'        => 0,
                                                'errors'    => 0,
                                            );
                        if(!empty($selected_files)){
                          foreach ($selected_files as $index => $file_id) {
                              $this_file      = new FilesActions();
                              $delete_status  = $this_file->delete_files($file_id);
                              if ( $delete_status == true ) {
                                  $delete_results['ok']++;
                              }
                              else {
                                  $delete_results['errors']++;
                                  unset($all_files[$file_id]);
                              }
                          }
                        }
                        if(!empty($selected_orphans)){
                          foreach ($selected_orphans as  $filename) {
                            unlink(UPLOADED_FILES_FOLDER.$filename);
                            $delete_results['ok']++;
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
        $form_action_url = 'draft.php';
        $query_table_files = true;
        if (isset($search_on)) {
            $params = array();
            $cq = "SELECT * FROM " . TABLE_FILES_RELATIONS . " WHERE $search_on = :id";
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
            if ( isset($search_on) && !empty($gotten_files) ) {
                $conditions[] = "FIND_IN_SET(id, :files)";
                $params[':files'] = $gotten_files;
            }
            /** Add the search terms */ 
            if(isset($_GET['search']) && !empty($_GET['search'])) {
                $conditions[] = "(filename LIKE :name OR description LIKE :description)";
                $no_results_error = 'search';
                $search_terms           = '%'.$_GET['search'].'%';
                $params[':name']        = $search_terms;
                $params[':description'] = $search_terms;
            }
            /**
             * If the user is an uploader, or a client is editing his files
             * only show files uploaded by that account.
            */
            $current_level = get_current_user_level();
            if ($current_level == '7' || $current_level == '8' || $current_level == '0' || $current_level == '9') {
                $no_results_error = 'account_level';
                $params[':uploader'] = $global_user;
            }
            /**
             * Add the category filter
             */
            if ( isset( $results_type ) && $results_type == 'category' ) {  
                $files_id_by_cat = array();
                $statement = $dbh->prepare("SELECT file_id FROM " . TABLE_CATEGORIES_RELATIONS . " WHERE cat_id = :cat_id");
                $statement->bindParam(':cat_id', $this_category['id'], PDO::PARAM_INT);
                $statement->execute();
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $file_data = $statement->fetchAll();
                
                if(!empty($file_data)) {
                    foreach ( $file_data as $data) {
                        $files_id_by_cat[] = $data['file_id'];
                    }
                    
                    $files_id_by_cat = implode(',',$files_id_by_cat);
                    /** Overwrite the parameter set previously */
                    $conditions1[] = "FIND_IN_SET(tbl_files.id, '".$files_id_by_cat."')";
                    $params[':files'] = $files_id_by_cat;
                }
                else {
                    $conditions1[] = "FIND_IN_SET(tbl_files.id, 'not found')";
                    $no_results_error = 'category';
                }
                
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
            $fq1 = "SELECT * FROM " . TABLE_FILES;
            if ($current_level == '7' || $current_level == '8' || $current_level == '0' || $current_level == '9') { 
                $conditions1[] = "tbl_files.uploader ='". CURRENT_USER_USERNAME."'";
                $conditions1[] = "tbl_files.id NOT IN(SELECT tbl_files_relations.file_id FROM tbl_files_relations WHERE tbl_files_relations.from_id = '". CURRENT_USER_ID."')";
                $conditions1[] =  "tbl_files.public_allow=0";
            }
            if(isset($_GET['search']) && !empty($_GET['search'])) {
                $term = "%".$_GET['search']."%";
            
                $conditions1[] = "(filename LIKE '$term' OR description LIKE '$term')";
                $no_results_error = 'search';
            }
            
            if ( !empty( $conditions1 ) ) {

                foreach ( $conditions1 as $index => $condition ) {

                    if($index == 0) {
                        $var_1 = 'WHERE';
                    }
                    else {
                        $var_1 = 'AND';
                    }
                    $fq1 .= ' '.$var_1.' '.$condition;

                }

            }
            
            $sql_files_draft = $dbh->prepare($fq1); 
            $sql_files_draft->execute();  
            $draft_count = $sql_files_draft->rowCount();            
            
            $current_date = date("Y-m-d");
         $q_sent_file = "SELECT * FROM tbl_files WHERE tbl_files.uploader ='".CURRENT_USER_USERNAME ."' AND tbl_files.id NOT IN(SELECT tbl_files_relations.file_id FROM tbl_files_relations WHERE tbl_files_relations.from_id = '". CURRENT_USER_ID."')  AND tbl_files.future_send_date >='".$current_date."' AND  tbl_files.public_allow=0 "; 
            $sql_files = $dbh->prepare($q_sent_file);  
            $sql_files->execute();
            $count = $sql_files->rowCount();
        }
/*=========================================orphan files===========================================================*/
		$work_folder = UPLOADED_FILES_FOLDER;
		/**
		 * Make a list of existing files on the database.
		 * When a file doesn't correspond to a record, it can
		 * be safely renamed.
		 */
		$sql = $dbh->query("SELECT url, id, public_allow, uploader FROM " . TABLE_FILES );
		$db_files = array();
		$sql->setFetchMode(PDO::FETCH_ASSOC);
		while ( $row = $sql->fetch() ) {
			$db_files[$row["url"]] = $row["id"];
			if ($row['public_allow'] == 1) {
				$db_files_public[$row["url"]] = $row["id"];
			}
		}
		//var_dump($sql); exit;
		/** Make an array of already assigned files */
		$sql = $dbh->query("SELECT DISTINCT file_id FROM " . TABLE_FILES_RELATIONS . " WHERE client_id IS NOT NULL OR group_id IS NOT NULL OR folder_id IS NOT NULL");
		$assigned = array();
		$sql->setFetchMode(PDO::FETCH_ASSOC);
		while ( $row = $sql->fetch() ) {
			$assigned[] = $row["file_id"];
		}
		//var_dump($sql); exit;
		/** We consider public file as assigned file */
    if(isset($db_files_public)){
  		foreach ($db_files_public as $file_id){
  			$assigned[] = $file_id;
  		}
    }
		/** Read the temp folder and list every allowed file */
		if ($handle = opendir($work_folder))  {
			while (false !== ($filename = readdir($handle))) {
				$filename_path = $work_folder.'//'.$filename;
				if(!is_dir($filename_path)) {
					if ($filename != "." && $filename != "..") {
						/** Check types of files that are not on the database */
						$x_id = CURRENT_USER_USERNAME ;
						if (!array_key_exists($filename,$db_files)) {
							$file_object = new PSend_Upload_File();
							$new_filename = $file_object->safe_rename_on_disk( $filename , $work_folder );
							/** Check if the filetype is allowed */
							if ($file_object->is_filetype_allowed($new_filename)) {
								/** Add it to the array of available files */
								$new_filename_path = $work_folder.'/'.$new_filename;
								//$files_to_add[$new_filename] = $new_filename_path;
								$files_to_add[] = array('path'		=> $new_filename_path,
														'name'		=> $new_filename,
														'reason'	=> 'not_on_db',);
							}
						}
					}
				}
			}
			closedir($handle);
		}
    if (isset($_GET['search'])) {

    			$search = htmlspecialchars($_GET['search']);
    			function search_text($item) {
    				global $search;
    				if (stripos($item['name'], $search) !== false) {
    					/**
    					 * Items that match the search
    					 */
    					return true;
    				}
    				else {
    					/**
    					 * Remove other items
    					 */
    					unset($item);
    				}
    				return false;
    			}
    			$files_to_add = array_filter($files_to_add, 'search_text');
    		}

    $orphanCount=0;
    if(isset($files_to_add) && count($files_to_add) > 0 ) {
    foreach ($files_to_add as $opfile){
      $x=explode("_", $opfile['name']);
      $cuid_arr=explode(".", $opfile['name']);
      $or_arr = array_reverse(preg_split('/(_)/',$cuid_arr[0],-1, PREG_SPLIT_NO_EMPTY));
      if($or_arr[0]==CURRENT_USER_ID){
        $orphanCount++;
      }
    }
    }
    $draft_count += $orphanCount;
/*=========================================orphan files end===========================================================*/
    ?>


          <div class="clear"></div>
<?php/*========================================orphen end===============================================*/ ?>
<div class="form_actions_left">
            <div class="form_actions_limit_results">
              <form action="<?php echo html_output($form_action_url); ?>" name="files_search" method="GET" class="form-inline">
                <div class="form-group group_float">
                  <input type="text" name="search" id="search" value="<?php if(isset($_GET['search']) && !empty($search_terms)) { echo html_output($_GET['search']); } ?>" class="txtfield form_actions_search_box form-control" />
                </div>

                <div class="form-group group_float">
                  <select name="category" id="category" class="txtfield form-control">
                    <option value="0">All categories</option>
                    <?php

									if(!empty($categories)){

										foreach ( $categories as $cat ) {
											if($cat['parent'] == ''){
									?>
                    <option <?php if(!empty($this_id)){if($this_id == $cat['id'] ){ echo "selected";}}?> value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php
										foreach ($categories as $childcat) {
											if($childcat['parent'] == $cat['id']){
											 ?>
											<option <?php if(!empty($this_id)){if($this_id == $childcat['id'] ){ echo "selected";}}?> value="<?php echo $childcat['id']; ?>"> &nbsp&nbsp<?php echo $childcat['name']; ?></option>
											<?php
												}
											}
											}
										}
									}

									?>
                  </select>
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
                // if($current_level != '0' || CLIENTS_CAN_DELETE_OWN_FILES == '1') {
            ?>
            <div class="form_actions_right">
              <div class="form_actions">
                <div class="form_actions_submit">
                  <div class="form-group group_float">
                    <label class="control-label hidden-xs hidden-sm"><i class="glyphicon glyphicon-check"></i>
                      <?php _e('Selected files actions','cftp_admin'); ?>
                      :</label>
                    <?php
                                        if (isset($search_on)) {
                                    ?>
                    <input type="hidden" name="modify_type" id="modify_type" value="<?php echo $search_on; ?>" />
                    <input type="hidden" name="modify_id" id="modify_id" value="<?php echo $this_id; ?>" />
                    <?php
                                        }
                                    ?>
                    <select name="files_actions" id="files_actions" class="txtfield form-control">
                      <?php
                                            /** Options only available when viewing a client/group files list */
                                            if (isset($search_on)) {
                                        ?>
                      <option value="hide">
                      <?php _e('Hide','cftp_admin'); ?>
                      </option>
                      <option value="show">
                      <?php _e('Show','cftp_admin'); ?>
                      </option>
                      <option value="unassign">
                      <?php _e('Unassign','cftp_admin'); ?>
                      </option>
                      <?php
                                            }
                                        ?>
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
                // }
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
                if (!$draft_count) {
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
<?php
if(isset($_REQUEST['edit']) == 1){echo '<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert">×</a>The file has been edited successfully.</div>';}
?>
            <table id="files_list" class="cc-mail-listing-style table table-striped table-bordered table-hover dataTable no-footer" data-page-size="<?php echo FOOTABLE_PAGING_NUMBER; ?>">
              <thead>
                <tr>

                  <th class="td_checkbox" data-sort-ignore="true">
                      <label class="cc-chk-container">
                          <input type="checkbox" name="select_all" class="select_all1" value="0" />
                          <span class="checkmark"></span>
                      </label>
                  </th>
                  <th data-type="numeric" data-sort-initial="descending" data-hide="phone"><?php _e('Date','cftp_admin'); ?></th>
                  <th data-hide="phone,tablet"><?php _e('Ext.','cftp_admin'); ?></th>
                  <th><?php _e('Title','cftp_admin'); ?></th>
                  <th><?php _e('Size','cftp_admin'); ?></th>
                  <th data-hide="phone,tablet"><?php _e('Uploader','cftp_admin'); ?></th>
                  <th data-hide="phone"><?php _e('Expiry','cftp_admin'); ?></th>
                  <?php
                            /**
                             * These columns are only available when filtering by client or group.
                             */
                            if (isset($search_on)) {
                        ?>
                  <th data-hide="phone"><?php _e('Status','cftp_admin'); ?></th>
                  <?php
                            }
                        ?>
                <th><?php _e('Prev. Assigned','cftp_admin'); ?></th>
                 <th data-hide="phone" data-sort-ignore="true"><?php _e('Actions','cftp_admin'); ?></th>
                </tr>
              </thead>
              <tbody>
              <?php
							$curr_usr_id =	CURRENT_USER_ID;
							if(isset($files_to_add) && count($files_to_add) > 0 ) {
							foreach ($files_to_add as $add_file){
								$x=explode("_", $add_file['name']);
								$cuid_array=explode(".", $add_file['name']);
								$arr = array_reverse(preg_split('/(_)/',$cuid_array[0],-1, PREG_SPLIT_NO_EMPTY));
								if($arr[0]==$curr_usr_id){
                  ?>
                <tr>
              <td>
                <label class="cc-chk-container">
                    <input type="checkbox" name="orphans[]" value="<?php echo html_output($add_file['name']); ?>" />
                    <span class="checkmark"></span>
                </label>
              </td>
              <td data-value="<?php echo filemtime($add_file['path']); ?>"><?php echo date(TIMEFORMAT_USE, filemtime($add_file['path'])); ?></td>
              <td><?php echo(pathinfo($add_file['name'], PATHINFO_EXTENSION)); ?></td>
              <td>
                <a draft-file= "<?php echo html_output($add_file['name']); ?>"  class="btn-ftp">
              <?php _e(html_output($add_file['name']),'cftp_admin'); ?>
                </a>
              </td>
              <td data-value="<?php echo filesize($add_file['path']); ?>"><?php echo html_output(format_file_size(get_real_size($add_file['path']))); ?></td>
              <td colspan="4"> </td>
              </tr>
              <?php
							}
						}
							}
						?>
                <?php
                        if ($count > 0) {
                            $sql_files_draft->setFetchMode(PDO::FETCH_ASSOC);
                            while( $row = $sql_files_draft->fetch() ) {
                            //echo "<pre>";print_r($row);echo "</pre>";
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
							$query_this_file="SELECT * FROM tbl_files WHERE tbl_files.uploader ='".CURRENT_USER_USERNAME ."' AND tbl_files.id NOT IN(SELECT tbl_files_relations.file_id FROM tbl_files_relations WHERE tbl_files_relations.from_id = '". CURRENT_USER_ID."')  AND tbl_files.future_send_date <='".$current_date."'   ";
								
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
                                                $download_count_sql = $dbh->prepare("SELECT user_id, file_id FROM " . TABLE_DOWNLOADS . " WHERE file_id = :file_id AND user_id = :user_id");
                                                $download_count_sql->bindParam(':file_id', $row['id'], PDO::PARAM_INT);
                                                $download_count_sql->bindParam(':user_id', $this_id, PDO::PARAM_INT);
                                                $download_count_sql->execute();
                                                $download_count = $download_count_sql->rowCount();
                                            break;
                                        case 'group':
                                        case 'category':
                                                $download_count_sql = $dbh->prepare("SELECT file_id FROM " . TABLE_DOWNLOADS . " WHERE file_id = :file_id");
                                                $download_count_sql->bindParam(':file_id', $row['id'], PDO::PARAM_INT);
                                                $download_count_sql->execute();
                                                $download_count = $download_count_sql->rowCount();
                                            break;
                                    }
                                }
                                $sql_this_file = $dbh->prepare($query_this_file);
                                $sql_this_file->execute( $params );
                                $sql_this_file->setFetchMode(PDO::FETCH_ASSOC);
                                $count_assignations = $sql_this_file->rowCount();
                                while( $data_file = $sql_this_file->fetch() ) {
                                //echo "<pre>";print_r($data_file);echo "</pre>";
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
        if(($row['expires'] == '0') || (time() < strtotime($row['expiry_date']))) {
                    ?>
                <tr>

                  <td>
                  <label class="cc-chk-container">
                      <input type="checkbox" name="files[]" value="<?php echo $row['id']; ?>" />
                      <span class="checkmark"></span>
                  </label>
                  </td>
                  <td data-value="<?php echo strtotime($row['timestamp']); ?>"><?php echo $date; ?></td>
                  <td><?php
                                            $pathinfo = pathinfo($row['url']);
                                            $extension = strtolower($pathinfo['extension']);
                                            echo html_output($extension);
                                        ?></td>
                  <td class="file_name"><?php
                                            $download_link = BASE_URI.'process.php?do=download&amp;client='.$global_user.'&amp;id='.$row['id'].'&amp;n=1';
                                        ?>
                                      <a href="<?php echo $download_link; ?>" target="_blank"> <?php echo html_output($row['filename']); ?> </a>
                                      </td>
                  <td data-value="<?php echo $this_file_size; ?>"><?php echo $formatted_size; ?></td>
                  <td>
                        <a href="edit-file.php?file_id=<?php echo $row["id"]; ?>&page_id=4" class="btn-sm">
                        <?php _e(html_output($row['uploader']),'cftp_admin'); ?>
                        </a>
</td>

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
                    <?php _e('Expired on','cftp_admin'); ?>
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
                                        /**
                                         * These columns are only available when filtering by client or group.
                                         */
                                        if (isset($search_on)) {
                                    ?>
                  <td class="<?php echo ($hidden == 1) ? 'file_status_hidden' : 'file_status_visible'; ?>"><?php
                                                    $status_hidden  = __('Hidden','cftp_admin');
                                                    $status_visible = __('Visible','cftp_admin');
                                                    $class          = ($hidden == 1) ? 'danger' : 'success';
                                                ?>
                    <span class="label label-<?php echo $class; ?>"> <?php echo ($hidden == 1) ? $status_hidden : $status_visible; ?> </span></td>
                    <?php        }      ?>

                  <td>
                    <?php
                    if($row['prev_assign']=='1'){
                     echo("YES");
                   }
                   else {
                     echo("NO");
                    }
                     ?>
                   </td>
                  <td><a href="edit-file.php?file_id=<?php echo $row["id"]; ?>&page_id=4" class="btn btn-primary btn-sm">
                    <?php _e('Edit','cftp_admin'); ?>
                    </a></td> 
                </tr>
                <?php
                                }
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
<?php include('footer.php'); ?>
 <script>
 $(document).ready(function(e) {
var numfiles = document.querySelectorAll("#files_list tbody tr");
var totalcount = document.querySelectorAll(".form_count_total span");
totalcount[0].innerHTML = numfiles.length + " files";
});
 </script>
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
    td:nth-of-type(7):before { content: "Expiry"; }
}
/*-------------------- Responsive table End--------------------------*/
</style>
<script >
$('.btn-ftp').on('click', function(){

    $('#draft').val($( this ).attr( "draft-file" ));
    $('#dynamic-draft-form').submit();

  });
</script>
