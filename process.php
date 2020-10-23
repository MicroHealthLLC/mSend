<?php


/**



 * Class that handles the log out and file download actions.



 *



 * @package		ProjectSend



 */



$allowed_levels = array(9,8,7,0);

require_once('sys.includes.php');



require_once('header.php');



ini_set("memory_limit","-1");



class process {



	function __construct() {



		global $dbh;



		$this->dbh = $dbh;



		$this->process();



	}







	function process() {


		switch ($_GET['do']) {



			case 'download':



				$this->download_file();



				break;



			case 'zip_download':



				$this->download_zip();



				break;



			case 'get_downloaders':



				$this->get_downloaders();



				break;



			case 'req_download':



				$this->reqdownload_file();



				break;
				
            case 'logout':



				$this->logout();



				break;


			default:



				header('Location: '.BASE_URI);



				break;



		}



	}







	function download_file() {



		$this->check_level = array(9,8,7,0);



		if (isset($_GET['id']) && isset($_GET['client']) && ($_GET['client']== $_SESSION['loggedin'])) {



			/** Do a permissions check for logged in user */



			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {







					/**



					 * Get the file name



					 */



					$this->statement = $this->dbh->prepare("SELECT url, expires, expiry_date, uploader, number_downloads  FROM " . TABLE_FILES . " WHERE id=:id");



					$this->statement->bindParam(':id', $_GET['id'], PDO::PARAM_INT);



					$this->statement->execute();



					$this->statement->setFetchMode(PDO::FETCH_ASSOC);



					$this->row = $this->statement->fetch();



					$this->real_file_url	= $this->row['url'];



					$this->expires			= $this->row['expires'];



					$this->expiry_date		= $this->row['expiry_date'];



					$this->uploader		= $this->row['uploader'];



					//var_dump($this->row);exit;



					$this->expired			= false;



					if ($this->expires == '1' && time() > strtotime($this->expiry_date)) {



						$this->expired		= true;



					}



					$this->can_download = true;



					$curr_usr_nm = CURRENT_USER_USERNAME;



					if ((CURRENT_USER_LEVEL == 0 ) && ($curr_usr_nm != $this->row['uploader'])) {
					    
					    //var_dump('11111111111111111111');


						if ($this->expires == '0' || $this->expired == false) {



							/**



							 * Does the client have permission to download the file?



							 * First, get the list of different groups the client belongs to.



							 */



							$this->groups = $this->dbh->prepare("SELECT DISTINCT group_id FROM " . TABLE_MEMBERS . " WHERE client_id=:id");



							$this->groups->bindValue(':id', CURRENT_USER_ID, PDO::PARAM_INT);



							$this->groups->execute();







							if ( $this->groups->rowCount() > 0 ) {



								$this->groups->setFetchMode(PDO::FETCH_ASSOC);



								while ( $this->row_groups = $this->groups->fetch() ) {



									$this->groups_ids[] = $this->row_groups["group_id"];



								}



								if ( !empty( $this->groups_ids ) ) {



									$this->found_groups = implode( ',', $this->groups_ids );



								}



							}











							$this->params = array(



												':client_id'	=> CURRENT_USER_ID,



											);



							$this->fq = "SELECT * FROM " . TABLE_FILES_RELATIONS . " WHERE (client_id=:client_id";



							// Add found groups, if any



							if ( !empty( $this->found_groups ) ) {



								$this->fq .= ' OR FIND_IN_SET(group_id, :groups)';



								$this->params[':groups'] = $this->found_groups;



							}



							// Continue assembling the query



							$this->fq .= ') AND file_id=:file_id';



							$this->params[':file_id'] = (int)$_GET['id'];







							$this->files = $this->dbh->prepare( $this->fq );



							$this->files->execute( $this->params );











							if ( $this->files->rowCount() > 0 ) {



								$this->can_download = true;



							}



					//to check whether download limit is exceeded--------------------------------------



					$this->download_statement = $this->dbh->prepare("SELECT COUNT(*) as download_count FROM " . TABLE_DOWNLOADS . " WHERE file_id=:id AND user_id=".CURRENT_USER_ID);



					$this->download_statement->bindParam(':id', $_GET['id'], PDO::PARAM_INT);



					$this->download_statement->execute();



					$this->download_statement->setFetchMode(PDO::FETCH_ASSOC);



					$this->download_count = $this->download_statement->fetch();



					//echo $this->download_count['download_count'];



					if($this->row['number_downloads'] != 0) {



						if($this->download_count['download_count'] >= $this->row['number_downloads']) {



							$this->can_download = false;



						}



					}



							/** Continue */



							if ($this->can_download == true) {



								/**



								 * If the file is being downloaded by a client, add +1 to



								 * the download count



								 */



								$this->statement = $this->dbh->prepare("INSERT INTO " . TABLE_DOWNLOADS . " (user_id , file_id, remote_ip, remote_host) VALUES (:user_id, :file_id, :remote_ip, :remote_host)");



								$this->statement->bindValue(':user_id', CURRENT_USER_ID, PDO::PARAM_INT);



								$this->statement->bindParam(':file_id', $_GET['id'], PDO::PARAM_INT);



								$this->statement->bindParam(':remote_ip', $_SERVER['REMOTE_ADDR']);



								$this->statement->bindParam(':remote_host', $_SERVER['REMOTE_HOST']);



								$this->statement->execute();







								/**



								 * The owner ID is generated here to prevent false results



								 * from a modified GET url.



								 */



								$log_action = 8;



								$log_action_owner_id = CURRENT_USER_ID;



							}



						}







					}



					else {

						$this->can_download = true;



						$log_action = 7;



						$global_user = get_current_user_username();



						$global_id = get_logged_account_id($global_user);



						$log_action_owner_id = $global_id;



					}







					if ($this->can_download == true) {



						/** Record the action log */



						$new_log_action = new LogActions();







						$log_action_args = array(



												'action'				=> $log_action,



												'owner_id'				=> $log_action_owner_id,



												'affected_file'			=> (int)$_GET['id'],



												'affected_file_name'	=> $this->real_file_url,



												'affected_account'		=> (int)$_GET['client_id'],



												'affected_account_name'	=> $_GET['client'],



												'get_user_real_name'	=> true,



												'get_file_real_name'	=> true



											);



						$new_record_action = $new_log_action->log_action_save($log_action_args);



						$this->real_file = UPLOADED_FILES_FOLDER.$this->real_file_url;



						$filePath = $this->real_file;



						$handle = @fopen($filePath, "r");



						if ($handle) {



							$ext = pathinfo($filePath, PATHINFO_EXTENSION);



							if($ext !='zip'){



								 $aes = new AESENCRYPT();



								 $decfile=$aes->decryptFile($this->real_file_url);



								 $real_file1 = UPLOADED_FILES_FOLDER."temp/".$this->real_file_url;

							 } else {



								 $path = UPLOADED_FILES_FOLDER.$this->real_file_url;



									$zip = new ZipArchive;



									$unzipped = array();



									if ($zip->open($path) === true) {



									    for($i = 0; $i < $zip->numFiles; $i++) {



									        $filename = $zip->getNameIndex($i);



									        $fileinfo = pathinfo($filename);



									        copy("zip://".$path."#".$filename, UPLOADED_FILES_FOLDER.'temp/'.$fileinfo['basename']);



													$unzipped[]= $fileinfo['basename'];



									    }



									    $zip->close();



									}



									if(!empty($unzipped)){



										/* REMOVED THE '_' BEFORE .ZIP*/



										$updatedfilename=str_replace("_.zip",".zip",$this->real_file_url);



										



										$zip = new ZipArchive();



										$zipFilePath = UPLOADED_FILES_FOLDER.'temp/'.$updatedfilename;



										$r = $zip->open($zipFilePath,  ZipArchive::CREATE);



										if(!file_exists(UPLOADED_FILES_FOLDER.'temp/zip')){



											mkdir(UPLOADED_FILES_FOLDER.'temp/zip');



										}



										//Decrypting invidual zip entries



										foreach ($unzipped as $unzip) {



											$aes = new AESENCRYPT();



											$aes->decryptZipFile($unzip);



											$zip->addFile(UPLOADED_FILES_FOLDER.'temp/zip/'.$unzip,$unzip);



										}



										$r=$zip->close();



										//Deleting all encrypted zip entries extracted to temp



										foreach ($unzipped as $unzip) {



											unlink(UPLOADED_FILES_FOLDER.'temp/'.$unzip);



										}



									}



									$real_file1 = $zipFilePath;


							 }

                     

						if (file_exists($real_file1)) {
						    
						    if(end(explode('/',$real_file1))==''){
						        header("location:" . BASE_URI . "inbox.php?status=1");
						    }
							session_write_close();
                            global $dbh;
                        	$f_id = $_GET['id'];
                        	$sql6 = $dbh->prepare("UPDATE " . TABLE_FILES . " SET `unread_flag` = '1' WHERE id = ". $f_id);
                        	$sql6->execute();


							while (ob_get_level()) ob_end_clean();



							header('Content-Type: application/octet-stream');



							header('Content-Disposition: attachment; filename='.basename($real_file1));



							header('Expires: 0');



							header('Cache-Control: must-revalidate, post-check=0, pre-check=0');



							header('Pragma: public');



							header('Cache-Control: private',false);



							header('Content-Length: ' . get_real_size($real_file1));



							header('Connection: close');



							//readfile($this->real_file);







							$context = stream_context_create();



							$file = fopen($real_file1, 'rb', FALSE, $context);



							while( !feof( $file ) ) {



								//usleep(1000000); //Reduce download speed



								echo stream_get_contents($file, 2014);



							}



							fclose( $file );



							unlink($real_file1);



							//Deleting all decrypted zip entries extracted in temp/zip folder



							$zentries = glob(UPLOADED_FILES_FOLDER.'temp/zip/*'); // get all file names



								foreach($zentries as $zentry){ // iterate files



								  if(is_file($zentry))



								    unlink($zentry); // delete file



								}
								
							exit;



						}

// var_dump($real_file1. '444');die();






						}







						else {



							header("HTTP/1.1 404 Not Found");



							?>



								<div id="main" role="main">



                                  <!-- MAIN CONTENT -->



                                  <div id="content">







                                    <!-- Added by B) -------------------->



                                    <div class="container-fluid">



                                      <div class="row">



                                        <div class="col-md-12">



										<h2><?php _e('File not found','cftp_admin'); ?></h2>



									</div>



								</div>



                                </div>



                                </div>



                                </div>



								<?php



                                header('Location:'.SITE_URI.'inbox.php?status=1');



						}



					}







			}



		}



