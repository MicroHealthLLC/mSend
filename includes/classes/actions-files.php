<?php
/**
 * Class that handles all the actions and functions that can be applied to
 * the already uploaded files.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 */

class FilesActions
{

	var $files = array();

	function __construct() {
		global $dbh;
		$this->dbh = $dbh;
	}

	function delete_files($rel_id)
	{
	   // var_dump('221222');die();
		$this->can_delete		= false;
		$this->result			= '';
		$this->check_level		= array(9,8,0);

		if (isset($rel_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->file_id = $rel_id;
				$this->sql = $this->dbh->prepare("SELECT url, uploader, tbl_drop_off_request_id FROM " . TABLE_FILES . " WHERE id = :file_id");
				$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
				$this->sql->execute();
				$this->sql->setFetchMode(PDO::FETCH_ASSOC);
				while( $this->row = $this->sql->fetch() ) {
					if ( CURRENT_USER_LEVEL == '0' ) {
						if ( CLIENTS_CAN_DELETE_OWN_FILES == '1' && $this->row['uploader'] == CURRENT_USER_USERNAME ) {
							$this->can_delete	= true;
						}
					}
					else {
						$this->can_delete	= true;
					}

					$this->file_url = $this->row['url'];
				    $this->request_id=$this->row['tbl_drop_off_request_id'];
				}

            	$clientid = $this->dbh->prepare("SELECT from_id,reqclientid,status FROM tbl_drop_off_request WHERE id = :req_id");
				$clientid->bindParam(':req_id', $this->request_id, PDO::PARAM_INT);
				$clientid->execute();
				$clientid->setFetchMode(PDO::FETCH_ASSOC);
				$reqinfo=$clientid->fetch();
				
				// var_dump($reqinfo);die();
				
// 		var_dump($reqinfo['reqclientid']);

				/** Delete the reference to the file on the database */
				if ( true === $this->can_delete ) {
				    if($this->request_id!=0){
				        if($reqinfo['status']==0){
				        
				        	$this_file_absolute =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$this->request_id.'/*';
        				    $filefolderpath =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$this->request_id;
				        
				            $filerelationdelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
        					$filerelationdelt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
        					$filerelationdelt->execute();
        					
        					$this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
        					$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
        					$this->sql->execute();
        					
        				    $filepositiondelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_pos_details WHERE tbl_draw_sign_details_id = :request_id");
        					$filepositiondelt->bindParam(':request_id', $this->request_id, PDO::PARAM_INT);
        					$filepositiondelt->execute();
        					
        					$filedrawdelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_details WHERE drop_off_request_id = :drequest_id");
        					$filedrawdelt->bindParam(':drequest_id', $this->request_id, PDO::PARAM_INT);
        					$filedrawdelt->execute();
        					
        					$dropoffdelt = $this->dbh->prepare("DELETE FROM tbl_drop_off_request WHERE id = :reqid");
        					$dropoffdelt->bindParam(':reqid', $this->request_id, PDO::PARAM_INT);
        					$dropoffdelt->execute();
        					
        				    $files1 = glob($this_file_absolute); // get all file names
                            foreach($files1 as $file1){ // iterate files
                                if(is_file($file1))
                                unlink($file1); // delete file
                            }
                            rmdir($filefolderpath);    
                            
				        }else{
				            $client_id = $this->dbh->prepare("SELECT req_status FROM " . TABLE_FILES . " WHERE id = :file_id");
            				$client_id->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
            				$client_id->execute();
            				$client_id->setFetchMode(PDO::FETCH_ASSOC);
            				$reqinfo11=$client_id->fetch();
        				    if($reqinfo11['req_status']!=0){
        				        $this_file_absolute =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$this->request_id.'/*';
        				    $filefolderpath =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$this->request_id;
				        
				            $filerelationdelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
        					$filerelationdelt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
        					$filerelationdelt->execute();
        					
        					$this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
        					$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
        					$this->sql->execute();
        					
        				    $filepositiondelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_pos_details WHERE tbl_draw_sign_details_id = :request_id");
        					$filepositiondelt->bindParam(':request_id', $this->request_id, PDO::PARAM_INT);
        					$filepositiondelt->execute();
        					
        					$filedrawdelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_details WHERE drop_off_request_id = :drequest_id");
        					$filedrawdelt->bindParam(':drequest_id', $this->request_id, PDO::PARAM_INT);
        					$filedrawdelt->execute();
        					
        					$dropoffdelt = $this->dbh->prepare("DELETE FROM tbl_drop_off_request WHERE id = :reqid");
        					$dropoffdelt->bindParam(':reqid', $this->request_id, PDO::PARAM_INT);
        					$dropoffdelt->execute();
        					
        				    $files1 = glob($this_file_absolute); // get all file names
                            foreach($files1 as $file1){ // iterate files
                                if(is_file($file1))
                                unlink($file1); // delete file
                            }
                            rmdir($filefolderpath);      
        				    }else{
        				        $this_file_absolute1 =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['from_id'].'/'.$this->request_id.'/signed/';
    				            $filefolderpath =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['from_id'].'/'.$this->request_id.'/signed';
    				            
    				            if (file_exists($this_file_absolute1)) {
                			         //var_dump('1111111111111111111111');die();
            					    $files5 = glob($this_file_absolute1.'*'); // get all file names
                                    foreach($files5 as $file5){ // iterate files
                                      if(is_file($file5))
                                        unlink($file5); // delete file
                                    }
            					}
            					rmdir($filefolderpath);
            					
            					$filerelationdelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
                                $filerelationdelt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
                                $filerelationdelt->execute();
                                
                                $this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
                                $this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
                                $this->sql->execute();
        				    }
				            
				        }
				    }else{
				        $this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
    					$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
    					$this->sql->execute();
    					// 	/**
        				// 	 * Use the id and uri information to delete the file.
        				// 	 *
        				// 	 * @see delete_file_from_disk
        				// 	 */
        				delete_file_from_disk(UPLOADED_FILES_FOLDER . $this->file_url);
				    }
				    $this->result = true;
				}else {
					$this->result = false;
				}
				return $this->result;
			}
		}
	}
	function delete_inbox_files($rel_id,$request_id)
	{
	    $this->can_delete		= false;
		$this->result			= '';
		$this->check_level		= array(9,8,0);

		if (isset($rel_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
			    if($request_id!=0){
			        
			        $this->file_id = $rel_id;
    				$this->sql = $this->dbh->prepare("SELECT url, uploader FROM " . TABLE_FILES . " WHERE id = :file_id");
    				$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
    				$this->sql->execute();
    				$this->sql->setFetchMode(PDO::FETCH_ASSOC);
    				while( $this->row = $this->sql->fetch() ) {
    						$this->can_delete	= true;
    						$this->file_url = $this->row['url'];
    				}
    				
    				$clientid = $this->dbh->prepare("SELECT from_id,reqclientid,status FROM tbl_drop_off_request WHERE id = :req_id");
    				$clientid->bindParam(':req_id', $request_id, PDO::PARAM_INT);
    				$clientid->execute();
    				$clientid->setFetchMode(PDO::FETCH_ASSOC);
    				$reqinfo=$clientid->fetch();
    				
        			/** Delete the reference to the file on the database */
    				if ( true === $this->can_delete ) {
    				    
    				// 	/**
    				// 	 * Use the id and uri information to delete the file.
    				// 	 *
    				// 	 * @see delete_file_from_disk
    				// 	 */
    				if($reqinfo['status']==0){
    				    // var_dump('11111111111111111111 : 0');die();
    				    /**--- start ---*/
    				    
    				    
    				    $this_file_absolute =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$request_id.'/*';
    				    $filefolderpath =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$request_id;
    				    
    				    $files1 = glob($this_file_absolute); // get all file names
                        foreach($files1 as $file1){ // iterate files
                        //   var_dump('2222222222222222222222');die();
                          if(is_file($file1))
                            unlink($file1); // delete file
                        }
    				    rmdir($filefolderpath);
    				    
    				    $filerelationdelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
    					$filerelationdelt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
    					$filerelationdelt->execute();
					
					
    					$filedelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
    					$filedelt->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
    					$filedelt->execute();
    					
    					$filepositiondelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_pos_details WHERE tbl_draw_sign_details_id = :request_id");
    					$filepositiondelt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    					$filepositiondelt->execute();
    					
    					$filedrawdelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_details WHERE drop_off_request_id = :drequest_id");
    					$filedrawdelt->bindParam(':drequest_id', $request_id, PDO::PARAM_INT);
    					$filedrawdelt->execute();
    					
    					$dropoffdelt = $this->dbh->prepare("DELETE FROM tbl_drop_off_request WHERE id = :reqid");
    					$dropoffdelt->bindParam(':reqid', $request_id, PDO::PARAM_INT);
    					$dropoffdelt->execute();
    					/**--- End ---*/
    					
    				    
    				}else{
        				$client_id = $this->dbh->prepare("SELECT req_status FROM " . TABLE_FILES . " WHERE id = :file_id");
        				$client_id->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
        				$client_id->execute();
        				$client_id->setFetchMode(PDO::FETCH_ASSOC);
        				$reqinfo11=$client_id->fetch();
    				    if($reqinfo11['req_status']!=0){
    				     
    				        $this_file_absolute =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$request_id.'/*';
        				    $filefolderpath =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$request_id;
        				  
        				    $files1 = glob($this_file_absolute); // get all file names
                            foreach($files1 as $file1){ // iterate files
                            //   var_dump('2222222222222222222222');die();
                              if(is_file($file1))
                                unlink($file1); // delete file
                            }
        				    rmdir($filefolderpath);
        				    
    				        // var_dump('33333333333333333 : 2');die();
    				        /**--- start ---*/
        				    $filerelationdelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
        					$filerelationdelt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
        					$filerelationdelt->execute();
    					
    					
        					$filedelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
        					$filedelt->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
        					$filedelt->execute();
        					
        					$filepositiondelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_pos_details WHERE tbl_draw_sign_details_id = :request_id");
        					$filepositiondelt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        					$filepositiondelt->execute();
        					
        					$filedrawdelt = $this->dbh->prepare("DELETE FROM tbl_draw_sign_details WHERE drop_off_request_id = :drequest_id");
        					$filedrawdelt->bindParam(':drequest_id', $request_id, PDO::PARAM_INT);
        					$filedrawdelt->execute();
        					
        					$dropoffdelt = $this->dbh->prepare("DELETE FROM tbl_drop_off_request WHERE id = :reqid");
        					$dropoffdelt->bindParam(':reqid', $request_id, PDO::PARAM_INT);
        					$dropoffdelt->execute();
        					/**--- End ---*/
        					
    				    }else{
    			
    				        $this_file_absolute1 =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['from_id'].'/'.$request_id.'/signed/';
    				        $filefolderpath =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['from_id'].'/'.$request_id;
    			
                            if (file_exists($this_file_absolute1)) {
            			         //var_dump('1111111111111111111111');die();
        					    $files5 = glob($this_file_absolute1.'*'); // get all file names
                                foreach($files5 as $file5){ // iterate files
                                  if(is_file($file5))
                                    unlink($file5); // delete file
                                }
        					}    			
    				        rmdir($filefolderpath);
    				        
    				        // var_dump('33333333333333333333333333 : 3');die();
    				        /**--- start ---*/
        				    $filerelationdelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
        					$filerelationdelt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
        					$filerelationdelt->execute();
    					
    					
        					$filedelt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
        					$filedelt->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
        					$filedelt->execute();
    					
    					    /**--- End ---*/
    				    }
    				    
    					
    				// 	$this_file_absolute =UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$reqinfo['reqclientid'].'/'.$request_id.'/*';
    					
    				    
    				}
    				 
    			    
    			   
    				 
    				// 	delete_file_from_disk($this_file_absolute);
    					$this->result = true;
    				}
    				else {
    					$this->result = false;
    				}
			        
			    }else{
			       /** Delete the reference to the file on the database */
					$normalfilerelationdlt = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
					$normalfilerelationdlt->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
					$normalfilerelationdlt->execute();
					
					$prev_assign = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
					$prev_assign->bindParam(':file_id', $rel_id, PDO::PARAM_INT);
					$prev_assign->execute();
					$this->result = true; 
			    }
				return $this->result;
			}
		}
	}


