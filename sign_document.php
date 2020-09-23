

<?php 

require_once('sys.includes.php');

$this_current_id = $_SESSION['loggedin_id'];



if(isset($_POST)){

        $sign_left_pos = $_POST['sign_left_pos'];

        $sign_top_pos = $_POST['sign_top_pos'];

        if(!empty($_POST['sign_width'])){$sign_width = $_POST['sign_width'];}else{$sign_width = 150;}

        if(!empty($_POST['sign_height'])){$sign_height = $_POST['sign_height'];}else{$sign_height = 40;}

        if(!empty($_POST['image_width'])){$image_width = $_POST['image_width'];}

        if(!empty($_POST['image_name'])){$image_name = $_POST['image_name'];}

        if(!empty($_POST['user_id'])){$log_user_id = $_POST['user_id'];}

        if(!empty($_POST['no_of_pages'])){$no_of_pages = $_POST['no_of_pages'];}

        //Array ( [sign_left_pos] => 83.37741088867188 [sign_top_pos] => 103.99038696289062 [sign_width] => 274.00962000000004 [sign_height] => 115.00962 )



}else{

        $sign_left_pos = 83.37741088867188;

        $sign_top_pos = 103.99038696289062;

        $sign_width = 274.00962000000004;

        $sign_height = 115.00962;

        $image_width = 1146.260;

}



 

 

$signature_request_key = $_GET['auth'];



$stmt1 = $dbh->prepare("SELECT * FROM tbl_draw_sign_details WHERE keypath=:keypath");

$stmt1->execute(['keypath' => $signature_request_key]); 



// $stmt1->execute(['drop_off_request_id' => $drop_off_request_id]); 

$tbl_draw_sign_detail_info = $stmt1->fetch();



// echo "<pre>";print_r($tbl_drop_off_request_info);echo "</pre>";exit;





if($tbl_draw_sign_detail_info){

    // $stmt5 = $dbh->prepare("SELECT * FROM tbl_drop_off_request WHERE id=".$tbl_draw_sign_detail_info['drop_off_request_id']." AND status=0");

    // $stmt5->execute(); 

    

    // $tbl_drop_off_request_info = $stmt5->fetch();

    // // echo "<pre>";print_r($tbl_drop_off_request_info);echo "</pre>";exit;

    // if($tbl_drop_off_request_info){

        $drop_off_request_id = $tbl_draw_sign_detail_info['drop_off_request_id'];

        $image_name = $tbl_draw_sign_detail_info['img_name'];

        $user_id = $tbl_draw_sign_detail_info['user_id'];

        $image_width = $tbl_draw_sign_detail_info['image_width'];

        $no_of_pages = $tbl_draw_sign_detail_info['no_of_pages'];

        //echo BASE_URI."upload/files/mysignature/".$user_id."/".$drop_off_request_id."/". $image_name;exit;

        //$drop_off_request_id=$tbl_draw_sign_detail_info['drop_off_request_id'];

        $stmt2 = $dbh->prepare("SELECT * FROM tbl_draw_sign_pos_details WHERE tbl_draw_sign_details_id=:tbl_draw_sign_details_id");

        $stmt2->execute(['tbl_draw_sign_details_id' => $tbl_draw_sign_detail_info['id']]); 

        $tbl_draw_sign_pos_details = $stmt2->fetchAll();

        //echo "<pre>";print_r($tbl_draw_sign_pos_details);echo "</pre>";exit;

        //need to check logged in user and signing user

        if($this_current_id!=$tbl_draw_sign_detail_info['user_id']){

            header('Location: '. BASE_URI.'process.php?do=logout');

        }

    // }else{

    //     // echo "<h2>";echo "You are not authorized to access this page!";   echo "</h2>";

    // }

}else{

   header('Location: '. BASE_URI.'process.php?do=logout');

}

$stmt = $dbh->prepare("SELECT * FROM tbl_draw_sign_details WHERE keypath=:keypath");

$stmt->execute(['keypath' => $signature_request_key]); 

// $stmt->execute(['drop_off_request_id' => $drop_off_request_id]); 

$tbl_draw_sign_details = $stmt->fetchAll();

while ($row = $stmt->fetch()) {

    //echo $row['sign_left_pos']."<br>";

}



// echo "<pre>";print_r($tbl_draw_sign_details);echo "</pre>";//exit;

if(!empty($tbl_draw_sign_details)) 

{

        $sign_left_pos = $tbl_draw_sign_details['sign_left_pos'];

        $sign_top_pos = $tbl_draw_sign_details['sign_top_pos'];

        $sign_width = $tbl_draw_sign_details['sign_width'];

        $sign_height = $tbl_draw_sign_details['sign_height'];

        //echo "YES";

}else{

        echo "NO key";

}

//exit;

//echo 'sign_width : '.$sign_width;

//echo 'sign_height : '.$sign_height;





$allowed_levels = array(9,8,7,0);



$sig_pic_details = $dbh->prepare("SELECT * FROM tbl_user_extra_profile WHERE user_id=:user_id and name='signature_pic'");

$sig_pic_details->execute(['user_id' => $this_current_id]);  

$details = $sig_pic_details->fetch();



$targetsignature_dir = UPLOADED_FILES_FOLDER.'../../img/avatars/tempsignature/'.$this_current_id.'/';

$sig_target_file = $targetsignature_dir . "/".$details['value'];



$imageFileType = pathinfo($sig_target_file,PATHINFO_EXTENSION);



//$log_user_id=1;

// $targetsignature_file = '/img/avatars/tempsignature/'.$this_current_id.'/temp/'.$this_current_id.".png";

$targetsignature_file = '/img/avatars/tempsignature/'.$this_current_id.'/temp/'.$this_current_id.'.'.$imageFileType;