		else {



			header('Location:'.SITE_URI.'access-denied.php');



		}



	}







	function download_zip() {



		$this->check_level = array(9,8,7,0);



		if (isset($_GET['files']) && isset($_GET['client'])) {



			// do a permissions check for logged in user



			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {



				$file_list = array();



				$requested_files = $_GET['files'];



				foreach($requested_files as $file_id) {



					echo $file_id;



					$this->statement = $this->dbh->prepare("SELECT url FROM " . TABLE_FILES . " WHERE id=:file_id");



					$this->statement->bindParam(':file_id', $file_id, PDO::PARAM_INT);



					$this->statement->execute();



					$this->statement->setFetchMode(PDO::FETCH_ASSOC);



					$this->row = $this->statement->fetch();



					$this->url = $this->row['url'];



					$file = UPLOADED_FILES_FOLDER.$this->url;



					if (file_exists($file)) {



						$file_list[] = $this->url;



					}



				}



				ob_clean();



				flush();



				echo implode( ',', $file_list );



			}



		}



	}







	function get_downloaders() {



		$this->check_level = array(9,8,7);



		if (isset($_GET['sys_user']) && isset($_GET['file_id'])) {



			// do a permissions check for logged in user



			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {



				$file_id = (int)$_GET['file_id'];



				$current_level = get_current_user_level();



				$this->statement = $this->dbh->prepare("SELECT id, uploader, filename FROM " . TABLE_FILES . " WHERE id=:file_id");



				$this->statement->bindParam(':file_id', $file_id, PDO::PARAM_INT);



				$this->statement->execute();



				$this->statement->setFetchMode(PDO::FETCH_ASSOC);



				$this->row = $this->statement->fetch();



				$this->uploader = $this->row['uploader'];







				/** Uploaders can only generate this for their own files */



				if ($current_level == '7') {



					if ($this->uploader != $_GET['sys_user']) {



						ob_clean();



						flush();



						_e("You don't have the required permissions to view the requested information about this file.",'cftp_admin');



						exit;



					}



				}







				$this->filename = $this->row['filename'];















				$this->sql_who = $this->dbh->prepare("SELECT user_id, COUNT(*) AS downloads FROM " . TABLE_DOWNLOADS . " WHERE file_id=:file_id GROUP BY user_id");



				$this->sql_who->bindParam(':file_id', $file_id, PDO::PARAM_INT);



				$this->sql_who->execute();



				$this->sql_who->setFetchMode(PDO::FETCH_ASSOC);



				while ( $this->wrow = $this->sql_who->fetch() ) {



					$this->downloaders_ids[] = $this->wrow['user_id'];



					$this->downloaders_count[$this->wrow['user_id']] = $this->wrow['downloads'];



				}







				$this->users_ids = implode(',',array_unique(array_filter($this->downloaders_ids)));







				$this->downloaders_list = array();















				$this->sql_who = $this->dbh->prepare("SELECT id, name, email, level FROM " . TABLE_USERS . " WHERE FIND_IN_SET(id,:users)");



				$this->sql_who->bindParam(':users', $this->users_ids);



				$this->sql_who->execute();



				$this->sql_who->setFetchMode(PDO::FETCH_ASSOC);







				$i = 0;



				while ( $this->urow = $this->sql_who->fetch() ) {



					$this->downloaders_list[$i] = array(



														'name' => $this->urow['name'],



														'email' => $this->urow['email']



													);



					$this->downloaders_list[$i]['type'] = ($this->urow['name'] == 0) ? 'client' : 'user';



					$this->downloaders_list[$i]['count'] = isset($this->downloaders_count[$this->urow['id']]) ? $this->downloaders_count[$this->urow['id']] : null;



					$i++;



				}







				ob_clean();



				flush();



				echo json_encode($this->downloaders_list);



			}



		}



	}







	function logout() {



		header("Cache-control: private");



		unset($_SESSION['loggedin']);



		unset($_SESSION['access']);



		unset($_SESSION['userlevel']);



		unset($_SESSION['lang']);



		session_destroy();







		/** If there is a cookie, unset it */



		setcookie("loggedin","",time()-COOKIE_EXP_TIME);



		setcookie("password","",time()-COOKIE_EXP_TIME);



		setcookie("access","",time()-COOKIE_EXP_TIME);



		setcookie("userlevel","",time()-COOKIE_EXP_TIME);







		/*



		$language_cookie = 'projectsend_language';



		setcookie ($language_cookie, "", 1);



		setcookie ($language_cookie, false);



		unset($_COOKIE[$language_cookie]);



		*/







		/** Record the action log */



		$new_log_action = new LogActions();



		$log_action_args = array(



								'action'	=> 31,



								'owner_id'	=> CURRENT_USER_ID,



								'affected_account_name' => $global_name



							);

		$new_record_action = $new_log_action->log_action_save($log_action_args);







		header("location:index.php");



	}
	
	
	function reqdownload_file() {
	   // var_dump($_GET['completed']);die();
    	$this->download_statement = $this->dbh->prepare("SELECT * FROM tbl_files WHERE id=:req_id");
		$this->download_statement->bindParam(':req_id', $_GET['id'], PDO::PARAM_INT);
		$this->download_statement->execute();
		$this->download_statement->setFetchMode(PDO::FETCH_ASSOC);
		$this->download_filedata = $this->download_statement->fetch();
		if($this->download_filedata){
		    	$this->download_statement1 = $this->dbh->prepare("SELECT * FROM ".TABLE_DROPOFF." WHERE id=:request_id");
        		$this->download_statement1->bindParam(':request_id', $this->download_filedata['tbl_drop_off_request_id'], PDO::PARAM_INT);
        		$this->download_statement1->execute();
        		$this->download_statement1->setFetchMode(PDO::FETCH_ASSOC);
        		$this->download_filedata1 = $this->download_statement1->fetch();
		}
		
		if(isset($_GET['completed'])){
		    if($_GET['req_status']=='1'){
		        $this->real_file = UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$this->download_filedata1["reqclientid"].'/'.$this->download_filedata['tbl_drop_off_request_id'].'/'.$this->download_filedata['filename'].'.pdf';

                if (file_exists($this->real_file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename='.basename($this->real_file));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($this->real_file));
                    ob_clean();
                    flush();
                    readfile($this->real_file);
                    exit;
                }
		    }else{
		        $this->real_file = UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$this->download_filedata1["from_id"].'/'.$this->download_filedata['tbl_drop_off_request_id'].'/'.$this->download_filedata['filename'].'.pdf';
		    }
		}else{
		    $this->real_file = UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$this->download_filedata1["from_id"].'/'.$this->download_filedata['tbl_drop_off_request_id'].'/signed/'.$this->download_filedata['filename'].'.pdf';
            if (!file_exists($this->real_file)) {
                header("location:" . BASE_URI . "inbox.php?status=1");
            }
		}
		
		

	
		$filePath = $this->real_file;
		$handle = @fopen($filePath, "r");
	    
		if ($handle) {
			$ext = pathinfo($filePath, PATHINFO_EXTENSION);
			
			if($ext !='zip'){
				 $aes = new AESENCRYPT();
				if(isset($_GET['completed'])){
				    if($_GET['req_status']=='1'){
				        $real_file1 =  $aes->reqdecryptFile($this->download_filedata['filename'].'.pdf',$this->download_filedata1['reqclientid'],$this->download_filedata['tbl_drop_off_request_id'],$_GET['completed']);
				        
				        // $real_file1 = 'upload/files/mysignature/'.$this->download_filedata1['reqclientid'].'/'.$this->download_filedata['tbl_drop_off_request_id'].'/'.$this->download_filedata['filename'].'.pdf';
				        
				    }else{
				      
				        $real_file1 =  $aes->reqdecryptFile($this->download_filedata['filename'].'.pdf',$this->download_filedata1["from_id"],$this->download_filedata['tbl_drop_off_request_id'],$_GET['completed']);
				    }
				}else{
				  
				    $real_file1 =  $aes->reqdecryptFile($this->download_filedata['filename'].'.pdf',$this->download_filedata1["from_id"],$this->download_filedata['tbl_drop_off_request_id'],'');
				}
			 }
			 //var_dump($real_file1);die();

			if (file_exists($real_file1)) {
			    
			    
			      $this->download_statement5 = $this->dbh->prepare("SELECT COUNT(*) as download_count FROM " . TABLE_DOWNLOADS . " WHERE file_id=:id AND user_id=".CURRENT_USER_ID);
                $this->download_statement5->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
                $this->download_statement5->execute();
                $this->download_statement5->setFetchMode(PDO::FETCH_ASSOC);
                $this->download_count5 = $this->download_statement5->fetch();
                
                $this->can_download5 = true;
                
                // if($this->row['number_downloads'] != 0) {
                //     if($this->download_count5['download_count'] >= $this->row['number_downloads']) {
                // //         $this->can_download5 = false;
                //     }
                // }
                
               
                
                /** Continue */
                if ($this->can_download5 == true) {
                    /**
                     * If the file is being downloaded by a client, add +1 to
                     * the download count
                     */
                    $this->statement = $this->dbh->prepare("INSERT INTO " . TABLE_DOWNLOADS . " (user_id , file_id, remote_ip, remote_host) VALUES (:user_id, :file_id, :remote_ip, :remote_host)");
                    $this->statement->bindValue(':user_id', CURRENT_USER_ID, PDO::PARAM_INT);
                    $this->statement->bindParam(':file_id', $_GET['id'], PDO::PARAM_INT);
                    $this->statement->bindParam(':remote_ip', $_SERVER['REMOTE_ADDR']);
                    $this->statement->bindParam(':remote_host', $_SERVER['REMOTE_HOST']);
                    $this->statement->execute();
                }
			    
				session_write_close();
				global $dbh;
            	$f_id = $_GET['id'];
            	$sql6 = $dbh->prepare("UPDATE " . TABLE_FILES . " SET `unread_flag` = '1' WHERE id = ". $f_id);
            	$sql6->execute();
				while (ob_get_level()) ob_end_clean();
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($real_file1));
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Cache-Control: private',false);
				header('Content-Length: ' . get_real_size($real_file1));
				header('Connection: close');
				$context = stream_context_create();
				$file = fopen($real_file1, 'rb', FALSE, $context);
				while( !feof( $file ) ) {
					echo stream_get_contents($file, 2014);
				}
				fclose( $file );
				unlink($real_file1);
				$zentries = glob(UPLOADED_FILES_FOLDER.'temp/zip/*'); // get all file names



					foreach($zentries as $zentry){ // iterate files
					  if(is_file($zentry))
					    unlink($zentry); // delete file
					}
				exit;
			}
		}
// else {
			//header("HTTP/1.1 404 Not Found");
			?>
				<!--<div id="main" role="main">-->
                  <!-- MAIN CONTENT -->
    <!--              <div id="content">-->
                    <!-- Added by B) -------------------->
    <!--                <div class="container-fluid">-->
    <!--                  <div class="row">-->
    <!--                    <div class="col-md-12">-->
				<!--		<h2><?php //_e('File not found','cftp_admin'); ?></h2>-->
				<!--	</div>-->
				<!--</div>-->
    <!--            </div>-->
    <!--            </div>-->
    <!--            </div>-->
				<?php
                //header('Location:'.SITE_URI.'inbox.php?status=1');
	//	}
	}



}







$process = new process;



?>



