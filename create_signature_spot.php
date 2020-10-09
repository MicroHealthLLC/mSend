<?php 

// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Report all PHP errors
error_reporting(-1);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

$pdf_name = $_GET['pdf_name'];
$userid = $_GET['id'];
$req_id = $_GET['req_id'];
$pname=$pdf_name;
$cc_status ='';
if($_GET['mail_status']){    
    $cc_status = "<div class=\"alert alert-success cc-success\"><strong>Success! </strong>Your Request has been submitted successfully.</div>";
}else{
    $cc_status = "<div class=\"alert alert-danger cc-failed\"><strong>Oops! </strong>Something went wrong! Please try after sometime.</div>";
}



if(!empty($pdf_name)){
    if (file_exists(__DIR__ . "/upload/files/mysignature/".$userid."/".$req_id.'/'.$pdf_name)) {

		
            // create Imagick object
            $imagick = new Imagick();
            
            $imagick->setResolution(250, 250);
            $pdf_name = __DIR__ . "/upload/files/mysignature/".$userid."/".$req_id.'/'.$pdf_name;
            // Reads image from PDF
            $imagick->readImage($pdf_name);
			//$imagick = $imagick->flattenImages();
            //echo "exist";exit;
            $number_of_pdf_images = $imagick->getNumberImages();
			$imagick->setCompression(Imagick::COMPRESSION_JPEG); 
		
			//$imagick->setImageBackgroundColor('white');
			//$imagick->setImageAlphaChannel(11);
			$imagick->setImageFormat('jpg');
            $imagick->setImageCompressionQuality(100);
			$imagick->mergeImageLayers(imagick::LAYERMETHOD_FLATTEN);
            
            // $image_name = md5(date("dmYhisA"));
            $image_name = pathinfo($pname, PATHINFO_FILENAME);
            $image_converted_folder = __DIR__ . "/upload/files/mysignature/".$userid."/".$req_id;
            //echo $image_converted_folder;exit;
            if (!file_exists($image_converted_folder)) {
		        mkdir($image_converted_folder, 0777, true);
	        }
            $new_image_name = $image_converted_folder."/".$image_name.'.jpg';
            $imagick->writeImages($new_image_name, true);
			$imagick->clear();
			$imagick->destroy();
            chmod($new_image_name, 0755);

    } else {
        echo "The file $pdf_name does not exist";
    }
}

$allowed_levels = array(9,8,7,0);

require_once('sys.includes.php');
include('header_no_left.php');
?>

<style>
    .main1disp {
        display: none !important;
    }
    /*custom*/
	#main1, html {
		background:#edebeb;
	}
    body{overflow-y:hidden;}
    #frame{
        position: relative;
		overflow-y: scroll;
		margin-top: 40px;
		max-height: 450px;
    }
	.ba-doc-frame {
		-webkit-box-shadow: 0px 0px 16px -12px rgba(0,0,0,1);
		-moz-box-shadow: 0px 0px 16px -12px rgba(0,0,0,1);
		box-shadow: 0px 0px 16px -12px rgba(0,0,0,1);
	}
    #frame img{
    z-index: 1; 
    /*position: absolute; */
    
    }
	#cc-mail-status {
		z-index: 9999;
	}
    .tools {
        z-index: 9998; 
        position: absolute;
        height:40px;
        width:100%;
		background: #edebeb;
		font-size: 14px;
		line-height: 38px;
		padding: 0 15px;
		text-align: center
    }
	.tools a {
	color: #121519;
    letter-spacing: 1px;
    word-spacing: 4px;
    padding: 0 8px;
	}
    .sign_positions{
        z-index: 9999; 
        position: absolute;
        top:100px;
        right:100px;
        width:250px;
    }
    .signature_pos{
        z-index: 4; 
        top:100px;
        right:100px;
        width:150px;
        height:40px;
        background:light blue;
        border:2px dotted red;
        min-width:150px;
        min-height:40px;
        max-width:200px;
        max-height:60px;
    }
    .sign_pos{
        position:absolute !important;
        background:yellow !important;
        min-width:150px;
        min-height:40px;
        /*max-width:200px;*/
        /*max-height:60px;*/
    }
    .sign_pos_date{
        background:#a0ccce !important;
    }
    .sign_pos_active,.sign_pos_date_active{
        border:2px dotted red !important;
    }
    .sign_pos_text{
        background:#f7ba61 !important;
    }
    .sign_pos_textarea{
        background:#b8f19b !important;
    }
    .sign_pos_active,.sign_pos_text_active{
        border:2px dotted red !important;
    }
    .no_padding{
        padding:0px !important;
    }
    .tools_section{
    height:100px;
    }
	.tools-wrap {
		position: relative;
	}
	.ba-page-no {
		font-size: 10px;
	}
	.close_icon{
	    float:right;
	}
  </style>
  
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</style>
<style>
    body {
  overflow: hidden;
}


/* Preloader */

#preloader {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;z
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
</style>
<style>
    .disnone{
        display:none;
    }
</style>



<!-- Preloader start-->
<div id="preloader">
  <div id="contentdiv">&nbsp;Loading...</div>
  <div id="status">&nbsp;</div>
</div>

<!--<img width="100%" src='https://unsplash.it/3000/3000/?random' />-->
<!-- Preloader start-->