// echo $targetsignature_file;die();

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

    #main1, html {

        background:#edebeb;

    }

    /*body{overflow-y:hidden;}*/

    #frame{

        position: relative;

    /*overflow-y: scroll;

    max-height: 450px;*/

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

   .not_signature_exist,.signature_exist,.sign_pad_pos,.signature_text,.signature_textarea{

        z-index: 3; 

        position: absolute;

        /*background:white; */

        border:2px solid gray;

        /*border:2px solid #ffffff;*/

        overflow: hidden;

        text-align: left;

        font-size: 20px;

    }

 

    .sign_text{

        border: 0;

    }

    .modal{

       z-index: 99999 !important;

    }

    .sign_pad_pos img {

        /*width: auto;*/

        /*max-width: 33px;*/

        /*max-width: 75%;*/

        

        /*max-width: 100px !important;*/

        /*height: 40px;*/

        /*width:100%;*/

        /*object-fit: contain;*/

    }

    .ba-doc-frame {

        -webkit-box-shadow: 0px 0px 16px -12px rgba(0,0,0,1);

        -moz-box-shadow: 0px 0px 16px -12px rgba(0,0,0,1);

        box-shadow: 0px 0px 16px -12px rgba(0,0,0,1);

    }

    #cc-mail-status {

        z-index: 9999;

    }

    #btnSaveSign {

        margin: 10px 15px;

    }

    #myform {

        display: inline-block;

        width: 100%;

    }

    #myform button {

    margin: 10px 10px 0 0;

    }

    .ba-page-wrap {

        position: relative;

    }

    .ba-page-no {

        font-size: 10px;

    }

  </style>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

</style>







<!-- Preloader start-->

<style>

    body {

  /*overflow: hidden;*/

}





#preloader {

  position: fixed;

  top: 0;

  left: 0;

  right: 0;

  bottom: 0;

  background-color: #fff;

  /* change if the mask should have another color then white */

  z-index: 99;

  /* makes sure it stays on top */

}



#status {

  width: 200px;

  height: 300px;

  position: absolute;

  left: 50%;

  /* centers the loading animation horizontally one the screen */

  top: 50%;

  /* centers the loading animation vertically one the screen */

  background-image: url(https://raw.githubusercontent.com/niklausgerber/PreLoadMe/master/img/status.gif);

  /* path to your loading animation */

  background-repeat: no-repeat;

  background-position: center;

  margin: -100px 0 0 -100px;

  /* is width and height divided by two */

}



#contentdiv{

    width: 200px;

    height: 300px;

    position: absolute;

    left: 50%;

    top: 78%;

    /* background-image: url(https://raw.githubusercontent.com/niklausgerber/PreLoadMe/master/img/status.gif); */

    background-repeat: no-repeat;

    background-position: center;

    margin: -100px 0 0 -100px;

    text-align:center;

}

/*.alert-success {*/

/*    border-color: #8ac38b;*/

/*    color: #356635;*/

/*    background-color: #cde0c4;*/

/*}*/



.size_fix{

    /*max-width: 150px !important;*/

    /*height: 40px;*/

    
    object-fit: contain;
    width: auto;
    height: 100%;

}

</style>



<style>

.resizable_text{

    /*resize:horizontal !important;*/

}

.resizable {

    /*display: inline-block;*/

    /*background: red;*/

    /*resize: both;*/

    /*overflow: hidden;*/

    /*line-height: 0;*/

  }



/*.resizable img {*/

/*  width: 100%;*/

/*  height: 100%;*/

/*}*/



/* loading dots */



.loadinginfo:after {

  content: ' .';

  animation: dots 1s steps(5, end) infinite;}



@keyframes dots {

    100% {

    text-shadow:

      .25em 0 0 black,

      .5em 0 0 black;}

}



.loadinginfo {

  color: red;

  font: 100% Impact;

  text-align: center;}

  

.disnone{

    display:none;

}

</style>





<!-- Preloader start-->

<div id="preloader">

  <div id="contentdiv">&nbsp;Loading...</div>

  <div id="status">&nbsp;</div>

</div>



<!-- Preloader end-->











