<?php
    $num_bars = array(
        // white,black,white,black for left side
        0=>[3,2,1,1],
        1=>[2,2,2,1],
        2=>[2,1,2,2],
        3=>[1,4,1,1],
        4=>[1,1,3,2],
        5=>[1,2,3,1],
        6=>[1,1,1,4],
        7=>[1,3,1,2],
        8=>[1,2,1,3],
        9=>[3,1,1,2]
    );
    
    $guards = array(
        's'=>[1,0,1],
        'm'=>[0,1,0,1,0],
        'e'=>[1,0,1],
        'q'=>[0,0,0,0,0,0,0,0,0]
    );
    
    
    function create_gaurd($g){
        $str = "<li class=\"l g\">";
        foreach($GLOBALS["guards"][$g] as $v){
            $str .= "<div class=\"m$v\"></div>";
        }
        $str .= "</li>";
        return $str;
    }
    
    function create_digit($d,$s){
        $str = "<li class=\"$s\">";
        foreach($GLOBALS["num_bars"][$d] as $k=>$v){
            $t = $k%2;
            for($i=0;$i<$v;$i++){
                $str .= "<div class=\"m$t\"></div>";
            }
        }
        $str .= "</li>";
        return $str;
    }
?>


<style type="">
    div.upc *{
        margin:0;
        padding:0;
        list-style:none;
        border:0;
    }
    div.upc{
        background-color:transparent;
        height: 21.9mm;
        width: calc(114 * 0.33mm);
        padding:2mm;
        box-sizing: content-box;
        position:absolute;
        display:table;
        bottom:9mm;
        right:12mm;
        
    }
    div.vis{
        position:absolute;
        height:5mm;
        font-size:3mm;
        line-height:5mm;
        text-align:center;
        bottom:1mm;
        font-family:tahoma;
    }
    div.upc div.type{left:2.5mm;}
    div.upc div.lv{left:10mm;}
    div.upc div.rv{left:24mm;}
    div.upc div.check{left:37mm;}
    
    div.upc li div{
        width:0.33mm;
        height:18.9mm;
        margin:0;
        padding:0;
        float:left;
        display:block;
    }
    
    div.upc li.g div{
        height:20.55mm;
    }
    
    .l .m0{background-color:white;}
    .l .m1{background-color:black;}
    .r .m0{background-color:black;}
    .r .m1{background-color:white;}
    
    
    div.rec_info{
        line-height:6mm !important;
    }
    
    div.pp div.social{
        line-height:0mm !important;
        bottom:1mm;
        direction:ltr;
        text-align:center;
    }
    div.pp div.social img{
        position: relative;
        width: 5mm;
        height: 5mm;
        margin-right: 0mm;
        top: 1mm;
    }
</style>