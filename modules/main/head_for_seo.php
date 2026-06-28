<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    switch($module_name){
        case "posts":
            $tb = "posts";
            $title = getVarFromDB($tb,"title","name",$file_name);
            $keywords = getVarFromDB($tb,"keywords","name",$file_name);
            $desc = getVarFromDB($tb,"description","name",$file_name);
            if($file_name == "index"){
                $tb = "modules";
                $title = getVarFromDB($tb,"title","name",$module_name);
                $keywords = getVarFromDB($tb,"keywords","name",$module_name);
                $desc = getVarFromDB($tb,"description","name",$module_name);
            }
            break;
        case "pages":
            $tb = "pages";
            $title = getVarFromDB($tb,"title","name",$file_name);
            $keywords = getVarFromDB($tb,"keywords","name",$file_name);
            $desc = getVarFromDB($tb,"description","name",$file_name);
            break;
        case "products":
            $tb = "products";
            $title = getVarFromDB($tb,"name","file_name",$file_name)." | فروشگاه میوه خشک آبان";
            $keywords = getVarFromDB($tb,"keywords","file_name",$file_name);
            $desc = getVarFromDB($tb,"description","file_name",$file_name);
            break;
        default:
            $tb = "modules";
            $title = getVarFromDB($tb,"title","name",$module_name);
            $keywords = getVarFromDB($tb,"keywords","name",$module_name);
            $desc = getVarFromDB($tb,"description","name",$module_name);
            break;
    }
?>
<title><?php echo $title; ?></title>
<meta name="description" content="<?php echo $desc; ?>">
<meta name="keywords" content="<?php echo str_replace("-",",",$keywords); ?>">

<meta property="og:locale" content="en_GB">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo $title; ?>">
<meta property="og:description" content="<?php echo $desc; ?>">

<meta property="og:site_name" content="میوه خشک آبان">

<?php
// additional meta data for product pages
if($module_name == "products"){
    $tb = "products";
    $pid = getVarFromDB($tb,"id","file_name",$file_name);
    if($pid){
        $name = getVarFromDB($tb,"name","file_name",$file_name);
        echo "<meta property=\"product_name\" content=\"$name\">";
        $image_url = getVarFromDB($tb,"first_img_id","file_name",$file_name);
        $image_url = getVarFromDB("content","file_name","id",$image_url);
        $dir = "https://abanfruit.com/"."content";
        $image_url = "$dir/$image_url";
        echo "<meta property=\"og:image\" content=\"$image_url\">";
        $price = getVarFromDB("products_price","price","pid",$pid,'id DESC');
        $price = explode(",",$price)[0];
        echo "<meta property=\"product_price\" content=\"$price\">";
        $state = getVarFromDB($tb,"state","file_name",$file_name);
        $availability = "outofstock";
        if((int)$state === 0){$availability = "instock";}
        echo "<meta property=\"availability\" content=\"$availability\">";
        //url
        echo "<meta property=\"og:url\" content=\"". str_replace('structure',$file_name,$URL) ."\">";
    }
}





?>




<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-145106797-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-145106797-1');
</script>

<!-- YektaNet -->
<script>
    !function (t, e, n) {
        t.yektanetAnalyticsObject = n, t[n] = t[n] || function () {
            t[n].q.push(arguments)
        }, t[n].q = t[n].q || [];
        var a = new Date, r = a.getFullYear().toString() + "0" + a.getMonth() + "0" + a.getDate() + "0" + a.getHours(),
            c = e.getElementsByTagName("script")[0], s = e.createElement("script");
        s.id = "ua-script-XmBJr0AL"; s.dataset.analyticsobject = n;
        s.async = 1; s.type = "text/javascript";
        s.src = "https://cdn.yektanet.com/rg_woebegone/scripts_v3/XmBJr0AL/rg.complete.js?v=" + r, c.parentNode.insertBefore(s, c)
    }(window, document, "yektanet");
</script>

<?php
        }
    }
?>