<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
include $bu."$module_name/$cf/".$_SESSION[$cf]->state.".php";
?>

<?php
        }
    }
?>