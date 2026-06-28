<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<script type="text/javascript">
var oDoc, sDefTxt;

function initDoc() {
  oDoc = document.getElementById("textBox");
  sDefTxt = oDoc.innerHTML;
  document.execCommand('styleWithCSS', false, 'True');
  //if (document.compForm.switchMode.checked) { setDocMode(true); }
}

function formatDoc(sCmd, sValue) {
  if (validateMode()) { document.execCommand(sCmd, false, sValue); oDoc.focus(); }
}

function validateMode() {
//  if (!document.compForm.switchMode.checked) { return true ; }
//  alert("Uncheck \"Show HTML\".");
//  oDoc.focus();
//  return false;
    return true;
}

function insertImage(image_src){
    image_title = prompt('نام عکس را وارد کنید','');
    image_width = prompt('عرض عکس (پیکسل) را وارد کنید','');
    html = '<img src="'+image_src+'" style="width:'+image_width+'px;" alt="'+image_title+'" />';
    formatDoc('insertHTML',html);
}
function selectImage(){
    document.getElementById("image_select").classList.remove("hide");
}
function select_content(item){
    id = item.getElementsByClassName("id_span")[0].innerHTML;
    image_src = item.getElementsByTagName("img")[0].src;
    document.getElementById("image_select").classList.add("hide");
    insertImage(image_src);
    return;
}
</script>
<style type="text/css">
@font-face {
font-family: 'wyekan';
src:url('fonts/wYekan.eot');
src:url('fonts/wYekan.eot') format('embedded-opentype'),
url('fonts/wYekan.woff') format('woff'),
url('fonts/wYekan.ttf') format('truetype');
font-weight: normal;
font-style: normal;
}

.intLink { cursor: pointer; }
img.intLink { border: 0; }
#toolBar1 select{font-size:14px;width:auto;float:none;}
#toolBar1 label{font-size:14px;}
#toolBar1 input[type="color"]{width:60px;float:none;margin:0 6px;height:18px;position:relative;bottom:-3px;}
#textBox {
  max-width: 960px;
  height: 500px;
  border: 1px #000000 solid;
  padding: 12px;
  overflow: scroll;
  direction:rtl;
  font-family:wyekan;
  text-align:right;
}
#textBox ol li{
    list-style: decimal;
}
#textBox ul li{
    list-style: disc;
}

#textBox #sourceText {
  padding: 0;
  margin: 0;
  min-width: 498px;
  min-height: 200px;
}
#editMode label { cursor: pointer; }
#toolbar2 img{width:20px;}
#image_select{
    position:fixed;
    width:70%;
    height:60%;
    border:2px solid black;
    z-index:10;
    top:20%;
    right:15%;
    padding:10px;
    border-radius:10px;
    background-color:white;
    box-shadow:0 0 0 15px #fff;
    overflow-y:scroll;
}
</style>

<form class="detail" name="compForm" method="post" action="<?php echo $URL; ?>" onsubmit="if(validateMode()){this.content_text.value=oDoc.innerHTML;return true;}return false;">
<input type="hidden" name="content_text">
<div id="toolBar1">
<select onchange="formatDoc('formatblock',this[this.selectedIndex].value);this.selectedIndex=0;">
<option selected>- نوع متن -</option>
<option value="h1">Title 1 &lt;h1&gt;</option>
<option value="h2">Title 2 &lt;h2&gt;</option>
<option value="h3">Title 3 &lt;h3&gt;</option>
<option value="h4">Title 4 &lt;h4&gt;</option>
<option value="h5">Title 5 &lt;h5&gt;</option>
<option value="h6">Subtitle &lt;h6&gt;</option>
<option value="p">Paragraph &lt;p&gt;</option>
<option value="pre">Preformatted &lt;pre&gt;</option>
</select>
<select onchange="formatDoc('fontname',this[this.selectedIndex].value);this.selectedIndex=0;">
<option class="heading" selected>- قلم -</option>
<option>Arial</option>
<option>Times New Roman</option>
<option>یکان</option>
</select>
<select onchange="formatDoc('fontsize',this[this.selectedIndex].value);this.selectedIndex=0;">
<option class="heading" selected>- اندازه قلم -</option>
<option value="1">بسیار کوچک</option>
<option value="2">کوچک</option>
<option value="3">معمولی</option>
<option value="4">میانه</option>
<option value="5">بزرگ</option>
<option value="6">بسیار بزرگ</option>
<option value="7">حداکثر</option>
</select>
<!--<select onchange="formatDoc('forecolor',this[this.selectedIndex].value);this.selectedIndex=0;">
<option class="heading" selected>- رنگ -</option>
<option value="rgb(255,0,0)">Red</option>
<option value="rgb(0,0,255)">Blue</option>
<option value="green">Green</option>
<option value="black">Black</option>
</select>-->
<label>رنگ متن:</label>
<input type="color" onchange="formatDoc('forecolor',this.value);this.value='#000000';">
<label>رنگ پس‌زمینه متن:</label>
<input type="color" onchange="formatDoc('backcolor',this.value);this.value='#ffffff';" value="#ffffff">