<div id="main1">
    
    <div id="content"> 
    <div class="container">
        <div class="row">
	        <div class="col-md-12">
	        <div class="sign_positions">
                        <form action="draw_sign.php" id="sign_positions_form" method="POST">
                                <input type="hidden" id="drop_off_request_id" name="drop_off_request_id" value="<?php echo $req_id;?>">
                                <input type="hidden" id="pname" name="pname" value="<?php echo $pname;?>">
                                <input type="hidden" id="image_name" value="<?php echo $image_name.'.jpg';?>" name="image_name">
                                <input type="hidden" id="image_width" value="" name="image_width">
                                <input type="hidden" id="user_id" value="<?php echo $userid;?>" name="user_id">
                                <input type="hidden" id="no_of_pages" value="<?php echo $number_of_pdf_images;?>" name="no_of_pages">
                                <input type="submit" class="btn btn-primary disnone" id="savepos" value="SAVE POSITION" onclick="loadingmsg()">
                        </form>
                </div>
	                <div  class="tools-wrap">
                                <div  class="tools">
                                        
                                        <a href="#" id="sign-1" class="click_sign"> <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Signature</a> | 
                                        <a href="#" id="sign-date-1" class="click_sign_date"><i class="fa fa-calendar-o" aria-hidden="true"></i> Signed Date</a> |
                                        <a href="#" id="sign-text-1" class="click_sign_text"><i class="fa fa-file-text" aria-hidden="true"></i> Input Text</a> |    
                                        <!--<a href="#" id="sign-textarea-1" class="click_sign_textarea"><i class="fa fa-file-text-o" aria-hidden="true"></i> Textarea Input</a> | -->
                                        <a href="#"  onclick="saveposition()"><i class="fa fa-floppy-o" aria-hidden="true"></i>SAVE</a>                         
                                </div>
	                </div>
	                
	        </div>
       </div>
    </div>   
    <div class="container ba-doc-frame">
        <div class="row">
                <div class="col-md-12" id="frame">
                        <?php
                                if($number_of_pdf_images>1){
                                        for ($i = 0; $i < $number_of_pdf_images; $i++) {
                        ?>		
                                        <div class="ba-page-wrap">		
                                                <img src="<?php echo BASE_URI."upload/files/mysignature/".$userid."/".$req_id."/".$image_name."-".$i.".jpg"; ?>"  >
                                                <span class="pull-right ba-page-no"><?php echo $i+1 ." of ". $number_of_pdf_images;?></span>
					</div>
                        <?php
                                                       
                                        }
                                        
                                }else{
                                        
                        ?>
                        <div class="ba-page-wrap">
                                <img src="<?php echo BASE_URI."upload/files/mysignature/".$userid."/".$req_id."/".$image_name.'.jpg'; ?>" >
                        </div>
                        <?php
                        
                                }
                        ?>
                        
                        
                </div>
            </div>
        </div>
    </div>
     </div>
</div>

<!-- Modal -->

<div id="cc-mail-status" class="modal fade" role="dialog">

  <div class="modal-dialog">



    <!-- Modal content-->

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" onclick="modalclose()">&times;</button>

        <h4 class="modal-title">MicroHealth Send</h4>

      </div>

      <div class="modal-body">

		<?php echo isset($cc_status)? $cc_status : ''; ?>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"  onclick="modalclose()">Close</button>

      </div>

    </div>



  </div>

</div>
<?php
    include('footer.php');
?>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
function modalclose(){
    window.location.href="<?php echo BASE_URI.'requested_file.php';?>";
}

