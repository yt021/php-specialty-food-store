ready_uploaddrops(document);
function ready_uploaddrops(item){
    uploaddrops = item.getElementsByClassName('upload');
    for (i=0;i<uploaddrops.length;i++){
        uploaddrop = uploaddrops[i];
        uploadevents = ['dragenter', 'dragover', 'dragleave', 'drop'];
        uploadevents.forEach(eventname => {uploaddrop.addEventListener(eventname,prv_def,false);});
        ['dragenter', 'dragover'].forEach(eventName => {
          uploaddrop.addEventListener(eventName, ready, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
          uploaddrop.addEventListener(eventName, unready, false);
        });
        uploaddrop.addEventListener('drop', handleDrop, false);
        uploaddrop.getElementsByTagName('input')[0].addEventListener('change',handleClick,false);
    }
}

function prv_def(e){
  e.preventDefault();
  e.stopPropagation();
}
function ready(e){
    this.classList.add('ready');
}
function unready(e){
    this.classList.remove('ready');
}

function handleDrop(e){
    dt = e.dataTransfer;
    files = dt.files;
    
    input = this.getElementsByTagName('input')[0];
    input.files = files;
    handleInput(input);
    return;
}
function handleClick(){
    input = this;
    handleInput(input);
    return;
}
function handleInput(input){
    
    file = input.files[0];
    label = input.parentNode.getElementsByTagName('label')[0];
    label.innerHTML = "فایل‌ را انتخاب کنید یا در این قسمت رها کنید .";
    fs = file.size;
    size_limit = 3;     //MegaBytes
    if(fs > size_limit*1024*1024){
        label.innerHTML = "حجم فایل بیش از حد مجاز("+size_limit+" مگابایت) است. دوباره تلاش نمایید."
        input.files="";
        return;
    }
    
    fn = file.name;
    ff = fn.substring(fn.lastIndexOf('.')+1);
    ff = ff.toLowerCase();
    a = -1;
    if(isImage(ff))a = 0;
    if(isVideo(ff))a = 1;
    if(isAudio(ff))a = 2;
    if(isDocument(ff))a = 3;
    if(is3Dmodel(ff))a = 4;
    if(isDrawing(ff))a = 5;
    if(a == -1){
        label.innerHTML = "فرمت فایل غیر مجاز است. دوباره تلاش نمایید.";
        input.files="";
        return;
    }
    item = input.parentNode;
    if(a == 0){
        prv_up_img(item,file);
    }else{
        prv_up_file(item,file.name,a);
    }
    label.innerHTML = "فایل انتخاب شده مناسب است.";
    return;
    
}

function prv_up_file(item,fn,a){
    item.classList.add('has_prv_up');
    dotIndex = fn.lastIndexOf(".");
    if(dotIndex > 7){
        fn = fn.substring(0,3)+"..."+fn.substring(dotIndex-2);
    }
    gallery = item.getElementsByClassName('gallery')[0];
    gallery.innerHTML = "";
    frmtIcon = iconClass(a);
    div = document.createElement('div');
    div.classList.add('img');
    div.innerHTML += '<span class="icon '+frmtIcon+'"></span><br><span class="eng">'+fn+'</span>';
    gallery.appendChild(div);
    return;
}
function prv_up_img(item,file){
    item.classList.add('has_prv_up');
    reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function(){
        img = document.createElement('img');
        img.src = reader.result;
        div = document.createElement('div');
        div.appendChild(img);
        div.classList.add('img');
//        div.innerHTML += '<div class="rmv" onclick="dlt_file(this)"><span class="icon-xs"></span></div><div class="img_cvr"><span class="icon-srch"></span></div>';
        gallery = item.getElementsByClassName('gallery')[0];
        gallery.innerHTML = "";
        gallery.appendChild(div);
    }
    return;
}
function iconClass(ff_no){
    switch (ff_no){
            case 1:
                return "icon-vid";
            case 2:
                return "icon-oud";
            case 3:
                return "icon-doc";
            case 4:
                return "icon-3d";
            case 5:
                return "icon-f";
        }
}
function isImage(format){
    switch (format){
        case "jpg":
        case "jpeg":
        case "png":
        case "gif":
        case "tif":
        case "tiff":
            return true;
        default:
            return false;
    }
}
function isVideo(format){
    switch (format){
        case "avi":
        case "flv":
        case "mp4":
        case "wmv":
        case "mov":
        case "m4v":
        case "mpg":
        case "3gp":
            return true;
        default:
            return false;
    }
}
function isAudio(format){
    switch (format){
        case "3gp":
        case "aiff":
        case "m4a":
        case "mp3":
        case "ogg":
        case "wav":
        case "wma":
        case "webm":
        case "amr":
        case "au":
        case "awb":
            return true;
        default:
            return false;
    }
}
function isDocument(format){
    switch (format){
        case "pdf":
        case "doc":
        case "docx":
        case "xls":
        case "xlsx":
            return true;
        default:
            return false;
    }
}
function is3Dmodel(format){
    switch (format){
        case "stl":
        case "obj":
        case "sldprt":
        case "prt":
        case "sldasm":
        case "asm":
        case "igs":
        case "iges":
        case "step":
        case "stp":
        case "wrl":
        case "3dm":
            return true;
        default:
            return false;
    }
}
function isDrawing(format){
    switch (format){
        case "drw":
        case "slddrw":
            return true;
        default:
            return false;
    }
}