<?php

    if($tbl_draw_sign_detail_info){

        $stmt5 = $dbh->prepare("SELECT * FROM tbl_drop_off_request WHERE id=".$tbl_draw_sign_detail_info['drop_off_request_id']." AND status=0");

        $stmt5->execute(); 

        $tbl_drop_off_request_info = $stmt5->fetch();

        if($tbl_drop_off_request_info){

?>

<div id="main1">

    <div id="content" style="background:#edebeb;"> 

    

    <!-- Added by B) -------------------->

    <div class="container ba-doc-frame">

        <div class="row">

        <div class="row">

        <div class="col-md-12 tools_section" >
            <input type="hidden" id="textid">
            <button class="btn btn-primary pull-right fontplus disnone" onclick="changetextsize('+')">Font-size <i class="fa fa-plus" aria-hidden="true"></i></button>&nbsp;&nbsp;
            <button class="btn btn-primary pull-right fontminus disnone" onclick="changetextsize('-')">Font-size <i class="fa fa-minus" aria-hidden="true"></i> &nbsp;&nbsp; </button>
            <button id="btnSaveSign" class="btn btn-primary pull-right" onClick="genPDF()"><i class="fa fa-floppy-o" aria-hidden="true"></i> &nbsp;&nbsp; Save Signature</button>

        </div>

        </div>

              

                <div class="col-md-12" id="frame">

                    <?php

                    if(!empty($tbl_draw_sign_pos_details)){

                            foreach($tbl_draw_sign_pos_details as $sg){

                            $sg['sign_width'] = $sg['sign_width'] + 4; // for calculation adj

                            $sg['sign_height'] = $sg['sign_height'] + 4; // for calculation adj

                            //echo $sg['sign_height'];

                               if($sg['sig_type']=='date'){ ?>

                                    <input type="hidden" id="sign_date_pad_left-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_left_pos'];?>" >

                                    <input type="hidden" id="sign_date_pad_top-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_top_pos'];?>" >

                                    <input type="hidden" id="sign_date_pad_width-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_width'];?>" >

                                    <input type="hidden" id="sign_date_pad_height-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_height'];?>" >

                                    <div  id="sign_date_pad-<?php echo $sg['id'];?>" style="left:<?php echo $sg['sign_left_pos']."px";?>;top:<?php echo $sg['sign_top_pos']."px";?>;width:<?php echo $sg['sign_width']."px";?>;height:<?php echo $sg['sign_height']."px";?>;" class="sign_pad_pos" ><?php echo date('d/m/Y');?></div>

                                <?php }else if($sg['sig_type']=='text'){?>

                                        <input type="hidden" id="sign_text_pad_left-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_left_pos'];?>" >

                                        <input type="hidden" id="sign_text_pad_top-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_top_pos'];?>" >

                                        <input type="hidden" id="sign_text_pad_width-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_width'];?>" >

                                        <input type="hidden" id="sign_text_pad_height-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_height'];?>" >

                                        <!--<div  id="sign_text_pad-<?php //echo $sg['id'];?>" style="left:<?php //echo $sg['sign_left_pos']."px";?>;top:<?php //echo $sg['sign_top_pos']."px";?>;width:<?php //echo $sg['sign_width']."px";?>;height:<?php //echo $sg['sign_height']."px";?>;" class="sign_pad_pos signature_text" ><input type="text" class="sign_text"></div>-->

                                        <div  id="sign_text_pad-<?php echo $sg['id'];?>" style="left:<?php echo $sg['sign_left_pos']."px";?>;top:<?php echo $sg['sign_top_pos']."px";?>;width:<?php echo $sg['sign_width']."px";?>;height:<?php echo $sg['sign_height']."px";?>;" class="sign_pad_pos signature_text resizable_text" onclick="changefont('<?php echo $sg['id'];?>')"><input type="text" class="sign_text resizable_text" style="left:<?php echo $sg['sign_left_pos']."px";?>;top:<?php echo $sg['sign_top_pos']."px";?>;width:<?php echo $sg['sign_width']."px";?>;height:<?php echo $sg['sign_height']."px";?>;"></div>

                                

                                <?php }else if($sg['sig_type']=='textarea'){?>

                                        <input type="hidden" id="sign_textarea_pad_left-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_left_pos'];?>" >

                                        <input type="hidden" id="sign_textarea_pad_top-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_top_pos'];?>" >

                                        <input type="hidden" id="sign_textarea_pad_width-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_width'];?>" >

                                        <input type="hidden" id="sign_textarea_pad_height-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_height'];?>" >

                                        <div  id="sign_textarea_pad-<?php echo $sg['id'];?>" style="left:<?php echo $sg['sign_left_pos']."px";?>;top:<?php echo $sg['sign_top_pos']."px";?>;width:<?php echo $sg['sign_width']."px";?>;height:<?php echo $sg['sign_height']."px";?>;" class="sign_pad_pos signature_textarea resizable_textarea" ><textarea class="sign_text resizable_textarea" style="left:<?php echo $sg['sign_left_pos']."px";?>;top:<?php echo $sg['sign_top_pos']."px";?>;width:<?php echo $sg['sign_width']."px";?>;height:<?php echo $sg['sign_height']."px";?>;"></textarea></div>

                                

                                <?php }else{?>

                                    <input type="hidden" id="sign_pad_left-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_left_pos'];?>" >

                                    <input type="hidden" id="sign_pad_top-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_top_pos'];?>" >

                                    <input type="hidden" id="sign_pad_width-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_width'];?>" >

                                    <input type="hidden" id="sign_pad_height-<?php echo $sg['id'];?>" value="<?php echo $sg['sign_height'];?>" >

                                    <div id="sign_pad-<?php echo $sg['id'];?>" style="left:<?php echo $sg['sign_left_pos']."px";?>;top:<?php echo $sg['sign_top_pos']."px";?>;width:<?php echo $sg['sign_width']."px";?>;height:<?php echo $sg['sign_height']."px";?>;" class ="signature_exist sign_pad_pos " ></div>

                               <?php }

                            }

                        

                    }

                   // $image_name=$tbl_draw_sign_details[0]['img_name'];

                   // $log_user_id=$tbl_draw_sign_details[0]['user_id'];

            

                    ?>

                       <?php

                                $image_name1 = explode('.',$image_name)[0]; //echo "<br>";

                                $image_name2 = explode('.',$image_name)[1];

                                if($no_of_pages>1){

                                        for ($i = 0; $i < $no_of_pages; $i++) {

                        ?>              <div id="ba-page-wrap_<?php echo $i; ?>" class="ba-page-wrap">

                                        <img src="<?php echo BASE_URI."upload/files/mysignature/".$user_id."/".$drop_off_request_id."/".$image_name1."-".$i.".".$image_name2; ?>"  >

                                        <span class="pull-right ba-page-no"><?php echo $i+1 ." of ". $no_of_pages;?></span>

                                        </div>

                        <?php

                                                       

                                        }

                                        

                                }else{

                                        

                        ?>

                                <div id="ba-page-wrap_0" class="ba-page-wrap">

                                <img src="<?php echo BASE_URI."upload/files/mysignature/".$user_id."/".$drop_off_request_id."/".$image_name; ?>" >

                                <span class="pull-right ba-page-no"><?php echo $i+1 ." of ". $no_of_pages;?></span>

                                </div>

                        <?php

                        

                                }

                        ?>

                </div>

            </div>

        </div>

    </div>

     </div>

<?php 

        }else{

            echo "<h2 style='height: 505px!important;padding: 20px;'>";echo "You are not authorized to access this page!";  echo "</h2>";

        }

    }

?>

</div>



<?php

    include('footer.php');

?>









<?php //if($signature_exist){?>





<!-- Modal -->



<!--<div id="cc-mail-status1" class="modal fade" role="dialog"  data-backdrop="static" data-keyboard="false">-->

<div id="cc-mail-status1" class="modal fade" role="dialog" >



  <div class="modal-dialog">

    <!-- Modal content-->



    <div class="modal-content">



      <div class="modal-header">



        <button type="button" class="close" data-dismiss="modal" onclick="modalclose()">&times;</button>



        <h4 class="modal-title">MicroHealth Send</h4>



      </div>



      <div class="modal-body">

            <div class="alert alert-success cc-success"><span id="msg1">Success!</span></div>

      </div>



      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"  onclick="modalclose()">Close</button>



      </div>

    </div>

  </div>

</div>



<?php //}?>

    

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>



  <!-- SIGN RELATED -->

  <link href="./css/signature/jquery.signaturepad.css" rel="stylesheet">

