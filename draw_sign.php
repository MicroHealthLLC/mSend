<?php 


require_once('sys.includes.php');
$this_user = CURRENT_USER_USERNAME;
$this_current_id = CURRENT_USER_ID;
$client_info = get_client_by_username($this_user);
$logged_in_email = isset($client_info['email'])?$client_info['email']:'';
$logged_in_name = isset($client_info['name'])?$client_info['name']:'';

//echo "<pre>";print_r($_POST);echo "</pre>";exit;

        if(!empty($_POST['pname'])){$pname = $_POST['pname'];}
        if(!empty($_POST['drop_off_request_id'])){$drop_off_request_id = $_POST['drop_off_request_id'];}
        if(!empty($_POST['image_name'])){$image_name = $_POST['image_name'];}
        if(!empty($_POST['image_width'])){$image_width = $_POST['image_width'];}
        if(!empty($_POST['user_id'])){$log_user_id = $_POST['user_id'];}
        if(!empty($_POST['no_of_pages'])){$no_of_pages = $_POST['no_of_pages'];}
        $signature_array = $_POST['signature_array'];
        $signature_date_array = $_POST['signature_date_array'];
        $sig_status=0;
        $sigdate_status=0;
        if(!$signature_array){$sig_status=1;}
        if(!$signature_date_array){$sigdate_status=1;}
        
        //print_r($signature_array);
        //exit;
        
        $randkey=rtrim(base64_encode(md5(microtime().$log_user_id)),"=");
        $keydata="'".$randkey."'";
        $img_name="'".$image_name."'";
        $sqldata=$dbh->prepare("INSERT INTO tbl_draw_sign_details (user_id,drop_off_request_id,img_name,image_width,no_of_pages,keypath) VALUES ($log_user_id,$drop_off_request_id,$img_name,$image_width,$no_of_pages,$keydata)");
                if($sqldata->execute()){
                        $tbl_draw_sign_details_id = $dbh->lastInsertId();
                }
                //echo $tbl_draw_sign_details_id;
        if($signature_array){
            $sig_status=1;
            for ($i=0; $i<count($signature_array) ;$i++)
            {
                $key=$i+1;
                if(!empty( $_POST['sign_left_pos-'.$key])){$sign_left_pos =  $_POST['sign_left_pos-'.$key];}
                if(!empty( $_POST['sign_top_pos-'.$key])){$sign_top_pos =  $_POST['sign_top_pos-'.$key];}
                if(!empty( $_POST['sign_width-'.$key])){$sign_width =  $_POST['sign_width-'.$key];}
                if(!empty( $_POST['sign_height-'.$key])){$sign_height =  $_POST['sign_height-'.$key];}
                
                $sqldata=$dbh->prepare("INSERT INTO tbl_draw_sign_pos_details (sign_left_pos,sign_top_pos,sign_width,sign_height,tbl_draw_sign_details_id,sig_type) VALUES ($sign_left_pos,$sign_top_pos,$sign_width,$sign_height,$tbl_draw_sign_details_id,'signature')");
                $sqldata->execute();
          
            }
        }
        
        if($signature_date_array){
           $sigdate_status=1;
            for ($j=0; $j<count($signature_date_array) ;$j++)
            {
                $key1=$j+1;
                if(!empty( $_POST['sign_left_pos_date-'.$key1])){$sign_left_pos_date =  $_POST['sign_left_pos_date-'.$key1];}
                if(!empty( $_POST['sign_top_pos_date-'.$key1])){$sign_top_pos_date =  $_POST['sign_top_pos_date-'.$key1];}
                if(!empty( $_POST['sign_width_date-'.$key1])){$sign_width_date =  $_POST['sign_width_date-'.$key1];}
                if(!empty( $_POST['sign_height_date-'.$key1])){$sign_height_date =  $_POST['sign_height_date-'.$key1];}
                
                $sqldata1=$dbh->prepare("INSERT INTO tbl_draw_sign_pos_details (sign_left_pos,sign_top_pos,sign_width,sign_height,tbl_draw_sign_details_id,sig_type) VALUES ($sign_left_pos_date,$sign_top_pos_date,$sign_width_date,$sign_height_date,$tbl_draw_sign_details_id,'date')");
                $sqldata1->execute();
               
            }
        }


    if($sig_status==1 && $sigdate_status==1){
        $stmt = $dbh->prepare("SELECT * FROM tbl_drop_off_request WHERE id=:drop_off_request_id");
        $stmt->execute(['drop_off_request_id' => $drop_off_request_id]); 
        // $stmt->execute(['drop_off_request_id' => $drop_off_request_id]); 
        $email_exist = $stmt->fetch();
        // var_dump($email_exist['to_subject_request']);die();
        if(!empty($email_exist)) 
        {
            
            $from_organization = $email_exist['from_organization'];
            $to_organization = $email_exist['to_organization'];
            $to_name_request = $email_exist['to_name'];
            $to_email_request = $email_exist['to_email'];
            $to_subject_request = $email_exist['to_subject_request'];
            $to_note_request = $email_exist['to_note_request'];
            $signaturestatus = $email_exist['signaturestatus'];  
            
            
            $stmt11 = $dbh->prepare("SELECT * FROM tbl_draw_sign_details WHERE drop_off_request_id=:drop_off_request_id");
            $stmt11->execute(['drop_off_request_id' => $drop_off_request_id]); 
            $data11 = $stmt11->fetch();
            if($data11){
                $file_name = pathinfo($data11['img_name'], PATHINFO_FILENAME).".pdf";
                $url = $file_name;
                $filenamearray = explode(".",$url);
                $filename1 = $filenamearray[0];       $array_file_name[] = $filenamearray[0];
                $uploader = $_SESSION['loggedin'];
                $statement11 = $dbh->prepare("INSERT INTO ".TABLE_FILES." (`url`, `filename`, `description`, `timestamp`, `uploader`, `expires`, `expiry_date`, `future_send_date`, `public_allow`, `public_token`,`request_type`,`tbl_drop_off_request_id`) VALUES ('$url', '$filename1', '', CURRENT_TIMESTAMP, '$uploader', '0', '2017-12-09 00:00:00',  '".date('Y-m-d 00:00:00')."','0', NULL,'1','$drop_off_request_id');");
                if($statement11->execute()) {
                    $img_id = $dbh->lastInsertId();
                    $sql55 = $dbh->prepare("UPDATE " . TABLE_FILES . " SET `req_status` = '1' WHERE id = ". $img_id);
	                $sql55->execute();
                    $fromid=$email_exist['from_id'];
                    $reqclientid = $email_exist['reqclientid'];
                    $filesrelations11 = $dbh->prepare("INSERT INTO ".TABLE_FILES_RELATIONS." (`timestamp`, `file_id`, `client_id`, `from_id`, `group_id`, `folder_id`, `hidden`, `download_count`,`req_type`) VALUES (CURRENT_TIMESTAMP, ".$img_id.", ".$reqclientid.", ".$fromid." ,NULL, NULL, '0', '0', '1')");
                    $filesrelations11->execute();
                }
            }
            
            
        $signatureinstruction='<br> <strong>Step 4: Your signature will be required.</strong>';
        $message ="<html>
        <head>
        <meta name='viewport' content='width=device-width'>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
        <title>Simple Transactional Email</title>
        <style type='text/css'>
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {font-size: 28px !important;margin-bottom: 10px !important; }
            table[class=body] p,table[class=body] ul,table[class=body] ol,table[class=body] td,table[class=body] span,table[class=body] a {font-size: 16px !important; }
            table[class=body] .wrapper,
            table[class=body] .article {padding: 10px !important; }
            table[class=body] .content {padding: 0 !important; }
            table[class=body] .container {padding: 0 !important;width: 100% !important; }
            table[class=body] .main {border-left-width: 0 !important;border-radius: 0 !important;border-right-width: 0 !important; }
            table[class=body] .btn table {width: 100% !important; }
            table[class=body] .btn a {width: 100% !important; }
            table[class=body] .img-responsive {height: auto !important;max-width: 100% !important;width: auto !important; }
        }
        /* -------------------------------------
            PRESERVE THESE STYLES IN THE HEAD
        ------------------------------------- */
        @media all {
            .ExternalClass {width: 100%; }
            .ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div {line-height: 100%; }
            .apple-link a {color: inherit !important;font-family: inherit !important;font-size: inherit !important;font-weight: inherit !important;line-height: inherit !important;text-decoration: none !important; }
            .btn-primary table td:hover {background-color: #34495e !important; }
            .btn-primary a:hover {background-color: #34495e !important;border-color: #34495e !important; } }
        </style>
            <head>
            <title>$to_subject_request</title>
            </head>
        </head>
        <body class='' style='background-color:#f6f6f6;font-family:sans-serif;-webkit-font-smoothing:antialiased;font-size:14px;line-height:1.4;margin:0;padding:0;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;'>
        <table border='0' cellpadding='0' cellspacing='0' class='body' style='border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;background-color:#f6f6f6;width:100%;'>
        <tr>
            <td style='font-family:sans-serif;font-size:14px;vertical-align:top;'>&nbsp;</td>
            <td class='container' style='font-family:sans-serif;font-size:14px;vertical-align:top;display:block;max-width:580px;padding:10px;width:580px;Margin:0 auto !important;'>
            <div class='content' style='box-sizing:border-box;display:block;Margin:0 auto;max-width:580px;padding:10px;'>
            <!-- START CENTERED WHITE CONTAINER -->
            <span class='preheader' style='color:transparent;display:none;height:0;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;visibility:hidden;width:0;'>$to_subject_request</span>
            <table class='main' style='border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;background:#fff;border-radius:3px;width:100%;'>
              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class='wrapper' style='font-family:sans-serif;font-size:14px;vertical-align:top;box-sizing:border-box;padding:20px;'>
                  <table border='0' cellpadding='0' cellspacing='0' style='border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;'>
                    <tr>
                      <td style='font-family:sans-serif;font-size:14px;vertical-align:top;'>
                        <p style='font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;Margin-bottom:15px;'>Hi $to_name_request</p>
                        <p style='font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;Margin-bottom:15px;'>This message was sent so that you can drop-off some files for someone at MicroHealth Send.</p>
        
              <p>
            DETAILS:<br>
            From Name: $logged_in_name<br>
            From Organization: $from_organization<br>
            Email: $logged_in_email<br><br>
            To Name: $to_name_request<br>
            To Organization: $to_organization<br>
            Email: $to_email_request<br></p>
            <p><em>Note: ".$to_note_request."</em><br></p>
              <p>INSTRUCTIONS:<br>
              Step 1: Click the Go link below.<br>
              Step 2: If already logged in to MicroHealth Send in this browser, go to Step 3. Otherwise, log in on the Index screen.<br>
              Step 3: Continue the uploading process on the Drop-off Request screen.".$signatureinstruction."</p>
            
                                    <table border='0' cellpadding='0' cellspacing='0' class='btn btn-primary' style='border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;box-sizing:border-box;width:100%;'>
                                      <tbody>
                                        <tr>
                                          <td align='left' style='font-family:sans-serif;font-size:14px;vertical-align:top;padding-bottom:15px;'>
                                            <table border='0' cellpadding='0' cellspacing='0' style='border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;width:auto;'>
                                              <tbody>
                                                <tr>
                                                  <td style='font-family:sans-serif;font-size:14px;vertical-align:top;background-color:#ffffff;border-radius:5px;text-align:center;background-color:#3498db;'> <a href='".BASE_URI."sign_document.php?auth=".$randkey."&key=sign' target='_blank' style='text-decoration:underline;background-color:#ffffff;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;color:#3498db;cursor:pointer;display:inline-block;font-size:14px;font-weight:bold;margin:0;padding:12px 25px;text-decoration:none;text-transform:capitalize;background-color:#3498db;border-color:#3498db;color:#ffffff;'>go</a> </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    
                                    <p style='font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;Margin-bottom:15px;'>Good luck!</p>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <!-- END MAIN CONTENT AREA -->
                        </table>
                        <!-- START FOOTER -->
                        <div class='footer' style='clear:both;padding-top:10px;text-align:center;width:100%;'>
                          <table border='0' cellpadding='0' cellspacing='0' style='border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;'>
                            <tr>
                              <td class='content-block' style='font-family:sans-serif;font-size:14px;vertical-align:top;color:#999999;font-size:12px;text-align:center;'>
                                <span class='apple-link' style='color:#999999;font-size:12px;text-align:center;'>MicroHealth Send</span>
                                <br>
                                 Don't like these emails? <a href='' style='color:#3498db;text-decoration:underline;color:#999999;font-size:12px;text-align:center;'>Unsubscribe</a>.
                              </td>
                            </tr>
                            <tr>
                              <td class='content-block powered-by' style='font-family:sans-serif;font-size:14px;vertical-align:top;color:#999999;font-size:12px;text-align:center;'>
                               
                              </td>
                            </tr>
                          </table>
                        </div>
                        <!-- END FOOTER -->
                        <!-- END CENTERED WHITE CONTAINER -->
                      </div>
                    </td>
                    <td style='font-family:sans-serif;font-size:14px;vertical-align:top;'>&nbsp;</td>
                  </tr>
                </table>
              </body>
            </html>";
            /* phpMailer */
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
            $send_mail->Subject = "DROP OFF REQUEST";
            $send_mail->MsgHTML($message);
            $send_mail->AltBody = __('This email contains HTML formatting and cannot be displayed right now. Please use an HTML compatible reader.','cftp_admin');
            $send_mail->SetFrom(ADMIN_EMAIL_ADDRESS, MAIL_FROM_NAME);
            $send_mail->AddReplyTo(ADMIN_EMAIL_ADDRESS, MAIL_FROM_NAME);
            $send_mail->AddAddress($email_exist['to_email']);
            
            /**
             * Finally, send the e-mail.
             */
            if($send_mail->Send()) {
                 echo '<script>window.location.href="create_signature_spot.php?pdf_name='.$pname.'&id='.$log_user_id.'&req_id='.$drop_off_request_id.'&mail_status=true"</script>';
                
                // $cc_status = "<div class=\"alert alert-success cc-success\"><strong>Success! </strong>Your Request has been submitted successfully.</div>";
            }
            else {
                 echo '<script>window.location.href="create_signature_spot.php?pdf_name='.$pname.'&id='.$log_user_id.'&req_id='.$drop_off_request_id.'&mail_status=false"</script>';
                // $cc_status = "<div class=\"alert alert-danger cc-failed\"><strong>Oops! </strong>Something went wrong! Please try after sometime.</div>";
            }
            // echo '<script>$(document).ready(function(){$("#cc-mail-status").modal("toggle");});</script>';
        }
        // $sqldata->debugDumpParams();
    }




$allowed_levels = array(9,8,7,0);

//$log_user_id=1;
$targetsignature_file = '/img/avatars/tempsignature/'.$log_user_id.'/temp/'.$log_user_id.".png";
//echo $targetsignature_file;
if (file_exists(__DIR__ . $targetsignature_file)) {
        $signature_exist = true;
}else{
        $signature_exist = false;
}
require_once('sys.includes.php');
include('header_no_left.php');
?>

<style>
    /*custom*/
    #frame{
    }
    #frame img{
    z-index: 1; 
    position: relative;
    
    }
    .draggable{
        z-index: 2; 
        position: relative;
    }
    .sign_positions{
        z-index: 3; 
        position: absolute;
        top:100px;
        right:100px;
        width:250px;
    }
    .no_padding{
        padding:0px !important;
    }
   .not_signature_exist,.signature_exist,.sign_pad_pos{
        z-index: 3; 
        position: absolute;
        background:white;
        border:2px solid gray;
        overflow: hidden;
        text-align: center;
        font-size: 20px;
    }
  </style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</style>
