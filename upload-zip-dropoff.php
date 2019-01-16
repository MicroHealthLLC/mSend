<?php
require_once('sys.includes.php');
if (! check_for_session() ) {
	die();
}
$targetDir = UPLOADED_FILES_FOLDER;
$auth_key = isset($_POST['auth_key'])?$_POST['auth_key']:'';
$target_id = isset($_POST['target_id'])?$_POST['target_id']:'';
$target_name = isset($_POST['target_name'])?$_POST['target_name']:'';
    $zip = new ZipArchive();
    $finishedfile=$_POST['finished_files'];
    $fileName = $finishedfile['0'];
    $ext = strrpos($fileName, '.');
  	$fileName_a = substr($fileName, 0, $ext);
  	$fileName_b = substr($fileName, $ext);	$curr_usr_id= CURRENT_USER_ID;

  	$count = 1;
  	while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . 'compressed_' . $count . '_'. $curr_usr_id . '_'. $fileName_b))
  	$count++;

  	$fileName = $fileName_a . 'compressed_' . $count. '_' . $curr_usr_id . '_'. $fileName_b;
    $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $fileName);
    $zipname=$withoutExt.".zip";
    $zipFilePath = UPLOADED_FILES_FOLDER.$zipname;
    $r = $zip->open($zipFilePath,  ZipArchive::CREATE);
		var_dump($r);
    foreach ($_POST['finished_files'] as $p) {
        	$filesToAdd= file_get_contents(UPLOADED_FILES_FOLDER.$p);
        	$img = new AES($filesToAdd, ENCRYPTION_KEY, BLOCKSIZE);
					$decryptData =  $img->decrypt();
					unlink(UPLOADED_FILES_FOLDER.$p);
					file_put_contents(UPLOADED_FILES_FOLDER.$p, $decryptData);
					$r=$zip->addFile(UPLOADED_FILES_FOLDER.$p,$p);
					var_dump($r);
				}


		$r=$zip->close();
		var_dump($r);
		foreach ($_POST['finished_files'] as $p) {
		unlink(UPLOADED_FILES_FOLDER.$p);
		 }
    $repost = array(
    "uploader_0_name" => $zipname,
    "zipupload"=>1
    );

		// Encrypting the zip file
      // $fileData = file_get_contents( UPLOADED_FILES_FOLDER. $zipname);
      // $aes = new AES($fileData, ENCRYPTION_KEY, BLOCKSIZE);
      // $encData = $aes->encrypt();
      // unlink( UPLOADED_FILES_FOLDER. $zipname);
      // file_put_contents(UPLOADED_FILES_FOLDER. $zipname , $encData);
      ?>
    <form id="myForm" action="upload-process-form-dropoff.php" method="post">
        <input type="hidden" value="<?php echo isset($auth_key)?$auth_key:''; ?>" name="auth_key" />
        <input type="hidden" value="<?php echo isset($target_id)?$target_id:''; ?>" name="target_id" />
        <input type="hidden" value="<?php echo isset($target_name)?$target_name:''; ?>" name="target_name" />
        <input type="hidden" value="<?php echo $repost['uploader_0_name']; ?>" name="uploader_0_name">
        <input type="hidden" value="<?php echo $repost['zipupload']; ?> " name="zipupload">
        <input type="hidden" value="<?php echo $repost['uploader_0_name']; ?>" name="finished_files[]">
        <input type="hidden" value="done" name="uploader_0_status">
        <input type="hidden" value="1" name="uploader_count">
    </form>
    <script type="text/javascript">
      document.getElementById('myForm').submit();
    </script>
