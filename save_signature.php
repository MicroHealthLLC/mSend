
<?php 
require_once('sys.includes.php');
$result = array();
if(isset($_POST['drop_off_request_id'])){
	
$drop_off_request_id=$_POST['drop_off_request_id'];
$stmt = $dbh->prepare("SELECT * FROM tbl_draw_sign_details WHERE drop_off_request_id=:drop_off_request_id");
$stmt->execute(['drop_off_request_id' => $drop_off_request_id]); 
$data = $stmt->fetch();
if($data){
	$stmt1 = $dbh->prepare("SELECT * FROM tbl_drop_off_request WHERE id=:drop_off_request_id");
	$stmt1->execute(['drop_off_request_id' => $drop_off_request_id]); 
	$data1 = $stmt1->fetch();
// 		var_dump($data1);die();
	$fname = $data['img_name'];
    $no_of_pages = $data['no_of_pages'];
    $to_email_request = $data1['to_email'];
    $to_subject_request = $data1['to_subject_request'];
    $to_name = $data1['to_name'];
    $reqclientid = $data1['reqclientid'];

	
	$filename = pathinfo($fname, PATHINFO_FILENAME).".pdf";
// 	$filename = md5(date("dmYhisA")).".pdf";
	//Location to where you want to created sign image
	$targetsignature_dir = UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$data1['from_id'].'/';

	if (!file_exists($targetsignature_dir)) {
		mkdir($targetsignature_dir, 0777, true);
	}
	if (!file_exists($targetsignature_dir.$drop_off_request_id)) {
	 mkdir($targetsignature_dir.$drop_off_request_id, 0777, true);
	}
	if (!file_exists($targetsignature_dir.$drop_off_request_id.'/signed/')) {
		mkdir($targetsignature_dir.$drop_off_request_id.'/signed/', 0777, true);
	}
	if (!file_exists($targetsignature_dir.$drop_off_request_id.'/signed/temp/')) {
		mkdir($targetsignature_dir.$drop_off_request_id.'/signed/temp/', 0777, true);
	}
	$file_name1 = UPLOADED_FILES_FOLDER.'../../upload/files/mysignature/'.$data1['from_id'].'/'.$drop_off_request_id.'/signed/';
//-----------------------------------------------
 if(move_uploaded_file($_FILES['pdf']['tmp_name'], $file_name1.$filename)){
//  if(move_uploaded_file($_FILES['pdf']['tmp_name'], $file_name1.$drop_off_request_id.".pdf")){


    	$aes = new AESENCRYPT ();					
		$result  = $aes->reqencryptFile($filename,$data1['from_id'],$drop_off_request_id);
		
// 		$result1  = $aes->reqdecryptFile($filename,$data1['from_id'],$drop_off_request_id);

	$result['status'] = true;
	$result['file_name'] = $file_name1;
	//$result['file_name'] = '/signatures/'.$filename;
	
    // $url = $fname;
    $url = $filename;
    $fromid = $data1['from_id'];
    $dropoffrequestid = $drop_off_request_id;
    // var_dump($dropoffrequestid);die();
    $filenamearray = explode(".",$url);
    $filename = $filenamearray[0];       $array_file_name[] = $filenamearray[0];
    $public_allow = 0;
    $uploader = $_SESSION['loggedin'];
    $time = '202020-07-07 00:00:00';
    $expdate = '2020-07-07 00:00:00';
  
    $statement = $dbh->prepare("INSERT INTO ".TABLE_FILES." (`url`, `filename`, `description`, `timestamp`, `uploader`, `expires`, `expiry_date`, `future_send_date`, `public_allow`, `public_token`,`request_type`,`tbl_drop_off_request_id`) VALUES ('$url', '$filename', '', CURRENT_TIMESTAMP, '$uploader', '0', '2017-12-09 00:00:00',  '".date('Y-m-d 00:00:00')."','0', NULL,'1','$dropoffrequestid');");

    if($statement->execute()) {
        $img_id = $dbh->lastInsertId();
        $stmt9 = $dbh->prepare("SELECT user FROM tbl_users WHERE id = ".$fromid);
        $stmt9->execute(); 
        $data9 = $stmt9->fetch();
        // var_dump($data9);die();
        $new_log_fileaction = new LogActions();		    
		$log_action_arguments = array(
			'action' => 5,
			'owner_id' => $fromid,
			'owner_user' => $_SESSION['loggedin'],
			'affected_file' =>$img_id,
// 			'affected_account' =>$reqclientid,
			'affected_file_name' => $filename,
			'affected_account_name' => $data9['user'],
			'file_type' => 'signature request file'
			
	    );		
		$sig_fileactive = $new_log_fileaction->log_action_save($log_action_arguments);
        if($sig_fileactive){
	        $sql55 = $dbh->prepare("UPDATE " . TABLE_LOG . " SET `file_type` = 'signature request file' WHERE id = ". $sig_fileactive);
	        $sql55->execute();
		}
        
        
        $new_log_action = new LogActions();		    
		$log_action_args = array(
			'action' => 25,
			'owner_id' => $fromid,
			'owner_user' => $_SESSION['loggedin'],
			'affected_file' =>$img_id,
			'affected_account' =>$reqclientid,
			'affected_file_name' => $filename,
			'affected_account_name' => $data9['user'],
			'file_type' => 'signature request file'
			
	    );		
		$sig_active = $new_log_action->log_action_save($log_action_args);
		if($sig_active){
	        $sql55 = $dbh->prepare("UPDATE " . TABLE_LOG . " SET `file_type` = 'signature request file' WHERE id = ". $sig_active);
	        $sql55->execute();
		}
        
        // $filesrelations = $dbh->prepare("INSERT INTO ".TABLE_FILES_RELATIONS." (`timestamp`, `file_id`, `client_id`, `from_id`, `group_id`, `folder_id`, `hidden`, `download_count`,`req_type`) VALUES (CURRENT_TIMESTAMP, ".$img_id.", ".$fromid.", ".$fromid." ,NULL, NULL, '0', '0', '1')");
        $filesrelations = $dbh->prepare("INSERT INTO ".TABLE_FILES_RELATIONS." (`timestamp`, `file_id`, `client_id`, `from_id`, `group_id`, `folder_id`, `hidden`, `download_count`,`req_type`) VALUES (CURRENT_TIMESTAMP, ".$img_id.", ".$fromid.", ".$reqclientid." ,NULL, NULL, '0', '0', '1')");
        // $filesrelations = $dbh->prepare("INSERT INTO ".TABLE_FILES_RELATIONS." (`timestamp`, `file_id`, `client_id`, `group_id`, `folder_id`, `hidden`, `download_count`,`req_type`) VALUES (CURRENT_TIMESTAMP, ".$img_id.", ".$fromid.", NULL, NULL, '0', '0', '1')");
        

        $sql = $dbh->prepare( 'SELECT * FROM '.TABLE_USERS.' WHERE id = "'.$fromid.'"' );	
    	$sql->execute();
    	$sql->setFetchMode(PDO::FETCH_ASSOC);
    	$grow = $sql->fetch();
    
    	$stmt = $dbh->prepare("UPDATE ".TABLE_DROPOFF." SET status=1 WHERE id=$drop_off_request_id");	$stmt->execute();
    	if($grow) {
    	    $from_email = $grow['email'];
    	}
        if($filesrelations->execute()) {
            

        		$to_subject_request = ":: Requested file is uploaded by ".$to_email_request;
        					$message ="<html>
        	  <head>
        		<meta name='viewport' content='width=device-width'>
        		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
        		<title>Simple Transactional Email</title>
        		<style type='text/css'>
        		/* -------------------------------------
        			INLINED WITH https://putsmail.com/inliner
        		------------------------------------- */
        		/* -------------------------------------
        			RESPONSIVE AND MOBILE FRIENDLY STYLES
        		------------------------------------- */
        		@media only screen and (max-width: 620px) {
        		  table[class=body] h1 {
        			font-size: 28px !important;
        			margin-bottom: 10px !important; }
        		  table[class=body] p,
        		  table[class=body] ul,
        		  table[class=body] ol,
        		  table[class=body] td,
        		  table[class=body] span,
        		  table[class=body] a {
        			font-size: 16px !important; }
        		  table[class=body] .wrapper,
        		  table[class=body] .article {
        			padding: 10px !important; }
        		  table[class=body] .content {
        			padding: 0 !important; }
        		  table[class=body] .container {
        			padding: 0 !important;
        			width: 100% !important; }
        		  table[class=body] .main {
        			border-left-width: 0 !important;
        			border-radius: 0 !important;
        			border-right-width: 0 !important; }
        		  table[class=body] .btn table {
        			width: 100% !important; }
        		  table[class=body] .btn a {
        			width: 100% !important; }
        		  table[class=body] .img-responsive {
        			height: auto !important;
        			max-width: 100% !important;
        			width: auto !important; }}
        		/* -------------------------------------
        			PRESERVE THESE STYLES IN THE HEAD
        		------------------------------------- */
        		@media all {
        		  .ExternalClass {
        			width: 100%; }
        		  .ExternalClass,
        		  .ExternalClass p,
        		  .ExternalClass span,
        		  .ExternalClass font,
        		  .ExternalClass td,
        		  .ExternalClass div {
        			line-height: 100%; }
        		  .apple-link a {
        			color: inherit !important;
        			font-family: inherit !important;
        			font-size: inherit !important;
        			font-weight: inherit !important;
        			line-height: inherit !important;
        			text-decoration: none !important; }
        		  .btn-primary table td:hover {
        			background-color: #34495e !important; }
        		  .btn-primary a:hover {
        			background-color: #34495e !important;
        			border-color: #34495e !important; } }
        		</style>
        		<head>
        			  <title>$to_subject_request</title>
        		</head>
        	  </head>
        	  
        	  <body class='' style='background-color:#f6f6f6;font-family:sans-serif;-webkit-font-smoothing:antialiased;font-size:14px;line-height:1.4;margin:0;padding:0;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;'>
        		<table width='550' border='0' cellspacing='0' cellpadding='0' style='background:#fff;border:1px solid #ccc;border-radius:5px' bgcolor='#FFFFFF' align='center'>
	<tbody>
		<tr>
			<td style='padding:20px;font-family:Arial,Helvetica,sans-serif;font-size:12px'>
				<h3 style='font-family:Arial,Helvetica,sans-serif;font-size:19px;font-weight:normal;margin-bottom:20px;margin-top:0;color:#333333'>
					<font face='Arial, Helvetica, sans-serif' color='#333333'>
						New files uploaded for you
					</font>
				</h3><p>The following files are now available for you to download.</p>
				<div style='padding:15px 0;border:solid #ddd;border-width:1px 0;margin:0 0 20px'>
					<ul style='list-style:none;margin:0;padding:0'>
						<li style='margin-bottom:11px'><p style='font-weight:bold;margin:0 0 5px 0;font-size:14px'>$filename</p></li>
					</ul>
				</div>
				<p>If you prefer not to be notified about new files, please go to My Account and deactivate the notifications checkbox.</p>
				<p>You can access a list of all your files or upload your own  <a href='".BASE_URI."' target='_blank'>by logging in here</a></p>
				
				
			</td>
		</tr>
	</tbody>
</table>
        	  </body>
        	</html>";
        	
        	
        // 	<a href='".BASE_URI."sign_document.php?auth=".$keypath."&key=sign' target='_blank'
        
        		/**
        		 * phpMailer
        		 */
        		require_once(ROOT_DIR.'/includes/phpmailer/class.phpmailer.php');
        		if (!spl_autoload_functions() OR (!in_array('PHPMailerAutoload', spl_autoload_functions()))) {
        			require_once(ROOT_DIR.'/includes/phpmailer/PHPMailerAutoload.php');
        		}
        
        		$send_mail = new PHPMailer();
        		switch (MAIL_SYSTEM) {
        			case 'smtp':
        					$send_mail->IsSMTP();
        					$send_mail->SMTPAuth = true;
        					$send_mail->Host = SMTP_HOST;
        					$send_mail->Port = SMTP_PORT;
        					$send_mail->Username = SMTP_USER;
        					$send_mail->Password = SMTP_PASS;
        					
        					if ( defined('SMTP_AUTH') && SMTP_AUTH != 'none' ) {
        						$send_mail->SMTPSecure = SMTP_AUTH;
        					}
        				break;
        			case 'gmail':
        					$send_mail->IsSMTP();
        					$send_mail->SMTPAuth = true;
        					$send_mail->SMTPSecure = "tls";
        					$send_mail->Host = 'smtp.gmail.com';
        					$send_mail->Port = 587;
        					$send_mail->Username = SMTP_USER;
        					$send_mail->Password = SMTP_PASS;
        				break;
        			case 'sendmail':
        					$send_mail->IsSendmail();
        				break;
        		}
        		
        		$send_mail->CharSet = EMAIL_ENCODING;
        //
        		$send_mail->Subject = $to_subject_request;
        //
        		$send_mail->MsgHTML($message);
        		$send_mail->AltBody = __('This email contains HTML formatting and cannot be displayed right now. Please use an HTML compatible reader.','cftp_admin');
        
        		$send_mail->SetFrom($to_email_request, $to_name);
        		$send_mail->AddReplyTo($to_email_request, $to_name);
        //
        		$send_mail->AddAddress($from_email);
  //--------------------------------------------------------------------------      		
        		/**
        		 * Finally, send the e-mail.
        		 */
        		 
        // 		if($send_mail->Send()) {
					
        // 			$cc_status1 = "<div class=\"alert alert-success cc-success\"><strong>Success!</strong>Your Request has been submitted successfully.</div>";
        // 		}
        // 		else {
        // 			$cc_status1 = "<div class=\"alert alert-danger cc-failed\"><strong>Oops! </strong>Something went wrong! please try after sometime.</div>";
        // 		}
        		 
        		 
        		 
        		 
        		if($send_mail->Send()) {
					
        			$cc_status = "<div class=\"alert alert-success cc-success\"><strong>Success!</strong>Your Request has been submitted successfully.</div>";
        		}
        		else {
        			$cc_status = "<div class=\"alert alert-danger cc-failed\"><strong>Oops! </strong>Something went wrong! please try after sometime.</div>";
        		}
        	
 //------------------------------------------------------------------------       		
        		/**
        		 * Check if BCC is enabled and get the list of
        		 * addresses to add, based on the email type.
        		 */
        		if (COPY_MAIL_ON_CLIENT_UPLOADS == '1') {
        					$try_bcc = true;
        				}
        		if ($try_bcc === true) {
        			$add_bcc_to = array();
        			if (COPY_MAIL_MAIN_USER == '1') {
        				$add_bcc_to[] = ADMIN_EMAIL_ADDRESS;
        			}
        			$more_addresses = COPY_MAIL_ADDRESSES;
        			if (!empty($more_addresses)) {
        				$more_addresses = explode(',',$more_addresses);
        				foreach ($more_addresses as $add_bcc) {
        					$add_bcc_to[] = $add_bcc;
        				}
        			}
        
        
        			/**
        			 * Add the BCCs with the compiled array.
        			 * First, clean the array to make sure the admin
        			 * address is not written twice.
        			 */
        
        			if (!empty($add_bcc_to)) {
        				$add_bcc_to = array_unique($add_bcc_to);
        				foreach ($add_bcc_to as $set_bcc) {
        					$send_mail->AddBCC($set_bcc);
        				}
        			}
        			 
        		}
        
        
        	
        		/**
        		 * Finally, send the e-mail.
        		 */
        // 		if($send_mail->Send()) {
					
        // 			$cc_status = "<div class=\"alert alert-success cc-success\"><strong>Success!</strong>Your Request has been submitted successfully.</div>";
        // 		}
        // 		else {
        // 			$cc_status = "<div class=\"alert alert-danger cc-failed\"><strong>Oops! </strong>Something went wrong! please try after sometime.</div>";
        // 		}
        	

        }
    }
            
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	echo 1;
	exit;
 } else {
	 echo 0;
	 exit;
 }
//-----------------------------------------------

} else{
	echo 0;
	exit;
	//$result['status'] = false;
}
	
}
else {
	echo 0;
	exit;
	//echo 'drop off request id not found';
	
}