<div id="main1">
    
    <div id="content" style="background:#edebeb;"> 
    
    <!-- Added by B) -------------------->
    <div class="container">
      <div class="row">
        <div class="col-md-12 tools_section" >
                <button id="btnSaveSign" class="btn btn-primary pull-right">Save Signature</button>
        </div>
        </div>
        </div>
       
    <div class="container">
        <div class="row">
                <div class="col-md-12" id="frame">
                
                <?php
                if(!empty($signature_date_array)){
                        foreach($signature_date_array as $sg){
                        
                        ?>
                        <input type="hidden" id="sign_date_pad_left-<?php echo $sg;?>" value="<?php echo $_POST['sign_left_pos_date-'.$sg];?>" >
                        <input type="hidden" id="sign_date_pad_top-<?php echo $sg;?>" value="<?php echo $_POST['sign_top_pos_date-'.$sg];?>" >
                        <input type="hidden" id="sign_date_pad_width-<?php echo $sg;?>" value="<?php echo $_POST['sign_width_date-'.$sg];?>" >
                        <input type="hidden" id="sign_date_pad_height-<?php echo $sg;?>" value="<?php echo $_POST['sign_height_date-'.$sg];?>" >
                                <div  id="sign_date_pad-<?php echo $sg;?>" style="left:<?php echo $_POST['sign_left_pos_date-'.$sg]."px";?>;top:<?php echo $_POST['sign_top_pos_date-'.$sg]."px";?>;width:<?php echo $_POST['sign_width_date-'.$sg]."px";?>;height:<?php echo $_POST['sign_height_date-'.$sg]."px";?>;" class="sign_pad_pos" ><?php echo date('d/m/Y') ?></div>
                        <?php
                        }
                
                }
        
                ?>
                <?php
                if(!empty($signature_array)){
                        foreach($signature_array as $sg){
                        
                        ?>
                        <input type="hidden" id="sign_pad_left-<?php echo $sg;?>" value="<?php echo $_POST['sign_left_pos-'.$sg];?>" >
                        <input type="hidden" id="sign_pad_top-<?php echo $sg;?>" value="<?php echo $_POST['sign_top_pos-'.$sg];?>" >
                        <input type="hidden" id="sign_pad_width-<?php echo $sg;?>" value="<?php echo $_POST['sign_width-'.$sg];?>" >
                        <input type="hidden" id="sign_pad_height-<?php echo $sg;?>" value="<?php echo $_POST['sign_height-'.$sg];?>" >
                                <div id="sign_pad-<?php echo $sg;?>" style="left:<?php echo $_POST['sign_left_pos-'.$sg]."px";?>;top:<?php echo $_POST['sign_top_pos-'.$sg]."px";?>;width:<?php echo $_POST['sign_width-'.$sg]."px";?>;height:<?php echo $_POST['sign_height-'.$sg]."px";?>;" <?php if($signature_exist){ echo 'class ="signature_exist sign_pad_pos"'; }else{echo 'class="not_signature_exist sign_pad_pos"'; }?> ></div>
                        <?php
                        }
                
                }
        
                ?>
                        
                                
                        
                        <img width = "<?php echo $image_width;?>" src="<?php echo BASE_URI."upload/files/mysignature/".$log_user_id."/".$drop_off_request_id."/". $image_name; ?>" >
                
                </div>
                
            </div>
        </div>
    </div>
     </div>
