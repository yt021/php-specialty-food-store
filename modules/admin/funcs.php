<?php
//$bu = "../../";
include_once $bu."modules/wdb/db_connection.php";
include_once $bu."modules/wdb/db_funcs.php";


class where{
    public $section;
    public $level;
    public $state;
    public $order_by;
    public $offset;
    public $del_flag;
    public $sql_where;
    public $id;
    public $sub_id;
    public $page_limit;
    public function __construct($section,$level = 0,$state = null,$id = null){
        $this->section = $section;
        $this->level = $level;
        $this->state = $state;
        $this->order_by = new order_by();
        $this->id = $id;
        $this->del_flag = 0;
        $this->offset = 0;
        $this->page_limit = 30;
    }
}

class order_by{
    private $items = array();
    public function add_item($key){
        $f = $this->find_item($key);
        $order = "DESC";
        if($f>-1){
            if($this->items[$f]->data()["order"]=="DESC")$order="ASC";
        }
        $this->delete_item($key);
        $o = sizeof($this->items);
        $this->items[$o] = new sort_item($key,$order);
        return;
    }
    private function find_item($key){
        $o = sizeof($this->items);
        $f = 0;
        for($i=0;$i<$o;$i++){
            $f += $this->items[$i]->is_key($key)*($i+1);
        }
        return $f-1;  
    }
    private function delete_item($key){
        $o = sizeof($this->items);
        $f = $this->find_item($key);
        if($f>-1){
            for($i = $f;$i<$o-1;$i++){
                $this->items[$i] = $this->items[$i+1];
            }
            unset($this->items[$i]);
        }
        return;
    }
    public function order_str($table){
        $str = " ORDER BY ";
        if($o = sizeof($this->items)){
        
        for($i=$o-1;$i>=0;$i--){
            if($this->items[$i]->check_table($table)){
                $str .= $this->items[$i]->data()["key"]." ".$this->items[$i]->data()["order"].", ";
            }
        }
        }
        $str .= "id DESC ";
        return $str;
    }
    public function keys_array(){
        $keys_array = array();
        if($o = sizeof($this->items)){
            for($i=0;$i<$o;$i++){
                    $keys_array[$i] = $this->items[$i]->data()["key"];
            }
        }
        return $keys_array;
    }
}
class sort_item{
    private $keyword;
    private $order;
    public function __construct($keyword,$order="DESC"){
        $this->keyword = $keyword;
        $this->order = $order;
    }
    public function data(){
        $data["key"] = $this->keyword;
        $data["order"] = $this->order;
        return $data;
    }
    public function is_key($keyword){
        if($keyword == $this->keyword){return 1;}
        return 0;
    }
    public function check_table($table){
        if(check_number($this->keyword)){return 1;}
        include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
        $st = "SELECT column_name FROM information_schema.columns WHERE table_name = ?;";
        $st = $mysqli->prepare($st);
        $st->bind_param('s',$table);
        if(!$st->execute())return 0;
        $res = $st->get_result();
        while($row = $res->fetch_assoc()){if($row["column_name"] == $this->keyword)return 1;}
        return 0;
    }
}
?>