<script src="./js/signature/numeric-1.2.6.min.js"></script> 

<script src="./js/signature/bezier.js"></script>

<script src="./js/signature/jquery.signaturepad.js"></script> 



<script type='text/javascript' src="./js/signature/html2canvas.js"></script>

<script src="./js/signature/json2.min.js"></script>

<!--<script src="https://unpkg.com/jspdf@latest/dist/jspdf.min.js"></script>-->

<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>-->



<script src="https://unpkg.com/jspdf@1.5.3/dist/jspdf.min.js"></script>



<script>

    function modalclose(){

        window.location.href="<?php echo BASE_URI.'requested_file.php';?>";

    }

                     

                function genPDF() {

                    var tot_signaturecount = $('.signature_exist').length;

                    var sig_count=$(".signature_exist > img").length;

                    

                    if(tot_signaturecount!=sig_count){

                         alert('Please fill out all signature filed');

                         

                    }else{

                        $('#btnSaveSign').prop("disabled", true);

                        $('#contentdiv').html('Please wait...');

                        $('#status').fadeIn(); 

                        $('#preloader').fadeIn('slow');

                        

                        $('.not_signature_exist').css("border","2px solid #ffffff");

                        $('.signature_exist').css("border","2px solid #ffffff");

                        $('.sign_pad_pos').css("border","2px solid #ffffff");

                    

                    

                        var w=595;

                        var h=842;

                        var deferreds = [];

                        //var doc = new jsPDF('l', 'px', [h,w]);

                        var doc = new jsPDF("l", "px", "a4",true);

                        console.log(doc);

                        for (let i = 0; i < $('.ba-page-wrap').length; i++) {

                                var deferred = $.Deferred();

                                deferreds.push(deferred.promise());

                                generateCanvas(i, doc, deferred);

                        }

    

                        $.when.apply($, deferreds).then(function () { // executes after adding all images

                        //doc.save('test.pdf');

                        var ajxurl='<?php echo BASE_URI; ?>';

                        var blob = doc.output('blob');

                        var formData = new FormData();

                        formData.append('pdf', blob);

                        formData.append('drop_off_request_id', '<?php echo $drop_off_request_id;?>');

                        $.ajax({

                            url: ajxurl+'save_signature.php',

                            method: 'POST',

                            data: formData,

                            processData: false,

                            contentType: false,

                            success: function(response){

                                // alert(response);

                                if(response == 1) {

                        //          alert(response);

                                //  alert('Success!');

                                    $('#status').fadeOut();

                                    $('#preloader').fadeOut();

                                    $("#cc-mail-status1").modal("toggle").trigger('change');

                                    //window.location.href="<?php //echo BASE_URI.'inbox.php';?>";

                                }

                                else {

                                //  alert('Something Went wrong.!');

                                    $('#main1').fadeIn();

                                    $('#status').fadeOut();

                                    $('#preloader').fadeOut();

                                    $('#msg1').html('Something Went wrong.!');

                                    $("#cc-mail-status1").modal("toggle");

                                }

                            },

                            error: function(response){

                                if(response == 1) {

                        //          alert('Success!');

                                    $('#status').fadeOut();

                                    $('#preloader').fadeOut();

                                    $('#msg1').html('Success!');

                                    $("#cc-mail-status1").modal("toggle");

                                    //window.location.href="<?php //echo BASE_URI.'inbox.php';?>";

                                }

                                else {

                        //          alert('Something Went wrong.!');

                                    $('#main1').fadeIn();

                                    $('#status').fadeOut();

                                    $('#preloader').fadeOut();

                                    $('#msg1').html('Something Went wrong.!');

                                    $("#cc-mail-status1").modal("toggle");

                                }

                            }

                        });

                        //----------------------------------------------------------

                        });

                        

                    }

                    

                }



                function generateCanvas(i, doc, deferred){

                        var w=595;

                        var h=842;



                        html2canvas(document.getElementById("ba-page-wrap_" + i), {

                        



                                onrendered: function (canvas) {



                                        var img = canvas.toDataURL();

                                        //doc.addImage(img, 'JPEG');

                                        

                                        //doc.addImage(img, 'JPEG',  0, 0,w,h);

                                        //doc.addImage(img, 'JPEG', 45, 10, 520, 430,'FAST');

                                        doc.addImage(img, 'PNG', 56, 10, 520, 430,'','FAST');

                                        var number_of_page = $('.ba-page-wrap').length;

                                        console.log('number_of_page : '+number_of_page);

                                        if(number_of_page-1 > i){

                                                doc.addPage(); 

                                        }



                                        deferred.resolve();

                                }

                        });

                }

$(".sign_pad_pos").each(function() {

    e_id = $(this).attr('id');    

});



