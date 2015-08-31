<?php

error_reporting(0);
class DB {
	private $link_id;
	public function __construct(){
	}
	public function connect($hostname,$username,$password,$database,$charset){
		$this->link_id=mysql_connect($hostname,$username,$password);
		if(!$this->link_id)
		$this->error_tip("数据库链接失败:");
		if(!mysql_select_db($database,$this->link_id)){
		mysql_query("create DATABASE $database CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'",$this->link_id);
		mysql_select_db($database,$this->link_id);
		}
		mysql_query("set names 'utf8'",$this->link_id);
	}
        //获取最后插入的id
        
        public function insert_id() {
            $id = mysql_insert_id($this->link_id);
            return $id;
        }    
        
	public function query($sql){
		$query=mysql_query($sql,$this->link_id);
		if(!$query)
		$this->error_tip("查询失败：".$sql);
		return $query;
	}
	public function get_all($sql,$result_type=MYSQL_ASSOC){
		$query=$this->query($sql);
		$result=array();
		if($query){
		$i=0;
		$result=array();
		while($row=mysql_fetch_array($query,$result_type)){
		$result[$i]=$row;
		$i++;
		}
		}
		return $result;
	}
	public function get_one($sql,$result_type = MYSQL_ASSOC){
		$query = $this->query($sql);
		$rt =mysql_fetch_array($query,$result_type);
		return $rt;
	}
	public function insert($table,$data_array){
		$field="";
		$value="";
		if(!is_array($data_array)||count($data_array)<=0){
		$this->error_tip("没有要添加的数据");
		return false;
		}
		while(list($key,$val)=each($data_array)){
		$field.="$key,";
		$value.="'$val',";
		}
		$field=substr($field,0,-1);
		$value=substr($value,0,-1);
		$sql="insert into $table ($field) values ($value)";
			$this->error_tip($sql);
		if(!$this->query($sql))
		return false;
		return true;
	}
	public function update($table,$data_array,$condition=""){
		if(!is_array($data_array)||count($data_array)<=0){
		$this->error_tip("没有要更新的数据");
		return false;
		}
		$value="";
		while(list($key,$val)=each($data_array)){
		$value.="$key='$val',";
		}
		$value=substr($value,0,-1);
		$sql="update $table set $value $condition";
		if(!$this->query($sql))
		return false;
		return true;
	}
	public function delete($table,$condition=""){
		if(empty($condition)){
		$this->error_tip("没有删除条件");
		return false;
		}
		$sql="delete from $table $condition";
		if(!$this->query($sql))
		return false;
		return true;
	}
	public function num_rows($sql){
		$result=$this->query($sql);
		if(!is_bool($result)){
		$num=mysql_num_rows($result);
		return $num;
		}
		else
		return 0;
	}
	public function free_result(){
		$void=func_get_args();
		foreach($void as $query){
		if(is_resource($query)&&get_resource_type($query)=='mysql_result')
		return mysql_free_result($query);
		}
	}
	public function close(){
		return mysql_close($this->link_id);
	}
	public function __destruct(){
		$this->free_result();
		$this->close();
	}
	public function error_tip($tip){
		$tip.="\r\n".mysql_error();
	}
}
?>