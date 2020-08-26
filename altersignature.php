
<?php
    require_once('sys.includes.php');
    $user_id_mic = CURRENT_USER_ID;

    $sig_pic_details = $dbh->prepare("SELECT * FROM tbl_user_extra_profile WHERE user_id=:user_id and name='signature_pic'");
    $sig_pic_details->execute(['user_id' => $user_id_mic]);  
    $details = $sig_pic_details->fetch();
    if($details){
        echo json_encode(array('status'=>true,'name'=>$details['value']));
    }else{
        echo json_encode(array('status'=>false,'name'=>$details['value']));
    }
    
?>
