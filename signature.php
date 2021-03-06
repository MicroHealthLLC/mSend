
<style type="text/css">
  #sig-canvas {
    border: 2px dotted #CCCCCC;
    border-radius: 15px;
    cursor: crosshair;
  }
</style>


  <!-- Content -->
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h1>E-Signature</h1>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <canvas id="sig-canvas" width="535" height="160">
          Get a better browser, bro.
        </canvas>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <button class="btn btn-primary" id="sig-submitBtn">Submit Signature</button>
        <button class="btn btn-default" id="sig-clearBtn">Clear Signature</button>
      </div>
    </div>
   <!--  <br/>
    <div class="row">
      <div class="col-md-12">
        <textarea id="sig-dataUrl" class="form-control" rows="5" style="width: 535px;">Data URL for your signature will go here!</textarea>
      </div>
    </div>
    <br/>
    <div class="row">
      <div class="col-md-12">
        <img id="sig-image" src="" alt="Your signature will go here!"/>
      </div>
    </div> -->
  </div>


<script type="text/javascript">
  (function() {
  window.requestAnimFrame = (function(callback) {
    return window.requestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.oRequestAnimationFrame ||
      window.msRequestAnimaitonFrame ||
      function(callback) {
        window.setTimeout(callback, 1000 / 60);
      };
  })();

  var canvas = document.getElementById("sig-canvas");
  var ctx = canvas.getContext("2d");
  ctx.strokeStyle = "#222222";
  ctx.lineWidth = 4;

  var drawing = false;
  var mousePos = {
    x: 0,
    y: 0
  };
  var lastPos = mousePos;

  canvas.addEventListener("mousedown", function(e) {
    drawing = true;
    lastPos = getMousePos(canvas, e);
  }, false);

  canvas.addEventListener("mouseup", function(e) {
    drawing = false;
  }, false);

  canvas.addEventListener("mousemove", function(e) {
    mousePos = getMousePos(canvas, e);
  }, false);

  // Add touch event support for mobile
  canvas.addEventListener("touchstart", function(e) {

  }, false);

  canvas.addEventListener("touchmove", function(e) {
    var touch = e.touches[0];
    var me = new MouseEvent("mousemove", {
      clientX: touch.clientX,
      clientY: touch.clientY
    });
    canvas.dispatchEvent(me);
  }, false);

  canvas.addEventListener("touchstart", function(e) {
    mousePos = getTouchPos(canvas, e);
    var touch = e.touches[0];
    var me = new MouseEvent("mousedown", {
      clientX: touch.clientX,
      clientY: touch.clientY
    });
    canvas.dispatchEvent(me);
  }, false);

  canvas.addEventListener("touchend", function(e) {
    var me = new MouseEvent("mouseup", {});
    canvas.dispatchEvent(me);
  }, false);

  function getMousePos(canvasDom, mouseEvent) {
    var rect = canvasDom.getBoundingClientRect();
    return {
      x: mouseEvent.clientX - rect.left,
      y: mouseEvent.clientY - rect.top
    }
  }
  

  function getTouchPos(canvasDom, touchEvent) {
    var rect = canvasDom.getBoundingClientRect();
    return {
      x: touchEvent.touches[0].clientX - rect.left,
      y: touchEvent.touches[0].clientY - rect.top
    }
  }

  function renderCanvas() {
    if (drawing) {
      ctx.moveTo(lastPos.x, lastPos.y);
      ctx.lineTo(mousePos.x, mousePos.y);
      ctx.stroke();
      lastPos = mousePos;
    }
  }

  // Prevent scrolling when touching the canvas
  document.body.addEventListener("touchstart", function(e) {
    if (e.target == canvas) {
      e.preventDefault();
    }
  }, false);
  document.body.addEventListener("touchend", function(e) {
    if (e.target == canvas) {
      e.preventDefault();
    }
  }, false);
  document.body.addEventListener("touchmove", function(e) {
    if (e.target == canvas) {
      e.preventDefault();
    }
  }, false);

  (function drawLoop() {
    requestAnimFrame(drawLoop);
    renderCanvas();
  })();


  function clearCanvas() {
    canvas.width = canvas.width;
  }

//vsus
function trimCanvas(c) {
    var ctx = c.getContext('2d'),
        copy = document.createElement('canvas').getContext('2d'),
        pixels = ctx.getImageData(0, 0, c.width, c.height),
        l = pixels.data.length,
        i,
        bound = {
            top: null,
            left: null,
            right: null,
            bottom: null
        },
        x, y;
    
    // Iterate over every pixel to find the highest
    // and where it ends on every axis ()
    for (i = 0; i < l; i += 4) {
        if (pixels.data[i + 3] !== 0) {
            x = (i / 4) % c.width;
            y = ~~((i / 4) / c.width);

            if (bound.top === null) {
                bound.top = y;
            }

            if (bound.left === null) {
                bound.left = x;
            } else if (x < bound.left) {
                bound.left = x;
            }

            if (bound.right === null) {
                bound.right = x;
            } else if (bound.right < x) {
                bound.right = x;
            }

            if (bound.bottom === null) {
                bound.bottom = y;
            } else if (bound.bottom < y) {
                bound.bottom = y;
            }
        }
    }
    
    // Calculate the height and width of the content
    var trimHeight = bound.bottom - bound.top,
        trimWidth = bound.right - bound.left,
        trimmed = ctx.getImageData(bound.left, bound.top, trimWidth, trimHeight);

    copy.canvas.width = trimWidth;
    copy.canvas.height = trimHeight;
    copy.putImageData(trimmed, 0, 0);

    // Return trimmed canvas
    return copy.canvas;
}
//vsus end






  // Set up the UI
  var sigText = document.getElementById("sig-dataUrl");
  var sigImage = document.getElementById("sig-image");
  var clearBtn = document.getElementById("sig-clearBtn");
  var submitBtn = document.getElementById("sig-submitBtn");
  clearBtn.addEventListener("click", function(e) {
    clearCanvas();
    // sigText.innerHTML = "Data URL for your signature will go here!";
    // sigImage.setAttribute("src", "");
  }, false);
  submitBtn.addEventListener("click", function(e) {

    var trimmedCanvas = trimCanvas(canvas);

    var dataUrl = trimmedCanvas.toDataURL();
    //console.log(dataUrl);
    // sigText.innerHTML = dataUrl;
    // sigImage.setAttribute("src", dataUrl);

    // Split the base64 string in data and contentType
    var block = dataUrl.split(";");
    // Get the content type of the image
    var contentType = block[0].split(":")[1];// In this case "image/gif"
    // get the real base64 content of the file
    var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...."
    // console.log(realData);
    // Convert it to a blob to upload
    // var blob = b64toBlob(realData, contentType);
    
    var aa=isCanvasBlank(document.getElementById("sig-canvas"));
    if(aa){
        alert('The canwas area is empty please draw new signature');
    }else{
        if($('#sig #pageid').val()=='sign_document'){
            drawnewsignature(dataUrl);
        }else{
            savepic(realData,$('#uid').val());
        }
    }
        
  }, false);

})();