$(document).ready(function(){

        var current_frame_width = $('#frame').width();

        var parent_frame_width = <?php echo $image_width;?>;

        $('#frame img').width(current_frame_width);

        $(".sign_pad_pos").each(function() {

            var sign_pad_id = $(this).attr('id');

            var sign_pad_width = $('#'+sign_pad_id.split('-')[0]+'_width-'+sign_pad_id.split('-')[1]).val();

            var sign_pad_height = $('#'+sign_pad_id.split('-')[0]+'_height-'+sign_pad_id.split('-')[1]).val();

            var sign_pad_left = $('#'+sign_pad_id.split('-')[0]+'_left-'+sign_pad_id.split('-')[1]).val();

            var sign_pad_top = $('#'+sign_pad_id.split('-')[0]+'_top-'+sign_pad_id.split('-')[1]).val();

            adjust_document_sign_pos(parent_frame_width,current_frame_width,sign_pad_id,sign_pad_width,sign_pad_height,sign_pad_left,sign_pad_top);

        });

        var big = Math.max(parent_frame_width,current_frame_width);

        

        $( window ).resize(function() {

                // location.reload();

                //$(function() { $(".resizable").resizable(); });

        });

        function adjust_document_sign_pos(parent_frame_width=null,current_frame_width=null,sign_pad_id,sign_pad_width,sign_pad_height,sign_pad_left,sign_pad_top){

                //alert(parent_frame_width+" -----  "+current_frame_width);

                if(parent_frame_width == current_frame_width){

                        sign_pad_id_n =  sign_pad_id.split('-')[1];

                        

                        $('#frame img').width(current_frame_width);

                }

                

                

                if(parent_frame_width > current_frame_width){

                        var fraction = parent_frame_width/current_frame_width;

                        sign_pad_left = sign_pad_left/fraction;

                        sign_pad_top = sign_pad_top/fraction;

                        sign_pad_width = sign_pad_width/fraction;

                        sign_pad_height = sign_pad_height/fraction;

                        $('#'+sign_pad_id).css('left',sign_pad_left);

                        $('#'+sign_pad_id).css('top',sign_pad_top);

                        $('#'+sign_pad_id).css('width',sign_pad_width);

                        $('#'+sign_pad_id).css('height',sign_pad_height);

                        sign_pad_id_n =  sign_pad_id.split('-')[0];

                }

                

                

                if(parent_frame_width < current_frame_width){

                        var fraction = parent_frame_width/current_frame_width;

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

        $('#btnSaveSign').prop("disabled", false);

  });            

</script>



<script>

//  $(document).on('click','.not_signature_exist',function(){

//          $('#sig').modal('toggle');

//          var eid = $(this).attr("id");

//                 $('#sign_pad_id').val(eid);

//                 var wdth = $(this).width();

//                 $('#sign_pad_width').val(wdth);

//  });

    

   

       



    



$(':file').on('change', function () {

    $('#loadinginfo').removeClass('disnone').addClass('disnone');

    $('#loadinginfo1').removeClass('disnone').addClass('disnone');

    $("#sign_exist_new .sig1_new").prop("disabled", false);

    $("#sign_exist_new .sig2_new").prop("disabled", false);

    $("#sign_exist_new .sig3_new").prop("disabled", false);

    $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

    $('#sign_exist_new #use_this_sign').prop("disabled", false);

    var file = this.files[0];

    $("#savefile").click(); 

});













 



    $(document).ready(function() {

        $('#signArea').signaturePad({drawOnly:true, drawBezierCurves:true, lineTop:90});

        $("#preloader").fadeOut();

    });

 

</script> 







------------------------------------

            START

------------------------------------



<div id="sign_exist_new" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog">



    <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close sign_exist_new_close" onclick="signatureexistmodalclose()">&times;</button>

                <h4 class="modal-title">Use this signature</h4>

            </div>

            <div class="modal-body modelstylediv">

                

                <div class="col-sm-12">

                    <input type="hidden" id="uid" value="<?php echo $this_current_id;?>">

                    <span id="imgrender"></span>

                    <input type="hidden" id="sign_pad_id" value="">

                    <input type="hidden" id="sign_pad_width" value="">

                </div>  

             

                <div class="col-sm-12" id="sigs">

                    <div class="col-sm-8">

                        <label>

                            <input id="0001" type="radio" name="add_user_signature" class="sig1_new" onclick="changeradiobtn(1)"> 

                            <label for="0001">Current Signature</label>

                            <input id="0002" type="radio" name="add_user_signature" class="sig2_new" onclick="changeradiobtn(2)"> 

                            <label for="0002">Add new Signature</label>

                            <input type="hidden" name="sigtype" class="sigtype" id="sigtype" / >

                    </div>

                    <div class="col-sm-4">

                        <button class="btn btn-primary col-md-8" id="use_this_sign" type="button">Use this</button> 

                    </div>

                    <!--<div class="col-sm-3"></div>-->

                    <div class="col-sm-9">

                        <label>

                            <input type="checkbox" class="sig3_new" / > <?php _e('Save as your account signature','cftp_admin'); ?>

                            <input type="hidden" id="tempimgsrc" value="">

                            <input type="hidden" id="tempimgsrc_ext" value="">

                    </div>

                </div>

                    

            </div>

            <div class="modal-footer">

                <span class="col-md-12 disnone loadinginfo" id="loadinginfo">Loading</span>

                <button type="button" class="btn btn-default sign_exist_new_close" onclick="signatureexistmodalclose()">Close</button>

            </div>

        </div>



    </div>

</div>





<div id="signew" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog">



    <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close signew_close" onclick="signewclose()">&times;</button>

                <h4 class="modal-title">Add New Signature </h4>

            </div>

            <div class="modal-body" style="height: 130px;">

                <div class="form-group">

                    <div class="col-sm-12">

                        <input id="0003" type="radio" name="add_usersignature" class="sig1" checked="true">

                        <label for="0003">Upload Signature</label>

                        <input id="0004" type="radio" name="add_usersignature" class="sig2" onclick="newsign()" class='data-toggle="modal" data-target="#sig"' style="margin-left:10px;"> 

                        <label for="0004">Draw Signature</label>

                    </div>

                </div>

                <div class="col-sm-12"></div>

                <div class="form-group">

                    <div class="col-sm-8">

                        <input type="file"  name="upload_this_sign"  id="upload_this_sign"  accept=".png,.jpg,.jpeg,.gif" style="margin-top:15px;">

                        <input type="button"  id="but_upload" value="Upload"  style="display:none" onclick="uploadsignaturefun()"/>

                    </div>
                    <div class="col-sm-12" style="padding-bottom: 10px;"></div>
                    <div class="col-sm-12 disnone proceed" >
                         <button class="btn btn-primary btn-sm " type="button" onclick="fileproceed()">Proceed</button> 
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <span class="col-md-12 disnone loadinginfo" id="loadinginfo1">Loading</span>

                <button type="button" class="btn btn-default signew_close" onclick="signewclose()">Close</button>

            </div>

        </div>



    </div>

</div>





<div id="sig" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog" id="sigmodal">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" onclick="signaturemodalclose()">&times;</button>

                <h4 class="modal-title">Draw New Signature </h4>

            </div>

            <div class="modal-body">

                <input type="hidden" id="pageid" value="sign_document">

                <input type="hidden" id="uid" value="<?php echo $this_current_id;?>">

                <input type="hidden" id="doc_sign_page" name="doc_sign_page" value="<?php echo $this_current_id;?>">

                <input type="hidden" id="sigfile" name="sigfile" value="1">

                <input type="hidden" id="sign_pad_id" value="">

                <input type="hidden" id="sign_pad_width" value="">

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



<style >

    .sigoptionheight{

        margin-top:15px;

    }

    .modelstyle{

        height: 450px !important

    }

    .modelstyle1{

        height: 200px !important;

    }

</style>

<script>

   



    function changeradiobtn(arg){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        if(arg==2){

            // alert('Add new Signature model open - 444');

            // $('#sign_exist_new #tempimgsrc').val('');

            // $('#sign_exist_new #tempimgsrc_ext').val('');
            if($('#signew #upload_this_sign').val()!=''){
                $('.proceed').removeClass('disnone');
            }else{
                $('.proceed').removeClass('disnone').addClass('disnone');
            }

            $('#sign_exist_new').modal('hide');

            $('#signew').modal('toggle');

        }else{

            currentsignature(1,'currentsig');

        }

    }

        

    function signatureexistmodalclose() {

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // alert('Use this signature model close- 333');

        // $('#sign_exist_new .sig1_new').click();

        $('#sign_exist_new').modal('hide');

    }

    

    function signewclose() {

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        $('#signew').modal('hide');

        // $('#sign_exist_new .sig1_new').click();

        $('.sig2_new').prop('checked', false);

        // alert('Add New Signature model - 666');

    }

    

    $("#create_new_sign").click(function(e){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        $('#sig').modal('toggle');

        $('#signew').modal('toggle');

    });

    

    function signaturemodalclose() {

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // alert('Draw new signature model close - 888');

        $('#sig').modal('hide');

        $('.sig2_new').prop('checked', false);

        $('.sig1').click();

        // $('.sig1_new').click();

    }

    

</script>



<script>

    $("#frame").on( 'click', '.signature_exist', function () {

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // alert('Frame click open- 111');

        var eid ='';

        var wdth ='';

        var arg='';

        eid = $(this).attr("id");

        $('#sign_pad_id').val(eid);

        wdth = $(this).width();

        $('#sign_pad_width').val(wdth);

        arg='<?php echo $this_current_id;?>';

        currentsignature(1);

    });

    

    function currentsignature(arg='',arg1=''){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        var ajaxurl11='';

        var objectdata11 =''; 

        if(arg!=''){

            ajaxurl11='<?php echo BASE_URI; ?>';

            $.ajax({

                url: ajaxurl11+'altersignature.php',

                method: 'POST',

                processData: false,

                contentType: false,

                cache: false,

                success: function(response11){

                    objectdata11 = jQuery.parseJSON(response11);

                    // rendersignature(objectdata11,arg);

                    // console.log(objectdata11);

                    

                    var randNum1 = '';

                    var imgurl1= '';

            

                    var fd = '';

                    var files = '';

                    var imgurl2= '';

            

                    var ajaxurl3='';

                    var objct3 ='';

                    var imgurl3='';

                    var randNum3 ='';

                    

                    var sign_pad_id0 ='';

                    var sign_pad_width0 ='';

                    var ajaurl0='';

                    var objct0 ='';

                    var randNum0 ='';

                    var imgurl0='';

            

            

                    var dataUrl ="";

                    var block ="";

                    var contentType ="";

                    var realData ="";

            

                    if(arg==1){  

                        randNum1='?ver1='+Math.random() * 6;

                        if(objectdata11.status){

                            // alert('Current sign - 555');

                            imgurl1="";

                            imgurl1='<?php echo BASE_URI. 'img/avatars/tempsignature/'.$this_current_id.'/temp/';?>'+objectdata11.name+randNum1+'';

                            $('#sign_exist_new #sigs').addClass('sigoptionheight');

                            $('#sign_exist_new .modelstylediv').removeClass('modelstyle').addClass('modelstyle');

                            $('#sign_exist_new .modelstylediv').removeClass('modelstyle1');

                            $("#sign_exist_new .sig1_new").prop("disabled", false);

                            $("#sign_exist_new #use_this_sign").prop("disabled", false);

                            $('#sign_exist_new .sig1_new').prop('checked',true);

                            $('#sign_exist_new #imgrender').html('<img class="sign_img_new img-responsive" src="'+imgurl1+'">').trigger('change');

                            $("#sign_exist_new .sig3_new").prop("checked", false);

                            $("#sign_exist_new .sig3_new").prop("disabled", true);

                            $('#sign_exist_new #tempimgsrc').val('');

                            $('#sign_exist_new #tempimgsrc_ext').val('');

                            if(arg1!='currentsig'){

                                $('#sign_exist_new').modal('toggle');

                            }

                        }else{

                            // alert('Empty sign - 222');

                            imgurl1='<?php echo BASE_URI. 'img/avatars/no-image.png'?>';

                            $('#sign_exist_new .modelstylediv').removeClass('modelstyle1').addClass('modelstyle1');

                            $('#sign_exist_new .modelstylediv').removeClass('modelstyle');

                            $("#sign_exist_new .sig1_new").prop("disabled", true);

                            $("#sign_exist_new #use_this_sign").prop("disabled", true);

                            $('#sign_exist_new #imgrender').html('<img class="sign_img_new img-responsive" src="'+imgurl1+randNum1+'" style="margin: auto;">');

                            

                            $("#sign_exist_new .sig3_new").prop("checked", false);

                            $("#sign_exist_new .sig3_new").prop("disabled", true); 

                            

                            $('#sign_exist_new').modal('toggle');

                        }

                    } 

                    

                }

            });

        }  

        

    }

    

    $('#signew #upload_this_sign').on('change',function(evt) {

        // $('#signew .signew_close').prop("disabled", false);

        // $('#loadinginfo').removeClass('disnone').addClass('disnone');

        // $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        // $("#sign_exist_new .sig1_new").prop("disabled", false);

        // $("#sign_exist_new .sig2_new").prop("disabled", false);

        // $("#sign_exist_new .sig3_new").prop("disabled", false);

        // $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        // $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // $('#signew #but_upload').click();
        if($('#upload_this_sign').val()!=''){
            $('.proceed').removeClass('disnone').addClass('disnone');
            uploadsignature();
        }else{
            $('.proceed').removeClass('disnone').addClass('disnone');
        }
    });

    

    function uploadsignaturefun(){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        uploadsignature();

    }

    

    $("#signew #upload_this_sign").change(function(){
        if($('#upload_this_sign').val()!=''){
            $('.proceed').removeClass('disnone').addClass('disnone');
            $('#signew .signew_close').prop("disabled", false);
            $('#loadinginfo').removeClass('disnone').addClass('disnone');
    
            $('#loadinginfo1').removeClass('disnone').addClass('disnone');
    
            $("#sign_exist_new .sig1_new").prop("disabled", false);
    
            $("#sign_exist_new .sig2_new").prop("disabled", false);
    
            $("#sign_exist_new .sig3_new").prop("disabled", false);
    
            $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);
    
            $('#sign_exist_new #use_this_sign').prop("disabled", false);
    
            // $(".signew_close").prop("disabled", true);
    
            uploadsignature(this);
        }else{
            // $('.proceed').removeClass('disnone').addClass('disnone');
        }
    });

    

    function uploadsignature(datafile=""){

        $('#signew .signew_close').prop("disabled", true);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // alert('444');

        $('#loadinginfo1').removeClass('disnone');

        var fd = '';

        var files = '';

        var imgurl2= '';

        fd = new FormData();

        files = $('#upload_this_sign')[0].files[0];

        fd.append('upload_this_sign',files);

        setTimeout(function(){

            if (datafile.files && datafile.files[0]) {

                var reader = new FileReader();

                reader.onload = function (e) {

                    var ajaxurl001='';

                    var objectdata001 =''; 

                    ajaxurl001='<?php echo BASE_URI; ?>';

                    $.ajax({

                        url: ajaxurl001+'altersignature.php',

                        method: 'POST',

                        processData: false,

                        contentType: false,

                        cache: false,

                        success: function(response001){

                            objectdata001 = jQuery.parseJSON(response001);

                            if(objectdata001.status){

                                $("#sign_exist_new .sig1_new").prop("disabled", false);

                                $('#sign_exist_new .sig1_new').prop('checked',false);

                            }else{

                                $("#sign_exist_new .sig1_new").prop("disabled", true);

                            }

                        }

                    });

                    // setTimeout(function(){

                        var str = datafile.files[0].name;

                        var ext = str.substring(str.lastIndexOf(".") + 1, str.length);

                        $('#signew').modal('hide');

                        // $("#sign_exist_new .sig1_new").prop("disabled", false);

                        // $('#sign_exist_new .sig1_new').attr('checked',true);

                        // $('#sign_exist_new .sig1_new').click();

                        $("#sign_exist_new #use_this_sign").prop("disabled", false);

                        imgurl2 = e.target.result;

                        $('#sign_exist_new #tempimgsrc').val(imgurl2);

                        $('#sign_exist_new #tempimgsrc_ext').val(ext);

                        $('.sign_img_new').attr("src",imgurl2).trigger('change');

                        $("#sign_exist_new .sig3_new").prop("checked", false);

                        $("#sign_exist_new .sig3_new").prop("disabled", false);

                        $('.sig2_new').prop('checked', false);

                        // $(".signew_close").prop("disabled", false);

                        $('#sign_exist_new').modal('toggle');

                        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

                    // }, 2000);

                }

                reader.readAsDataURL(datafile.files[0]);

            }

        }, 2000);

    }

    

    $("#sign_exist_new #use_this_sign").click(function(e){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        var sign_pad_id0 ='';

        var sign_pad_width0 ='';

        var ajaurl0='';

        var objct0 ='';

        var randNum0 ='';

        var imgurl0='';

        sign_pad_id0 = $('#sign_pad_id').val();

        sign_pad_width0 = $('#sign_pad_width').val();

        ajaurl0='<?php echo BASE_URI; ?>';

        if($('#sign_exist_new #tempimgsrc').val()==''){

            // alert('use this - 111');

            $("#sign_exist_new .sig1_new").prop("disabled", true);

            $("#sign_exist_new .sig2_new").prop("disabled", true);

            $("#sign_exist_new .sig3_new").prop("disabled", true);

            $('#sign_exist_new .sign_exist_new_close').prop("disabled", true);

            $('#sign_exist_new #use_this_sign').prop("disabled", true);

            $('#loadinginfo').removeClass('disnone');

            

            $.ajax({

                url: ajaurl0+'altersignature.php',

                method: 'POST',

                processData: false,

                contentType: false,

                cache: false,

                success: function(response0){

                    setTimeout(function(){

                        objct0 = jQuery.parseJSON(response0);

                        // randNum0='?ver0='+Math.floor(Math.random() * 10);

                        randNum0='?ver0='+Math.random() * 7;

                        imgurl0='<?php echo BASE_URI. 'img/avatars/tempsignature/'.$this_current_id.'/temp/';?>'+objct0.name+randNum0+'';

                        $('#'+sign_pad_id0).html('<img class="size_fix" width="'+sign_pad_width0+'" src="'+imgurl0+'">').trigger('change');

                        $('#sign_exist_new').modal('toggle');

                        $('#loadinginfo').removeClass('disnone').addClass('disnone');

                    }, 2000);

                }

            });

        }else{

            // alert('use this - 222');

            var dataUrl=$("#tempimgsrc").val();

            // Split the base64 string in data and contentType

            var block = dataUrl.split(";");

            // Get the content type of the image

            var contentType = block[0].split(":")[1];// In this case "image/gif"

            // get the real base64 content of the file

            var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...." 

            

            if($("#sign_exist_new .sig3_new").prop('checked') == true){

                var altresult = confirm("Are you sure want to save as your account signature?");

                //  alert(altresult);

                if (altresult==true) {

                    $("#sign_exist_new .sig1_new").prop("disabled", true);

                    $("#sign_exist_new .sig2_new").prop("disabled", true);

                    $("#sign_exist_new .sig3_new").prop("disabled", true);

                    $('#sign_exist_new .sign_exist_new_close').prop("disabled", true);

                    $('#sign_exist_new #use_this_sign').prop("disabled", true);

                    $('#loadinginfo').removeClass('disnone');

                        // alert('aaa');

                    $.ajax({

                        url: 'save_tempfile.php',

                        data: { 'img_data':realData,'user_id_mic':'<?php echo $this_current_id;?>','doc_sign_page':true,'extension':$('#sign_exist_new #tempimgsrc_ext').val()},

                        type: 'post',

                        dataType: 'json',

                        async: false,

                        cache: false,

                        success:function(arg){

                            if(arg.status==true){

                                setTimeout(function(){

                                    $('#'+sign_pad_id0).html('<img class="size_fix" width="'+sign_pad_width0+'" src="'+dataUrl+'">').trigger('change');

                                    $("#sign_exist_new .sig3_new").prop("checked", false);

                                    $('#sign_exist_new .sig1_new').click();

                                    $('#sign_exist_new').modal('toggle').trigger('change');

                                    $('#loadinginfo').removeClass('disnone').addClass('disnone');

                                }, 2000);

                            }

                        }

                    });

                }else {

                    var ajaxurl003='';

                    var objectdata003 =''; 

                    ajaxurl003='<?php echo BASE_URI; ?>';

                    $.ajax({

                        url: ajaxurl003+'altersignature.php',

                        method: 'POST',

                        processData: false,

                        contentType: false,

                        cache: false,

                        success: function(response003){

                            objectdata003 = jQuery.parseJSON(response003);

                            if(objectdata003.status){

                                $('#sign_exist_new .sig1_new').click();

                            }else{

                                var imgurl0='<?php echo BASE_URI. 'img/avatars/no-image.png'?>';

                                var randNum0='?ver1='+Math.random() * 6;

                                $('#sign_exist_new .modelstylediv').removeClass('modelstyle1').addClass('modelstyle1');

                                $('#sign_exist_new .modelstylediv').removeClass('modelstyle');

                                $("#sign_exist_new .sig1_new").prop("disabled", true);

                                $("#sign_exist_new #use_this_sign").prop("disabled", true);

                                $('#sign_exist_new #imgrender').html('<img class="sign_img_new img-responsive" src="'+imgurl0+randNum0+'" style="margin: auto;">');

                                

                                $("#sign_exist_new .sig3_new").prop("checked", false);

                                $("#sign_exist_new .sig3_new").prop("disabled", true);   

                            }

                        }

                    });

                }

            }else{

                // alert('use this to account - 333');

                $('#'+sign_pad_id0).html('<img class="size_fix" width="'+sign_pad_width0+'" src="'+dataUrl+'">').trigger('change');

                // $('#sign_exist_new .sig1_new').click();

                $('#sign_exist_new').modal('toggle');

            }

        }

        

    });

    

    function newsign(){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // alert('Draw Signature - 777');

        $('#signaturechen').removeClass('disnone').addClass('disnone');

        $('#signew').modal('hide');

        $('#sig').modal('toggle');

    }

    

    function drawnewsignature(datafile=""){

        $('#signew .signew_close').prop("disabled", false);

        $('#loadinginfo').removeClass('disnone').addClass('disnone');

        $('#loadinginfo1').removeClass('disnone').addClass('disnone');

        $("#sign_exist_new .sig1_new").prop("disabled", false);

        $("#sign_exist_new .sig2_new").prop("disabled", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $('#sign_exist_new .sign_exist_new_close').prop("disabled", false);

        $('#sign_exist_new #use_this_sign').prop("disabled", false);

        // alert('drawnewsignature - 999');

        var ajaxurl002='';

        var objectdata002 =''; 

        ajaxurl002='<?php echo BASE_URI; ?>';

        $.ajax({

            url: ajaxurl002+'altersignature.php',

            method: 'POST',

            processData: false,

            contentType: false,

            cache: false,

            success: function(response002){

                objectdata002 = jQuery.parseJSON(response002);

                if(objectdata002.status){

                    $("#sign_exist_new .sig1_new").prop("disabled", false);

                    $('#sign_exist_new .sig1_new').prop('checked',false);

                }else{

                    $("#sign_exist_new .sig1_new").prop("disabled", true);

                }

            }

        });

        var imgurl3='';

        var str = datafile;

        var ext = str.substring(str.lastIndexOf(".") + 1, str.length);

        $('#sig').modal('hide');

        // $("#sign_exist_new .sig1_new").prop("disabled", false);

        

        $('#signew .sig1').click();

        

        // $('#sign_exist_new .sig1_new').attr('checked',true);

        // $('#sign_exist_new .sig1_new').click();

        imgurl3 = datafile;

        $('#sign_exist_new #tempimgsrc').val(imgurl3);

        $('#sign_exist_new #tempimgsrc_ext').val('png');

        $('.sign_img_new').attr("src",imgurl3).trigger('change');

        $("#sign_exist_new .sig3_new").prop("checked", false);

        $("#sign_exist_new .sig3_new").prop("disabled", false);

        $("#sign_exist_new #use_this_sign").prop("disabled", false);

        $('.sig2_new').prop('checked', false);

        $('#sign_exist_new').modal('toggle');         

    }
    
    function fileproceed(){
        $("#signew #upload_this_sign").change();
    }

    function changefont(signtext_id){
        $('#textid').val(signtext_id);
        $('.fontplus').removeClass('disnone').addClass('disnone');
        $('.fontminus').removeClass('disnone').addClass('disnone');
        $('.fontplus').removeClass('disnone');
        $('.fontminus').removeClass('disnone');
    }

    function changetextsize(fonttype){
        var total=0;
        var textpos=$('#textid').val();
        var currentfontsize=$('#sign_text_pad-'+textpos+ '.signature_text').css('font-size');
        if(fonttype=='+'){
            total = parseFloat(currentfontsize) + Number(1);
                $('#sign_text_pad-'+textpos+ '.signature_text').css("font-size",total + "px");
        }else{
            total = parseFloat(currentfontsize) - Number(1);
                $('#sign_text_pad-'+textpos+ '.signature_text').css("font-size",total + "px");
        }
    }
    

</script>



-----------------------------

            END

------------------------------