	function delete_public_files($rel_id)
	{
		$this->can_delete		= false;
		$this->result			= '';
		$this->check_level		= array(9,8,0);

		if (isset($rel_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->file_id = $rel_id;
				$this->sql = $this->dbh->prepare("SELECT url, uploader FROM " . TABLE_FILES . " WHERE id = :file_id");
				$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
				$this->sql->execute();
				$this->sql->setFetchMode(PDO::FETCH_ASSOC);
				while( $this->row = $this->sql->fetch() ) {
						$this->can_delete	= true;
						$this->file_url = $this->row['url'];
				}

				/** Delete the reference to the file on the database */
				if ( true === $this->can_delete ) {
					$this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES . " WHERE id = :file_id");
					$this->sql->bindParam(':file_id', $this->file_id, PDO::PARAM_INT);
					$this->sql->execute();
					/**
					 * Use the id and uri information to delete the file.
					 *
					 * @see delete_file_from_disk
					 */
					delete_file_from_disk(UPLOADED_FILES_FOLDER . $this->file_url);
					$this->result = true;
				}
				else {
					$this->result = false;
				}
				
				return $this->result;
			}
		}
	}
	
	function change_files_hide_status($file_id,$change_to,$modify_type,$modify_id)
	{
		$this->check_level = array(9,8,7);
		if (isset($file_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hidden=:hidden WHERE file_id = :file_id AND $modify_type = :modify_id");
				$this->sql->bindParam(':hidden', $change_to, PDO::PARAM_INT);
				$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
				$this->sql->bindParam(':modify_id', $modify_id, PDO::PARAM_INT);
				$this->sql->execute();
			}
		}
	}
	function hide_n_show($file_id,$change_to)
	{
		$this->check_level = array(9,8,7);
		if (isset($file_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hidden=:hidden WHERE file_id = :file_id");
				$this->sql->bindParam(':hidden', $change_to, PDO::PARAM_INT);
				$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
				$this->sql->execute();
			}
		}
	}
	function hide_inbox($file_id)
	{
		$this->check_level = array(9,8,7);
		if (isset($file_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hide_inbox ='1' WHERE file_id = :file_id");
				$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
				$this->sql->execute();
			}
		}
	}
	function hide_sent($file_id)
	{
		$this->check_level = array(9,8,7);
		if (isset($file_id)) {
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hide_sent ='1' WHERE file_id = :file_id");
				$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
				$this->sql->execute();
			}
		}
	}
	function show_inbox()
	{
				$fileinfo= $this->dbh->prepare("SELECT file_id from ".TABLE_FILES_RELATIONS." WHERE hide_inbox= '1' AND client_id =".CURRENT_USER_ID);
				$fileinfo->execute();
				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hide_inbox ='0' WHERE  hide_inbox= '1' AND client_id =".CURRENT_USER_ID);
				$this->sql->execute();
				return($fileinfo->fetchAll(PDO::FETCH_ASSOC));
	}
	function show_sent()
	{
				$fileinfo= $this->dbh->prepare("SELECT file_id from ".TABLE_FILES_RELATIONS." WHERE hide_sent= '1' AND from_id =".CURRENT_USER_ID);
				$fileinfo->execute();
				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hide_sent ='0' WHERE hide_sent= '1' AND from_id =".CURRENT_USER_ID);
				$this->sql->execute();
				return($fileinfo->fetchAll(PDO::FETCH_ASSOC));
	}
	function show_all()
	{

				$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hide_sent ='0' WHERE hide_sent= '1' AND from_id =".$_SESSION['loggedin_id']);
				$this->sql->execute();

				$this->sql1 = $this->dbh->prepare("UPDATE " . TABLE_FILES_RELATIONS . " SET hide_inbox ='0' WHERE  hide_inbox= '1' AND client_id =".$_SESSION['loggedin_id']);
				$this->sql1->execute();
	}

	function manage_hide($file_id)
	{
	 			$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES ." SET hidden= '1' WHERE id = :file_id");
	 			$this->sql->bindParam(':file_id', $file_id);
	 			$this->sql->execute();
	}
	function manage_show()
	{
	 			$this->sql = $this->dbh->prepare("UPDATE " . TABLE_FILES ." SET hidden= '0' ");
	 			$this->sql->execute();
	}

	function unassign_file($file_id,$modify_type,$modify_id)
	{
		if (isset($file_id)) {
				$this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS . " WHERE file_id = :file_id AND $modify_type = :modify_id");
				$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
				$this->sql->bindParam(':modify_id', $modify_id, PDO::PARAM_INT);
				$this->sql->execute();

		}
	}
	function unassign($file_id)
	{
		if (isset($file_id)) {
			/** Do a permissions check */
				$check = $this->dbh->prepare("SELECT * FROM " . TABLE_FILES_RELATIONS . " WHERE file_id =".$file_id);
				$check->execute();
				if($check->rowCount() > 1 ){
					$this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS. " WHERE file_id = :file_id AND client_id =".CURRENT_USER_ID);
					$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
					$this->sql->execute();
				 }
				else{
					$this->sql = $this->dbh->prepare("DELETE FROM " . TABLE_FILES_RELATIONS . " WHERE file_id = :file_id");
					$this->sql->bindParam(':file_id', $file_id, PDO::PARAM_INT);
					$this->sql->execute();
				}

				$unassign = $this->dbh->prepare("UPDATE " . TABLE_FILES . " SET prev_assign ='1' WHERE id = :file_id");
				$unassign->bindParam(':file_id', $file_id, PDO::PARAM_INT);
				$unassign->execute();


		}
	}

}

?>