</div>

<?php
    include('footer.php');
?>

<div id="sig" class="modal fade" role="dialog">
	<div class="modal-dialog">

	<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" onclick="signaturemodalclose()">&times;</button>
				<h4 class="modal-title">Draw New Signature </h4>
			</div>
			<div class="modal-body">
				<input type="hidden" id="uid" value="<?php echo $log_user_id;?>">
				<?php
					include('signature.php');
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" onclick="signaturemodalclose()">Close</button>
			</div>
		</div>

	</div>
</div>
<?php if($signature_exist){?>
<div id="sign_exist" class="modal fade" role="dialog">
	<div class="modal-dialog">

	<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" onclick="signatureexistmodalclose()">&times;</button>
				<h4 class="modal-title">Use this signature </h4>
			</div>
			<div class="modal-body">
				<input type="hidden" id="uid" value="<?php echo $log_user_id;?>">
				<input type="hidden" id="sign_pad_id" value="">
				<input type="hidden" id="sign_pad_width" value="">
				<!--<img src="<?php //echo "http://rndsllc.website/mSend-master005/". $targetsignature_file;?>"> -->
				<img src="<?php echo BASE_URI. $targetsignature_file;?>"> 
				<button type="button" id="use_this_sign" class="btn btn-green" >Use this</button>
				<button type="button" id="create_new_sign" class="btn btn-primary" >Draw New</button>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" onclick="signatureexistmodalclose()">Close</button>
			</div>
		</div>

	</div>
</div>

<?php }?>
	
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  <!-- SIGN RELATED -->
  <link href="./css/signature/jquery.signaturepad.css" rel="stylesheet">