<!--<select onchange="formatDoc('backcolor',this[this.selectedIndex].value);this.selectedIndex=0;">
<option class="heading" selected>- پس‌زمینه متن -</option>
<option value="red">Red</option>
<option value="green">Green</option>
<option value="black">Black</option>
</select>-->
</div>
<div id="toolBar2">
<img class="intLink" title="Clean" onclick="if(validateMode()&&confirm('آیا مطمئنید?')){oDoc.innerHTML=sDefTxt};" src="<?php echo $s; ?>img/TEI/clean.gif" />
<!--<img class="intLink" title="Print" onclick="printDoc();" src="<?php echo $s; ?>img/TEI/print.png">-->
<img class="intLink" title="Redo" onclick="formatDoc('redo');" src="<?php echo $s; ?>img/TEI/redo.gif" />
<img class="intLink" title="Undo" onclick="formatDoc('undo');" src="<?php echo $s; ?>img/TEI/undo.gif" />
<img class="intLink" title="Remove formatting" onclick="formatDoc('removeFormat')" src="<?php echo $s; ?>img/TEI/remove_format.png">
<img class="intLink" title="Bold" onclick="formatDoc('bold');" src="<?php echo $s; ?>img/TEI/bold.gif" />
<img class="intLink" title="Italic" onclick="formatDoc('italic');" src="<?php echo $s; ?>img/TEI/italic.gif" />
<img class="intLink" title="Underline" onclick="formatDoc('underline');" src="<?php echo $s; ?>img/TEI/underline.gif" />
<img class="intLink" title="Right align" onclick="formatDoc('justifyright');" src="<?php echo $s; ?>img/TEI/align_right.gif" />
<img class="intLink" title="Center align" onclick="formatDoc('justifycenter');" src="<?php echo $s; ?>img/TEI/align_center.gif" />
<img class="intLink" title="Left align" onclick="formatDoc('justifyleft');" src="<?php echo $s; ?>img/TEI/align_left.gif" />
<img class="intLink" title="Numbered list" onclick="formatDoc('insertorderedlist');" src="<?php echo $s; ?>img/TEI/list_numbered.gif" />
<img class="intLink" title="Dotted list" onclick="formatDoc('insertunorderedlist');" src="<?php echo $s; ?>img/TEI/list_dotted.gif" />
<!--<img class="intLink" title="Quote" onclick="formatDoc('formatblock','blockquote');" src="<?php echo $s; ?>img/TEI/quote.gif" />-->
<img class="intLink" title="Add indentation" onclick="formatDoc('indent');" src="<?php echo $s; ?>img/TEI/indent_add.gif" />
<img class="intLink" title="Delete indentation" onclick="formatDoc('outdent');" src="<?php echo $s; ?>img/TEI/indent_remove.gif" />
<img class="intLink" title="Hyperlink" onclick="var sLnk=prompt('آدرس مقصد را وارد کنید:','http:\/\/');if(sLnk&&sLnk!=''&&sLnk!='http://'){formatDoc('createlink',sLnk)}" src="<?php echo $s; ?>img/TEI/hyperlink.gif" />
<img class="intLink" title="Insert Image" onclick="selectImage()" src="<?php echo $s; ?>img/TEI/image.gif" />
<!--<img class="intLink" title="Cut" onclick="formatDoc('cut');" src="<?php echo $s; ?>img/TEI/cut.gif" />
<img class="intLink" title="Copy" onclick="formatDoc('copy');" src="<?php echo $s; ?>img/TEI/copy.gif" />
<img class="intLink" title="Paste" onclick="formatDoc('paste');" src="<?php echo $s; ?>img/TEI/paste.gif" />-->
</div>
<div id="textBox" contenteditable="true">
    <?php $content = getVarFromDB($tb,"content","id",$_SESSION[$cf]->id);echo $content; ?>
</div>
<!--<p id="editMode"><input type="checkbox" name="switchMode" id="switchBox" onchange="setDocMode(this.checked);" /> <label for="switchBox">Show HTML</label></p>-->
<br><p><input class="btn" type="submit" name="edit" value="ثبت" /></p>
</form>
<script type="text/javascript">
    initDoc();
</script>
<div id="image_select" class="hide">
    <h3 class="tac">تصویر مورد نظر خود را انتخاب کنید</h2><br>
    <div class="images multi">
<?php
    $st = "SELECT id,file_name FROM content WHERE del_flag = 0 AND type = 'Image' ORDER BY id DESC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $dir = $s."content/";
    $onc = "select_content(this)";
    while($row = $res->fetch_assoc())
    {
        $file = $dir.$row["file_name"];
?>
            <div class="option img" onclick="<?php echo $onc; ?>">
                <img src="<?php echo $file; ?>" />
                <div class="btn mid"></div>
                <span class="id_span hide"><?php echo $row["id"]; ?></span>
            </div>
<?php
    }
?>
</div>
</div>        
<?php
        }
    }
?>