<link rel="stylesheet" href="<?php echo asset_url('css/style_2.css'); ?>" type="text/css" media="all">
<link rel="stylesheet"  href="<?php echo asset_url('css/style_sec.css'); ?>" type="text/css" media="none" onload="if(media!='all')media='all'">
<link rel="stylesheet" href="<?php echo asset_url('css/font_icon.css'); ?>" type="text/css" media="none" onload="if(media!='all')media='all'">
<link rel="stylesheet" href="<?php echo asset_url('css/chat.css'); ?>" type="text/css" media="none" onload="if(media!='all')media='all'">

<?php
    if($module_name == "custom"){
?>
<link rel="stylesheet" href="<?php echo asset_url('css/custom.css'); ?>" type="text/css" media="all">
<?php
    }
?>