<script src="./js/signature/numeric-1.2.6.min.js"></script> 
<script src="./js/signature/bezier.js"></script>
<script src="./js/signature/jquery.signaturepad.js"></script> 

<script type='text/javascript' src="./js/signature/html2canvas.js"></script>
<script src="./js/signature/json2.min.js"></script>
<script>
$(document).ready(function(){
        var current_frame_width = $('#frame').width();
        console.log(current_frame_width);
        var parent_frame_width = <?php echo $image_width;?>;
        $('#frame img').width(current_frame_width);
        //$('#frame img').width(frame_width);
        $(".sign_pad_pos").each(function() {
            var sign_pad_id = $(this).attr('id');
            console.log('sign_pad_id1 ::: '+sign_pad_id.split('-')[0]);
            console.log('sign_pad_id2 ::: '+sign_pad_id.split('-')[1]);
            console.log('sign_pad_id3 ::: '+sign_pad_id.split('-')[0]+'_left-'+sign_pad_id.split('-')[1]);
            var sign_pad_width = $('#'+sign_pad_id.split('-')[0]+'_width-'+sign_pad_id.split('-')[1]).val();
            console.log('sign_pad_width ::: '+sign_pad_width);
            var sign_pad_height = $('#'+sign_pad_id.split('-')[0]+'_height-'+sign_pad_id.split('-')[1]).val();
            console.log('sign_pad_height ::: '+sign_pad_height);
            var sign_pad_left = $('#'+sign_pad_id.split('-')[0]+'_left-'+sign_pad_id.split('-')[1]).val();
            console.log('sign_pad_left ::: '+sign_pad_left);
            var sign_pad_top = $('#'+sign_pad_id.split('-')[0]+'_top-'+sign_pad_id.split('-')[1]).val();
            console.log('sign_pad_top ::: '+sign_pad_top);
            adjust_document_sign_pos(parent_frame_width,current_frame_width,sign_pad_id,sign_pad_width,sign_pad_height,sign_pad_left,sign_pad_top);
        });
        var big = Math.max(parent_frame_width,current_frame_width);
        /*var sign_left_pos = <?php echo $sign_left_pos; ?>;
        var sign_top_pos = <?php echo $sign_top_pos; ?>;
        var sign_width = <?php echo $sign_width; ?>;
        var sign_height = <?php echo $sign_height; ?>;*/
        //console.log("big: "+big);
        
        $( window ).resize(function() {
                location.reload();
        });
        function adjust_document_sign_pos(parent_frame_width=null,current_frame_width=null,sign_pad_id,sign_pad_width,sign_pad_height,sign_pad_left,sign_pad_top){
                //alert(parent_frame_width+" -----  "+current_frame_width);
                if(parent_frame_width == current_frame_width){
                        sign_pad_id_n =  sign_pad_id.split('-')[1];
                        console.log('------'+sign_pad_id_n);
                        
                        /*sign_left_pos = <?php echo $sign_left_pos; ?>;
                        sign_top_pos = <?php echo $sign_top_pos; ?>;
                        sign_width = <?php echo $sign_width; ?>;
                        sign_height = <?php echo $sign_height; ?>;*/
                        $('#frame img').width(current_frame_width);
                }
                
                
                if(parent_frame_width > current_frame_width){
                        var fraction = parent_frame_width/current_frame_width;
                        console.log(('f1: '+fraction));
                        sign_pad_left = sign_pad_left/fraction;
                        sign_pad_top = sign_pad_top/fraction;
                        sign_pad_width = sign_pad_width/fraction;
                        sign_pad_height = sign_pad_height/fraction;
                        $('#'+sign_pad_id).css('left',sign_pad_left);
                        $('#'+sign_pad_id).css('top',sign_pad_top);
                        $('#'+sign_pad_id).css('width',sign_pad_width);
                        $('#'+sign_pad_id).css('height',sign_pad_height);
                        sign_pad_id_n =  sign_pad_id.split('-')[0];
                        console.log('------'+sign_pad_id_n);
                }
                
                
                if(parent_frame_width < current_frame_width){
                        var fraction = parent_frame_width/current_frame_width;
                        //alert('f2: '+ fraction);
                        sign_pad_left = sign_pad_left*fraction;
                        sign_pad_top = sign_pad_top*fraction;
                        sign_pad_width = sign_pad_width*fraction;
                        sign_pad_height = sign_pad_height*fraction;
                        $('#frame img').width(current_frame_width);
                        $('#'+sign_pad_id).css('left',sign_pad_left);
                        $('#'+sign_pad_id).css('top',sign_pad_top);
                        $('#'+sign_pad_id).css('width',sign_pad_width);
                        $('#'+sign_pad_id).css('height',sign_pad_height);
                }
        }
        
  });			 