function savepic(argument,id) {
    var doc_sign_page=false;
    var sigfile=false;
    if($('#sigmodal').find('input[name="doc_sign_page"]').val()!=undefined){
        doc_sign_page=true;
    }
    if($('#sigmodal').find('input[name="sigfile"]').val()!=undefined){
        sigfile=true;
    }
    
    $.ajax({
        url: 'save_sign.php',
        data: { 'img_data':argument,'user_id_mic':id ,'doc_sign_page':doc_sign_page},
        type: 'post',
        dataType: 'json',
        async: false,
        success:function(arg){
          if(arg.status==true){
            $('#sig').modal('toggle');
            if(sigfile==false){
                $('.sig1').prop("checked", true).trigger('change');
                signaturefun(1);
                var sign_pad_id = $('#sign_pad_id').val();
                var sign_pad_width = $('#sign_pad_width').val();
                $('#sigtype').val(2);
                var img_src = '<?php echo BASE_URI;?>img/avatars/tempsignature/<?php echo $this_current_id;?>/temp/<?php echo $this_current_id;?>.png?ver='+ 1+ Math.floor(Math.random() * 6);
                $('#'+sign_pad_id).html('<img width="'+sign_pad_width+'" src="'+img_src+'">');
            }else{
                chksignaturestatus(3);
                // renderimg();
            }
          }
        }
    });
}

function isCanvasBlank(canvas) {
  const context = canvas.getContext('2d');

  const pixelBuffer = new Uint32Array(
    context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
  );

  return !pixelBuffer.some(color => color !== 0);
}
</script>