$(function() {
        $( ".click_sign" ).click(function(event) {
                event.preventDefault();
                var pos = $('#frame').offset();
                console.log("frame left "+parseFloat(pos.left));	
                console.log("frame top "+parseFloat(pos.top));
                //console.log("=================="+$(document).scrollTop());
                console.log($('#frame').scrollTop());
                var scroll_top = $('#frame').scrollTop()+250;
                var def_left_pos = 250;
                console.log("scroll_top "+scroll_top);
                console.log("def_left_pos "+def_left_pos);
                var sig_id = $(this).attr('id');
                $('.sign_pos').removeClass('sign_pos_active');
                var drag_id = $(this).attr('id');
                var new_drag_id = parseInt(drag_id.split("-")[1])+1;
                $(".click_sign").attr('id',"sign-"+new_drag_id);
                //dynamic hidden fields generation
                var sign_left_pos = 'sign_left_pos'+'-'+parseInt(drag_id.split("-")[1]);
                var sign_top_pos = 'sign_top_pos'+'-'+parseInt(drag_id.split("-")[1]);
                var sign_width = 'sign_width'+'-'+parseInt(drag_id.split("-")[1]);
                var sign_height = 'sign_height'+'-'+parseInt(drag_id.split("-")[1]);
                
                $('#sign_positions_form').append('<input type="hidden" id="'+sign_left_pos+'" name="'+sign_left_pos+'"><input type="hidden" id="'+sign_top_pos+'" name="'+sign_top_pos+'"><input type="hidden" id="'+sign_width+'" name="'+sign_width+'"><input type="hidden" id="'+sign_height+'" name="'+sign_height+'"><input type="hidden" class="signature_array-'+parseInt(drag_id.split("-")[1])+'" value="'+parseInt(drag_id.split("-")[1])+'" name="signature_array[]">');
                //left / top positions to hidden field
                $('#sign_left_pos-'+parseInt(drag_id.split("-")[1])).val(def_left_pos);
                $('#sign_top_pos-'+parseInt(drag_id.split("-")[1])).val(scroll_top);
                $('#sign_width-'+parseInt(drag_id.split("-")[1])).val(150);
                $('#sign_height-'+parseInt(drag_id.split("-")[1])).val(40);
                
                $( "<div class='ui-widget-content sign_pos sign_pos_active resizable' style='top:"+scroll_top+"px;left:"+def_left_pos+"px' id='"+sig_id+"'><a href='#' onclick='removediv("+parseInt(drag_id.split("-")[1])+",1)'><i class='fa fa-window-close close_icon' aria-hidden='true'></i></a><p>Sign Here "+parseInt(drag_id.split("-")[1])+"</p></div>" ).prependTo( "#frame" ).draggable({
                containment: 'parent',
                drag:function(ev, ui) {
                        $('.sign_pos').removeClass('sign_pos_active');
                        $(this).addClass('sign_pos_active');
                }, 
                stop:function(ev, ui) {
                        var offset = $(ui.helper).offset();
                        var pos = $(ui.helper).position();
                        console.log("offset left "+offset.left);	
                        console.log("pos left "+parseFloat(pos.left));	
                        console.log("offset top "+offset.top);	
                        console.log("pos top "+parseFloat(pos.top));
                        console.log("drag_id: "+drag_id);
                        var this_top = $(this).css("top");
                        console.log("this_top: "+this_top);
                        var this_left = $(this).css("left");
                        console.log("this_left: "+this_left);
                        var drag_new_id = $(ui.helper).attr("id");
                        drag_new_id = drag_new_id.split('-')[1]
                        console.log("drag_id: "+drag_new_id);
                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                        //console.log("offset left "+sign_left_pos);
                        //console.log("drag_new_id "+drag_new_id);
                        $('#sign_left_pos-'+drag_new_id).val(parseFloat(this_left));
                        $('#sign_top_pos-'+drag_new_id).val(parseFloat(this_top));
                        var signature_pos_width = $( "#"+drag_id ).width();
                        var signature_pos_height = $( "#"+drag_id ).height();
                        $('#sign_width-'+drag_new_id).val(signature_pos_width);
                        $('#sign_height-'+drag_new_id).val(signature_pos_height);
                        
                },
                
			
        });
        $(".resizable").resizable();
                
        });
        
        
        
        
        
        //manage sign pad move on mouse click 
        $("#frame div img").click(function(e) {
                //alert('frame click');
                //e.stopPropagation();
                var relativePosition = {
                left: e.pageX - $(document).scrollLeft() - $('#frame img').offset().left,
                top : e.pageY - $(document).scrollTop() - $('#frame img').offset().top
                };
                if ( $( ".sign_pos_active" ).length ){
                        drag_id = $('.sign_pos_active').attr('id');
                        drag_new_id = drag_id.split('-')[1]
                        console.log("click drag_id: "+drag_new_id);
                        if(drag_new_id=='date'){
                                drag_new_id = drag_id.split('-')[2]
                                console.log("drag_id: "+drag_new_id);
                                $('#sign-date-'+drag_new_id).css('top',relativePosition.top);
                                $('#sign-date-'+drag_new_id).css('left',relativePosition.left);
                                //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                //console.log("offset left "+sign_left_pos);
                                //console.log("drag_new_id "+drag_new_id);
                                $('#sign_left_pos_date-'+drag_new_id).val(relativePosition.left);
                                $('#sign_top_pos_date-'+drag_new_id).val(relativePosition.top);        
                        }else if(drag_new_id=='text'){
                                drag_new_id = drag_id.split('-')[2]
                                console.log("drag_id: "+drag_new_id);
                                $('#sign-text-'+drag_new_id).css('top',relativePosition.top);
                                $('#sign-text-'+drag_new_id).css('left',relativePosition.left);
                                //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                //console.log("offset left "+sign_left_pos);
                                //console.log("drag_new_id "+drag_new_id);
                                $('#sign_left_pos_text-'+drag_new_id).val(relativePosition.left);
                                $('#sign_top_pos_text-'+drag_new_id).val(relativePosition.top);    
                                $('#sign_width_text-'+drag_new_id).val($('.sign_pos_active').width());
                                $('#sign_height_text-'+drag_new_id).val($('.sign_pos_active').height());
                        }else if(drag_new_id=='textarea'){
                                drag_new_id = drag_id.split('-')[2]
                                console.log("drag_id: "+drag_new_id);
                                $('#sign-textarea-'+drag_new_id).css('top',relativePosition.top);
                                $('#sign-textarea-'+drag_new_id).css('left',relativePosition.left);
                                //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                //console.log("offset left "+sign_left_pos);
                                //console.log("drag_new_id "+drag_new_id);
                                $('#sign_left_pos_textarea-'+drag_new_id).val(relativePosition.left);
                                $('#sign_top_pos_textarea-'+drag_new_id).val(relativePosition.top);    
                                $('#sign_width_textarea-'+drag_new_id).val($('.sign_pos_active').width());
                                $('#sign_height_textarea-'+drag_new_id).val($('.sign_pos_active').height());
                        }else{
                                console.log("drag_id: "+drag_new_id);
                                $('#sign-'+drag_new_id).css('top',relativePosition.top);
                                $('#sign-'+drag_new_id).css('left',relativePosition.left);
                                //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                //console.log("offset left "+sign_left_pos);
                                //console.log("drag_new_id "+drag_new_id);
                                $('#sign_left_pos-'+drag_new_id).val(relativePosition.left);
                                $('#sign_top_pos-'+drag_new_id).val(relativePosition.top);
								$('#sign_width-'+drag_new_id).val($('.sign_pos_active').width());
                                $('#sign_height-'+drag_new_id).val($('.sign_pos_active').height());
                                console.log(relativePosition.left +', '+ relativePosition.top);
                        }
                }
               
        });
        
        
        $("#frame").mouseup(function(){
			if ( $( ".sign_pos_active" ).length ){
				
			drag_id = $('.sign_pos_active').attr('id');
            drag_new_id = drag_id.split('-')[1];
			if(drag_new_id=='date'){
			}
			else if(drag_new_id=='text'){
			    drag_new_id = drag_id.split('-')[2];
			    $('#sign_width_text-'+drag_new_id).val($('.sign_pos_active').width());
				$('#sign_height_text-'+drag_new_id).val($('.sign_pos_active').height());
			}
			else if(drag_new_id=='textarea'){
			    drag_new_id = drag_id.split('-')[2];
			    $('#sign_width_textarea-'+drag_new_id).val($('.sign_pos_active').width());
				$('#sign_height_textarea-'+drag_new_id).val($('.sign_pos_active').height());
			}
			else {
				$('#sign_width-'+drag_new_id).val($('.sign_pos_active').width());
				$('#sign_height-'+drag_new_id).val($('.sign_pos_active').height());
			}
			}
		});
        
        
        //activate divs
        $(document).on("click", ".sign_pos" , function(e) {
                
                e.stopPropagation();
                //alert('sign pos clicked');
                $('.sign_pos').removeClass('sign_pos_active');
                $(this).addClass('sign_pos_active');
        });
        
        $( ".click_sign_date" ).click(function(event) {
                event.preventDefault();
                var pos = $('#frame').offset();
                console.log("frame left "+parseFloat(pos.left));	
                console.log("frame top "+parseFloat(pos.top));
                //console.log("=================="+$(document).scrollTop());
                console.log($('#frame').scrollTop());
                var scroll_top = $('#frame').scrollTop()+300;
                var def_left_pos = 500;
                console.log("scroll_top "+scroll_top);
                console.log("def_left_pos "+def_left_pos);
                var sig_id = $(this).attr('id');
                $('.sign_pos').removeClass('sign_pos_active');
                var drag_id = $(this).attr('id');
                var new_drag_id = parseInt(drag_id.split("-")[2])+1;
                $(".click_sign_date").attr('id',"sign-date-"+new_drag_id);
                //dynamic hidden fields generation
                var sign_left_pos_date = 'sign_left_pos_date'+'-'+parseInt(drag_id.split("-")[2]);
                var sign_top_pos_date = 'sign_top_pos_date'+'-'+parseInt(drag_id.split("-")[2]);
                var sign_width_date = 'sign_width_date'+'-'+parseInt(drag_id.split("-")[2]);
                var sign_height_date = 'sign_height_date'+'-'+parseInt(drag_id.split("-")[2]);
                $('#sign_positions_form').append('<input type="hidden" id="'+sign_left_pos_date+'" name="'+sign_left_pos_date+'"><input type="hidden" id="'+sign_top_pos_date+'" name="'+sign_top_pos_date+'"><input type="hidden" id="'+sign_width_date+'" name="'+sign_width_date+'"><input type="hidden" id="'+sign_height_date+'" name="'+sign_height_date+'"><input type="hidden" class="signature_date_array-'+parseInt(drag_id.split("-")[2])+'" value="'+parseInt(drag_id.split("-")[2])+'" name="signature_date_array[]">');
                //left / top positions to hidden field
                $('#sign_left_pos_date-'+parseInt(drag_id.split("-")[2])).val(def_left_pos);
                $('#sign_top_pos_date-'+parseInt(drag_id.split("-")[2])).val(scroll_top);
                $('#sign_width_date-'+parseInt(drag_id.split("-")[2])).val(150);
                $('#sign_height_date-'+parseInt(drag_id.split("-")[2])).val(40);
                
                $( "<div class='ui-widget-content sign_pos sign_pos_date sign_pos_active' style='top:"+scroll_top+"px;left:"+def_left_pos+"px' id='"+sig_id+"'><a href='#' onclick='removediv("+parseInt(drag_id.split("-")[2])+",4)'><i class='fa fa-window-close close_icon' aria-hidden='true'></i></a><p>Signed date "+parseInt(drag_id.split("-")[2])+"</p></div>" ).prependTo( "#frame" ).draggable({
                containment: 'parent',
                drag:function(ev, ui) {
                        $('.sign_pos').removeClass('sign_pos_active');
                        $(this).addClass('sign_pos_active');
                }, 
                stop:function(ev, ui) {
                        var offset = $(ui.helper).offset();
                        var pos = $(ui.helper).position();
                        console.log("offset left "+offset.left);	
                        console.log("pos left "+parseFloat(pos.left));	
                        console.log("offset top "+offset.top);	
                        console.log("pos top "+parseFloat(pos.top));
                        console.log("drag_id: "+drag_id);
                        var this_top = $(this).css("top");
                        console.log("this_top: "+this_top);
                        var this_left = $(this).css("left");
                        console.log("this_left: "+this_left);
                        var drag_new_id = $(ui.helper).attr("id");
                        drag_new_id = drag_new_id.split('-')[2]
                        console.log("drag_new_id: "+drag_new_id);
                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                        //console.log("offset left "+sign_left_pos);
                        //console.log("drag_new_id "+drag_new_id);
                        $('#sign_left_pos_date-'+drag_new_id).val(parseFloat(this_left));
                        $('#sign_top_pos_date-'+drag_new_id).val(parseFloat(this_top));
                        var signature_pos_width = $( "#"+drag_id ).width();
                        var signature_pos_height = $( "#"+drag_id ).height();
                        $('#sign_width_date-'+drag_new_id).val(signature_pos_width);
                        $('#sign_height_date-'+drag_new_id).val(signature_pos_height);
                },
                
			
        });
                
        });
        
    //-----------------------------------------------------------------    
        
        
        $( ".click_sign_text" ).click(function(event) {
            event.preventDefault();
            var pos = $('#frame').offset();
            console.log("frame left "+parseFloat(pos.left));	
            console.log("frame top "+parseFloat(pos.top));
            //console.log("=================="+$(document).scrollTop());
            console.log($('#frame').scrollTop());
            var scroll_top = $('#frame').scrollTop()+350;
            var def_left_pos = 700;
            console.log("scroll_top "+scroll_top);
            console.log("def_left_pos "+def_left_pos);
            var sig_id = $(this).attr('id');
            $('.sign_pos').removeClass('sign_pos_active');
            var drag_id = $(this).attr('id');
            var new_drag_id = parseInt(drag_id.split("-")[2])+1;
            $(".click_sign_text").attr('id',"sign-text-"+new_drag_id);
            //dynamic hidden fields generation
            var sign_left_pos_text = 'sign_left_pos_text'+'-'+parseInt(drag_id.split("-")[2]);
            var sign_top_pos_text = 'sign_top_pos_text'+'-'+parseInt(drag_id.split("-")[2]);
            var sign_width_text = 'sign_width_text'+'-'+parseInt(drag_id.split("-")[2]);
            var sign_height_text = 'sign_height_text'+'-'+parseInt(drag_id.split("-")[2]);
            $('#sign_positions_form').append('<input type="hidden" id="'+sign_left_pos_text+'" name="'+sign_left_pos_text+'"><input type="hidden" id="'+sign_top_pos_text+'" name="'+sign_top_pos_text+'"><input type="hidden" id="'+sign_width_text+'" name="'+sign_width_text+'"><input type="hidden" id="'+sign_height_text+'" name="'+sign_height_text+'"><input type="hidden" class="signature_text_array-'+parseInt(drag_id.split("-")[2])+'" value="'+parseInt(drag_id.split("-")[2])+'" name="signature_text_array[]">');
            //left / top positions to hidden field
            $('#sign_left_pos_text-'+parseInt(drag_id.split("-")[2])).val(def_left_pos);
            $('#sign_top_pos_text-'+parseInt(drag_id.split("-")[2])).val(scroll_top);
            $('#sign_width_text-'+parseInt(drag_id.split("-")[2])).val(150);
            $('#sign_height_text-'+parseInt(drag_id.split("-")[2])).val(40);
            $( "<div class='ui-widget-content sign_pos sign_pos_text sign_pos_active resizable_text' style='top:"+scroll_top+"px;left:"+def_left_pos+"px' id='"+sig_id+"'><a href='#' onclick='removediv("+parseInt(drag_id.split("-")[2])+",2)'><i class='fa fa-window-close close_icon' aria-hidden='true'></i></a><p>Input Text "+parseInt(drag_id.split("-")[2])+"</p></div>" ).prependTo( "#frame" ).draggable({
                containment: 'parent',
                drag:function(ev, ui) {
                    $('.sign_pos').removeClass('sign_pos_active');
                    $(this).addClass('sign_pos_active');
                }, 
                stop:function(ev, ui) {
                    var offset = $(ui.helper).offset();
                    var pos = $(ui.helper).position();
                    console.log("offset left "+offset.left);	
                    console.log("pos left "+parseFloat(pos.left));	
                    console.log("offset top "+offset.top);	
                    console.log("pos top "+parseFloat(pos.top));
                    console.log("drag_id: "+drag_id);
                    var this_top = $(this).css("top");
                    console.log("this_top: "+this_top);
                    var this_left = $(this).css("left");
                    console.log("this_left: "+this_left);
                    var drag_new_id = $(ui.helper).attr("id");
                    drag_new_id = drag_new_id.split('-')[2]
                    console.log("drag_new_id: "+drag_new_id);
                    //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                    //console.log("offset left "+sign_left_pos);
                    //console.log("drag_new_id "+drag_new_id);
                    $('#sign_left_pos_text-'+drag_new_id).val(parseFloat(this_left));
                    $('#sign_top_pos_text-'+drag_new_id).val(parseFloat(this_top));
                    var signature_pos_width = $( "#"+drag_id ).width();
                    var signature_pos_height = $( "#"+drag_id ).height();
                    $('#sign_width_text-'+drag_new_id).val(signature_pos_width);
                    $('#sign_height_text-'+drag_new_id).val(signature_pos_height);
                },
            
            });
            $(".resizable_text").resizable({ handles: 'e, w'});
        });
        
        
    //------------------------------------------------------- 
        
            
    //-----------------------------------------------------------------    
        
        
        $( ".click_sign_textarea" ).click(function(event) {
            event.preventDefault();
            var pos = $('#frame').offset();
            console.log("frame left "+parseFloat(pos.left));	
            console.log("frame top "+parseFloat(pos.top));
            //console.log("=================="+$(document).scrollTop());
            console.log($('#frame').scrollTop());
            var scroll_top = $('#frame').scrollTop()+400;
            var def_left_pos = 700;
            console.log("scroll_top "+scroll_top);
            console.log("def_left_pos "+def_left_pos);
            var sig_id = $(this).attr('id');
            $('.sign_pos').removeClass('sign_pos_active');
            var drag_id = $(this).attr('id');
            var new_drag_id = parseInt(drag_id.split("-")[2])+1;
            $(".click_sign_textarea").attr('id',"sign-textarea-"+new_drag_id);
            //dynamic hidden fields generation
            var sign_left_pos_textarea = 'sign_left_pos_textarea'+'-'+parseInt(drag_id.split("-")[2]);
            var sign_top_pos_textarea = 'sign_top_pos_textarea'+'-'+parseInt(drag_id.split("-")[2]);
            var sign_width_textarea = 'sign_width_textarea'+'-'+parseInt(drag_id.split("-")[2]);
            var sign_height_textarea = 'sign_height_textarea'+'-'+parseInt(drag_id.split("-")[2]);
            $('#sign_positions_form').append('<input type="hidden" id="'+sign_left_pos_textarea+'" name="'+sign_left_pos_textarea+'"><input type="hidden" id="'+sign_top_pos_textarea+'" name="'+sign_top_pos_textarea+'"><input type="hidden" id="'+sign_width_textarea+'" name="'+sign_width_textarea+'"><input type="hidden" id="'+sign_height_textarea+'" name="'+sign_height_textarea+'"><input type="hidden" class="signature_textarea_array-'+parseInt(drag_id.split("-")[2])+'" value="'+parseInt(drag_id.split("-")[2])+'" name="signature_textarea_array[]">');
            //left / top positions to hidden field
            $('#sign_left_pos_textarea-'+parseInt(drag_id.split("-")[2])).val(def_left_pos);
            $('#sign_top_pos_textarea-'+parseInt(drag_id.split("-")[2])).val(scroll_top);
            $('#sign_width_textarea-'+parseInt(drag_id.split("-")[2])).val(150);
            $('#sign_height_textarea-'+parseInt(drag_id.split("-")[2])).val(40);
            $( "<div class='ui-widget-content sign_pos sign_pos_textarea sign_pos_active resizable_textarea' style='top:"+scroll_top+"px;left:"+def_left_pos+"px' id='"+sig_id+"'><a href='#' onclick='removediv("+parseInt(drag_id.split("-")[2])+",3)'><i class='fa fa-window-close close_icon' aria-hidden='true'></i></a><p>Textarea Input "+parseInt(drag_id.split("-")[2])+"</p></div>" ).prependTo( "#frame" ).draggable({
                containment: 'parent',
                drag:function(ev, ui) {
                    $('.sign_pos').removeClass('sign_pos_active');
                    $(this).addClass('sign_pos_active');
                }, 
                stop:function(ev, ui) {
                    var offset = $(ui.helper).offset();
                    var pos = $(ui.helper).position();
                    console.log("offset left "+offset.left);	
                    console.log("pos left "+parseFloat(pos.left));	
                    console.log("offset top "+offset.top);	
                    console.log("pos top "+parseFloat(pos.top));
                    console.log("drag_id: "+drag_id);
                    var this_top = $(this).css("top");
                    console.log("this_top: "+this_top);
                    var this_left = $(this).css("left");
                    console.log("this_left: "+this_left);
                    var drag_new_id = $(ui.helper).attr("id");
                    drag_new_id = drag_new_id.split('-')[2]
                    console.log("drag_new_id: "+drag_new_id);
                    //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                    //console.log("offset left "+sign_left_pos);
                    //console.log("drag_new_id "+drag_new_id);
                    $('#sign_left_pos_textarea-'+drag_new_id).val(parseFloat(this_left));
                    $('#sign_top_pos_textarea-'+drag_new_id).val(parseFloat(this_top));
                    var signature_pos_width = $( "#"+drag_id ).width();
                    var signature_pos_height = $( "#"+drag_id ).height();
                    $('#sign_width_textarea-'+drag_new_id).val(signature_pos_width);
                    $('#sign_height_textarea-'+drag_new_id).val(signature_pos_height);
                },
            
            });
            $(".resizable_textarea").resizable();
        });
        
        
    //------------------------------------------------------- 
        
        
        
});
$(document).ready(function(){
        
        var frame_width = $( "#frame" ).width();
        var image_height = $( "#frame img" ).height();
        var image_width = $( "#frame img" ).width();
        var frame_height = (frame_width*image_height)/image_width;
        console.log('image_height : '+image_height);
        console.log('frame_height : '+frame_height);
        $('#image_width').val(frame_width);
        $('#frame img').width(frame_width);
        ///$('#frame').height(frame_height);
        $(".click_sign1").draggable({
                helper: function(){
                // return a custom element to be used for dragging
                return $("<div/>",{ 
                        //text: $(this).text(),
                        text: "Sign here",
                        class:"signature_pos",
                        id:$(this).attr('id'),
                        
                        })
                }, // use a clone for the visual effect
                revert: false,
                stop:function(ev, ui) {
                        
                        var drag_id = $(this).attr('id');
                        var sign_left_pos = 'sign_left_pos'+'-'+parseInt(drag_id.split("-")[1]);
                        var sign_top_pos = 'sign_top_pos'+'-'+parseInt(drag_id.split("-")[1]);
                        var sign_width = 'sign_width'+'-'+parseInt(drag_id.split("-")[1]);
                        var sign_height = 'sign_height'+'-'+parseInt(drag_id.split("-")[1]);
                        $('#sign_positions_form').append('<input type="hidden" id="'+sign_left_pos+'" name="'+sign_left_pos+'"><input type="hidden" id="'+sign_top_pos+'" name="'+sign_top_pos+'"><input type="hidden" id="'+sign_width+'" name="'+sign_width+'"><input type="hidden" id="'+sign_height+'" name="'+sign_height+'"><input type="hidden" value="'+parseInt(drag_id.split("-")[1])+'" name="signature_array[]">');
                        var new_drag_id = parseInt(drag_id.split("-")[1])+1;
                        console.log(new_drag_id);
                        //values to text boxes
                        var offset = $(ui.helper).offset();
                        var pos = $(ui.helper).position();
					//--------------------------------
					var boffset = $('#frame').offset();
					var bxPos = offset.left;
					var byPos = offset.top;
					//alert(ev.clientY - $(ev.target).offset().top);
					//alert($(ev.target).offset().top);
					//---------------------------------
                        console.log("b offset left "+offset.left);	
                        console.log("b pos left "+parseFloat(pos.left));	
                        console.log("b offset top "+offset.top);	
                        console.log("b pos top "+parseFloat(pos.top));	
                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                        //console.log("offset left "+sign_left_pos);	
                        $('#'+sign_left_pos).val(pos.left);
                        $('#'+sign_top_pos).val(pos.top);
                        
                        
                        $(".click_sign").attr('id',"sign-"+new_drag_id);
                        console.log('drag_id: '+drag_id);
                        var signature_pos_width = $( "#"+drag_id ).width();
                        var signature_pos_height = $( "#"+drag_id ).height();
                        console.log("signature_pos_width "+signature_pos_width);	
                        console.log("signature_pos_height "+signature_pos_height);	
                        $('#'+sign_width).val(signature_pos_width);
                        $('#'+sign_height).val(signature_pos_height);
                        //When an existiung object is dragged
                        $('.signature_pos').draggable({
                                containment: 'parent',
                                stop:function(ev, ui) {
                                        var offset = $(ui.helper).offset();
                                        var pos = $(ui.helper).position();
                                        console.log("offset left "+offset.left);	
                                        console.log("pos left "+parseFloat(pos.left));	
                                        console.log("offset top "+offset.top);	
                                        console.log("pos top "+parseFloat(pos.top));
                                        console.log("drag_id: "+drag_id);
                                        var this_top = $(this).css("top");
                                        console.log("this_top: "+this_top);
                                        var drag_new_id = $(ui.helper).attr("id");
                                        drag_new_id = drag_new_id.split('-')[1]
                                        console.log("drag_id: "+drag_new_id);
                                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                        //console.log("offset left "+sign_left_pos);
                                        //console.log("drag_new_id "+drag_new_id);
                                        $('#sign_left_pos-'+drag_new_id).val(pos.left);
                                        $('#sign_top_pos-'+drag_new_id).val(pos.top);
                                        var signature_pos_width = $( "#"+drag_id ).width();
                                        var signature_pos_height = $( "#"+drag_id ).height();
                                        $('#sign_width-'+drag_new_id).val(signature_pos_width);
                                        $('#sign_height-'+drag_new_id).val(signature_pos_height);
                                        
                                },
                                
					
                        });	
                        $('.signature_pos').resizable({
                                containment: "#frame",
                                minHeight: 40,
                                minWidth: 150,
                                maxHeight: 60,
                                maxWidth: 200,
                                //When first dragged
                                stop:function(ev, ui) {
                                        $('.draggable').css("min-width",'150px');
                                        $('.draggable').css("min-height",'40px');
                                        var drag_new_id = $(ui.helper).attr("id");
                                        drag_new_id = drag_new_id.split('-')[1]
                                        console.log("drag_id: "+drag_new_id);
                                        var width = ui.size.width;
                                        var height = ui.size.height;
                                        $('#sign_width-'+drag_new_id).val(width);
                                        $('#sign_height-'+drag_new_id).val(height);
                                        console.log(height+"----"+width);
                                }
                        
                        });			
                }
        });
        $( window ).resize(function() {
                //location.reload();
        });
        $(".click_date_of_sign").draggable({
                helper: function(){
                // return a custom element to be used for dragging
                return $("<div/>",{ 
                        //text: $(this).text(),
                        text: "Signed date here",
                        class:"signature_pos",
                        id:$(this).attr('id')

                        })
                }, // use a clone for the visual effect
                revert: false,
                stop:function(ev, ui) {
                        
                        var drag_id = $(this).attr('id');
                        var sign_left_pos_date = 'sign_left_pos_date'+'-'+parseInt(drag_id.split("-")[2]);
                        var sign_top_pos_date = 'sign_top_pos_date'+'-'+parseInt(drag_id.split("-")[2]);
                        var sign_width_date = 'sign_width_date'+'-'+parseInt(drag_id.split("-")[2]);
                        var sign_height_date = 'sign_height_date'+'-'+parseInt(drag_id.split("-")[2]);
                        $('#sign_positions_form').append('<input type="hidden" id="'+sign_left_pos_date+'" name="'+sign_left_pos_date+'"><input type="hidden" id="'+sign_top_pos_date+'" name="'+sign_top_pos_date+'"><input type="hidden" id="'+sign_width_date+'" name="'+sign_width_date+'"><input type="hidden" id="'+sign_height_date+'" name="'+sign_height_date+'"><input type="hidden" value="'+parseInt(drag_id.split("-")[2])+'" name="signature_date_array[]">');
                        var new_drag_id = parseInt(drag_id.split("-")[2])+1;
                        console.log(new_drag_id);
                        //values to text boxes
                        var offset = $(ui.helper).offset();
                        var pos = $(ui.helper).position();
                        console.log("offset left "+offset.left);	
                        console.log("pos left "+parseFloat(pos.left));	
                        console.log("offset top "+offset.top);	
                        console.log("pos top "+parseFloat(pos.top));	
                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                        //console.log("offset left "+sign_left_pos);	
                        $('#'+sign_left_pos_date).val(pos.left);
                        $('#'+sign_top_pos_date).val(pos.top);
                        
                        
                        $(".click_date_of_sign").attr('id',"sign-date-"+new_drag_id);
                        var signature_pos_width = $( "#"+drag_id ).width();
                        var signature_pos_height = $( "#"+drag_id ).height();
                        $('#'+sign_width_date).val(signature_pos_width);
                        $('#'+sign_height_date).val(signature_pos_height);
                        //When an existiung object is dragged
                        $('.signature_pos').draggable({
                                containment: 'parent',
                                stop:function(ev, ui) {
                                        var offset = $(ui.helper).offset();
                                        var pos = $(ui.helper).position();
                                        console.log("offset left "+offset.left);	
                                        console.log("pos left "+parseFloat(pos.left));	
                                        console.log("offset top "+offset.top);	
                                        console.log("pos top "+parseFloat(pos.top));	
                                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                        //console.log("offset left "+sign_left_pos);	
                                        
                                        var drag_new_id = $(ui.helper).attr("id");
                                        drag_new_id = drag_new_id.split('-')[2]
                                        console.log("drag_id: "+drag_new_id);
                                        //var sign_left_pos =  parseFloat(offset.left) - parseFloat(frame_left);
                                        //console.log("offset left "+sign_left_pos);
                                        console.log('#sign_left_pos_date-'+drag_new_id);
                                        console.log('#sign_top_pos_date-'+drag_new_id);
                                        $('#sign_left_pos_date-'+drag_new_id).val(pos.left);
                                        $('#sign_top_pos_date-'+drag_new_id).val(pos.top);
                                        var signature_pos_width = $( "#"+drag_id ).width();
                                        var signature_pos_height = $( "#"+drag_id ).height();
                                        $('#sign_width_date-'+drag_new_id).val(signature_pos_width);
                                        $('#sign_height_date-'+drag_new_id).val(signature_pos_height);
                                }					
                        });	
                        $('.signature_pos').resizable({
                                containment: "#frame",
                                minHeight: 40,
                                minWidth: 150,
                                maxHeight: 60,
                                maxWidth: 200,
                                //When first dragged
                                stop:function(ev, ui) {
                                        $('.draggable').css("min-width",'150px');
                                        $('.draggable').css("min-height",'40px');
                                        
                                        var drag_new_id = $(ui.helper).attr("id");
                                        drag_new_id = drag_new_id.split('-')[2]
                                        console.log("drag_id: "+drag_new_id);
                                        var width = ui.size.width;
                                        var height = ui.size.height;
                                        $('#sign_width_date-'+drag_new_id).val(width);
                                        $('#sign_height_date-'+drag_new_id).val(height);
                                        console.log(height+"----"+width);
                                }
                        
                        });			
                }
        });
        
        
  $("#frame").droppable({
    accept: "a",
    drop: function(event, ui) {
        $(this).append($("ui.draggable").clone());
      //you might want to reset the css using attr("style","")
      ui.helper.clone().appendTo(this); // actually append a clone of helper to the droppable
      
    }
  }); 
  $().addclass
  
   <?php if($_GET['mail_status']){?>
        $('#main1').fadeIn();
        $('#status').fadeOut();
        $('#preloader').fadeOut();
        $("#cc-mail-status").modal("toggle");
        $("#main1").removeClass('main1disp').addClass('main1disp').trigger('change');
    <?php }?>
  });	
  
  
    
    function loadingmsg(){
        $('#contentdiv').html('Please wait...');
        $('#main1').fadeOut();
        $('#status').fadeIn(); 
        $('#preloader').fadeIn('slow');
    }
    
    
   // -----------------------------------------
    
    function saveposition(){
        if($('input[name="signature_array[]"]').val()==undefined){
            alert('Please add least one signature box');
        }else{
            $('#savepos').click();
        }
    }
    
    
    function removediv(argument,divtype){
        
        if(divtype==1){
            if (confirm('Are you sure want to delete the Sign Here '+argument)) {
                if($('#sign-'+argument).remove()){
                    $('#sign_left_pos-'+argument).remove();
                    $('#sign_top_pos-'+argument).remove();
                    $('#sign_width-'+argument).remove();
                    $('#sign_height-'+argument).remove();
                    $('.signature_array-'+argument).remove();
                }
            }
        }else if(divtype==2){
            if (confirm('Are you sure want to delete the input Text '+argument)) {
                if($('#sign-text-'+argument).remove()){
                    $('#sign_left_pos_text-'+argument).remove();
                    $('#sign_top_pos_text-'+argument).remove();
                    $('#sign_width_text-'+argument).remove();
                    $('#sign_height_text-'+argument).remove();
                    $('.signature_text_array-'+argument).remove();
                }
            }
        }else if(divtype==3){
            if (confirm('Are you sure want to delete the Textarea Input '+argument)) {
                if($('#sign-textarea-'+argument).remove()){
                    $('#sign_left_pos_textarea-'+argument).remove();
                    $('#sign_top_pos_textarea-'+argument).remove();
                    $('#sign_width_textarea-'+argument).remove();
                    $('#sign_height_textarea-'+argument).remove();
                    $('.signature_textarea_array-'+argument).remove();
                }
            }
        }else{
            if (confirm('Are you sure want to delete the Signed Date '+argument)) {
                if($('#sign-date-'+argument).remove()){
                    $('#sign_left_pos_date-'+argument).remove();
                    $('#sign_top_pos_date-'+argument).remove();
                    $('#sign_width_date-'+argument).remove();
                    $('#sign_height_date-'+argument).remove();
                    $('.signature_date_array-'+argument).remove();
                }
            }
        }
        
        
        
        
       
        
    }
    
    
    
    
    
    
    
    
    
    
    //----------------------------------------
  
  
    
    $(document).ready(function() {
      $("#preloader").fadeOut();
      $('#dltid').addClass('disnone');
    });
</script>