</script>
<script>
		$(document).ready(function() {
			$('#signArea').signaturePad({drawOnly:true, drawBezierCurves:true, lineTop:90});
		});
		
		$("#btnSaveSign").click(function(e){
		    
		        alert('Generating signed document... Click OK to continue....');
			html2canvas([document.getElementById('frame')], {
				onrendered: function (canvas) {
					var canvas_img_data = canvas.toDataURL('image/png');
					var img_data = canvas_img_data.replace(/^data:image\/(png|jpg);base64,/, "");
					//ajax call to save image inside folder
					var ajxurl='<?php echo BASE_URI; ?>';
					$.ajax({
				// 		url: 'http://rndsllc.website/mSend-master/save_signature.php',
						url: ajxurl+'save_signature.php',
						data: { img_data:img_data,drop_off_request_id:'<?php echo $tbl_draw_sign_details['drop_off_request_id'];?>' },
				// 		data: { img_data:img_data},
						type: 'post',
						dataType: 'json',
						success: function (response) {
						   //window.location.reload();
						  // window.open("http://rndsllc.website/mSend-master/"+response.file_name, '_blank');
						   window.open("<?php echo BASE_URI;?>"+response.file_name, '_blank');
						}
					});
				}
			});
		});
	$(".not_signature_exist").click(function(e){
	        $('#sig').modal('toggle');
	});
	
	function signaturefun(argument) {
	if(argument==1){
// 		$('#signature_exist').html('<img width="<?php //echo $sign_width; ?>" src="<?php// echo "http://rndsllc.website/mSend-master005/". $targetsignature_file;?>">');
		$('.signature_exist').html('<img width="<?php echo $sign_width; ?>" src="<?php echo BASE_URI. $targetsignature_file;?>">');
// 		$('#not_signature_exist').html('<img width="<?php //echo $sign_width; ?>" src="<?php //echo "http://rndsllc.website/mSend-master005/". $targetsignature_file;?>">');
		$('.not_signature_exist').html('<img width="<?php echo $sign_width; ?>" src="<?php echo BASE_URI. $targetsignature_file;?>">');
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
        function signatureexistmodalclose() {
                $('#sign_exist').modal('toggle');
        }
        $(".signature_exist").click(function(e){
                var eid = $(this).attr("id");
                $('#sign_pad_id').val(eid);
                var wdth = $(this).width();
                console.log(wdth);
                $('#sign_pad_width').val(wdth);
	        $('#sign_exist').modal('toggle');
	});
	$("#create_new_sign").click(function(e){
	        $('#sig').modal('toggle');
	        $('#sign_exist').modal('toggle');
	        
	});
	$("#use_this_sign").click(function(e){
	        $('#sign_exist').modal('toggle');
	       // $('#signature_exist').html('<img width="<?php //echo $sign_width; ?>" src="<?php //echo "http://rndsllc.website/mSend-master005/". $targetsignature_file;?>">');
	        var sign_pad_id = $('#sign_pad_id').val();
	        var sign_pad_width = $('#sign_pad_width').val();
	        $('#'+sign_pad_id).html('<img width="'+sign_pad_width+'" src="<?php echo BASE_URI. $targetsignature_file;?>">');
	});
	$("#upload_this_sign").click(function(e){
    //   alert();
	});
	$('#OpenImgUpload').click(function(){ $('#upload_this_sign').trigger('click'); });
	

$(':file').on('change', function () {
  var file = this.files[0];

  console.log(file);
     $("#savefile").click(); 
});


$('#upload_this_sign').on('change',function(evt) {
    $('#but_upload').click();
});


function aa(){
    var fd = new FormData();
    var files = $('#upload_this_sign')[0].files[0];
    fd.append('upload_this_sign',files);
    
    $.ajax({
        url: 'newsign_choose.php',
        type: 'post',
        data: fd,
        contentType: false,
        processData: false,
        success: function(response){
          console.log(response);
        },
    });
}



 
		  </script> 
