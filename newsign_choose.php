
<?php


    require_once('sys.includes.php');
    $user_id_mic = CURRENT_USER_ID;
    $targetsignature_dir = UPLOADED_FILES_FOLDER.'../../img/avatars/tempsignature/'.$user_id_mic.'/';
    $targetsignature_web = 'img/avatars/tempsignature/'.$user_id_mic.'/';
    //echo $targetsignature_dir;exit;
    if($_FILES["upload_this_sign"]["error"] == 0) {
	// echo 'updated';die();
		
		if (!file_exists($targetsignature_dir)) {
				mkdir($targetsignature_dir, 0777, true);
		}
		if (!file_exists($targetsignature_dir.'temp/')) {
			mkdir($targetsignature_dir.'temp/', 0777, true);
		}
		$target_file = $targetsignature_dir;
		$uploadOk = 1;
		$target_file = $targetsignature_dir . "/".basename($_FILES["upload_this_sign"]["name"]);
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		$fl_name = $user_id_mic.".".$imageFileType;
		$target_file = $targetsignature_dir.'temp/'.$fl_name;
		$targetsignature_web = $targetsignature_web.'temp/'.$fl_name;
		$uploadOk = 1;
		// Check if image file is a actual image or fake image
		$check = getimagesize($_FILES["upload_this_sign"]["tmp_name"]);
		if($check !== false) {
			//echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		} else {
		//	echo "File is not an image.";
			$uploadOk = 0;
		}
		// Allow certain file formats
// 		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
// 		&& $imageFileType != "gif" ) {
		if($imageFileType != "png") {
			//	echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
				$uploadOk = 0;
		}

		// echo("<br>Upload Ok = ".$uploadOk);
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
				echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (file_exists($target_file)) {
					unlink($target_file);
					// echo("<br>Unlinked Oldfile");
			}
			if (move_uploaded_file($_FILES["upload_this_sign"]["tmp_name"], $target_file)) {
//var_dump($target_file);die();
				$aes = new AESENCRYPT ();					
				$result  = $aes->encryptFile($fl_name,'upload',$user_id_mic);
// WORKING DECRYPTION CODE START
				// if($result){
					$result1  = $aes->decryptFile($fl_name,'upload',$user_id_mic);
				// echo "<pre>"; print_r($result1); echo "</pre>"; exit;
				// }
// WORKING DECRYPTION CODE END
				
				if(!empty($fl_name)){
					$statement = $dbh->prepare("DELETE FROM " . TABLE_USER_EXTRA_PROFILE . " WHERE user_id =".$user_id_mic." AND name='signature_pic'");
			    	$statement->execute();
					// echo("DONE");

					$alternate_email_save = $dbh->prepare( "INSERT INTO " . TABLE_USER_EXTRA_PROFILE . " (user_id, name, value,sig_type) VALUES (".$user_id_mic.",'signature_pic','".$fl_name."',2 ) ");
					$prochange=$alternate_email_save->execute();
					if($prochange==true){
			            echo json_encode(array("status"=>true,'file_name'=>$targetsignature_web));
					}else{
					 echo json_encode(array("status"=>false));
					}
				}

			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
	}
?>
