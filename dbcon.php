<?php
class dbcon{
    private $type;			            #数据库种类
    private $host;	                	#服务器地址
    private $user;		            	#用户
    private $pwd;		            	#密码
    private $db;			            #数据库
    private $port;                      #端口
    public static $dbins;               #唯一实体

    //构造函数
    private function __construct($host,$user,$pwd,$db,$type,$port) {
            $this->type=$type;
            $this->host=$host;
            $this->user=$user;
            $this->pwd=$pwd;
            $this->db=$db;
            $this->port=$port;
            return $this;
     }

    //连接重构
    public function setNewConnection($host,$user,$pwd,$db,$type = "mysql",$port = 3306) {
        $this->type=$type;
        $this->host=$host;
        $this->user=$user;
        $this->pwd=$pwd;
        $this->db=$db;
        $this->port=$port;
        return $this;
    }

    ## MySqli对象
    public function mysql_connect() {
        $my = new mysqli($this->host,$this->user,$this->pwd,$this->db,$this->port);
        if(!$my) return false;
        return $my;
    }

    public function mysql_query($sql,$type = "num") {
        $my = $this->mysql_connect();
        $res = $my->query($sql);
        if(is_bool($res)) return $res;
        $res_arr = "";
        switch ($type) {
            case 'json':
                $res_arr = json_encode($res->fetch_all(MYSQLI_ASSOC));
                break;
            case 'assoc':
                $res_arr = $res->fetch_All(MYSQLI_ASSOC);
                break;
            case 'obj':
                $res_arr = (object)$res->fetch_All(MYSQLI_ASSOC);
                break;
            case 'both':
                $res_arr = $res->fetch_All(MYSQLI_BOTH);
                break;
            default:
                $res_arr = $res->fetch_All(MYSQLI_NUM);
                break;
        }
        return $res_arr;
    }

    public function mysql_exec($sql) {
        $my = $this->mysql_connect();
        $res = $my->query($sql);
        return $res?true:false;
    }

    ## PDO对象
    public function pdo_connect() {
        $pdo = new PDO($this->type.":host=".$this->host.";dbname=".$this->db.";port=".$this->port,$this->user,$this->pwd);
        if(!$pdo) return false;
        return $pdo;
    }

    public function pdo_query($sql,$type = 'num') {
        $pdo = $this->pdo_connect();
        $res = $pdo->query($sql);
        $res_arr = "";
        switch ($type) {
            case 'json':
                $res_arr = json_encode($res->fetchAll(PDO::FETCH_CLASS));
                break;
            case 'assoc':
                $res_arr = $res->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'obj':
                $res_arr = $res->fetchAll(PDO::FETCH_CLASS);
                break;
            case 'both':
                $res_arr = $res->fetchAll(PDO::FETCH_BOTH);
                break;
            default:
                $res_arr = $res->fetchAll(PDO::FETCH_NUM);
                break;
        }
        return $res_arr;
    }

    public function pdo_exec($sql) {
        $pdo = $this->pdo_connect();
        $res = $pdo->exec($sql);
        if(!$res) return false;
        return $res;
    }

    public static function newdbcon($host = '',$user = '',$pwd = '',$db = '',$type = '',$port = ''){

        if(!$type) $type = "mysql";			            #数据库种类
        if(!$host) $host = "127.0.0.1";		            #服务器地址
        if(!$user) $user = "root";			            #用户
        if(!$pwd) $pwd = "root";			            #密码
        if(!$db) $db = "Bookdb";                        #数据库
        if(!$port) $port = 3306;                        #端口

        if (!self::$dbins){
            return self::$dbins=new self($host,$user,$pwd,$db,$type,$port);
        }else{
            return self::$dbins;
        }
    }
}

##面向过程
#Mysqli 函数
function connect($host="",$user="",$pwd="",$db="") {	#数据库连接

    if(!$host) $host = "127.0.0.1";		            #服务器地址
    if(!$user) $user = "root";			            #用户
    if(!$pwd) $pwd = "root";			            #密码
    if(!$db) $db = "Bookdb";                        #数据库

    $con = mysqli_connect($host,$user,$pwd,$db);
    if(!$con) return false;
    else return $con;
}

function query($sql,$type = 'num',$con = false) {	#查询
    if(!$con) $con = connect();
    $res = mysqli_query($con,$sql);
    if(is_bool($res)){
        if(!$res) return false;
        else return true;
    }
    $res_arr = "";
    switch ($type) {
        case 'json':
            $res_arr = json_encode(mysqli_fetch_all($res,MYSQLI_ASSOC));
            break;
        case 'assoc':
            $res_arr = mysqli_fetch_all($res,MYSQLI_ASSOC);
            break;
        case 'obj':
            $res_arr = (object)mysqli_fetch_all($res,MYSQLI_ASSOC);
            break;
        case 'both':
            $res_arr = mysqli_fetch_all($res,MYSQLI_BOTH);
            break;
        default:
            $res_arr = mysqli_fetch_all($res,MYSQLI_NUM);
            break;
    }
    mysqli_close($con);
    return $res_arr;
}

function execute($sql,$con = false) {	#删改
    if(!$con) $con = connect();
    $res = mysqli_query($con,$sql);
    mysqli_close($con);
    return ($res?true:false);
}

function insert($res_arr,$table_str){       #增
    if (!is_array($res_arr)) return false;
    $col_str="";
    $val_str="";
    foreach ($res_arr as $k=>$v){
        $col_str .= ("`".$k."`,");
        $val_str .= ("'".$v."',");
    }
    $col_str = substr($col_str,0,(strlen($col_str) - 1));
    $val_str = substr($val_str,0,(strlen($val_str) - 1));
    return execute("INSERT INTO `".$table_str."` (".$col_str.") VALUES ($val_str)")?:"INSERT INTO `".$table_str."` (".$col_str.") VALUES ($val_str)";
}

function delete($table_str,$condition_str){                #删
    if(!$table_str||!$condition_str) return false;
    return execute("DELETE FROM `".$table_str."` WHERE ".$condition_str);
}

function update($res_arr,$table_str,$position_str){       #改
    if (!is_array($res_arr)) return false;
    $val_str="";
    foreach ($res_arr as $k=>$v){
        $val_str .= ("`".$k."`='".$v."',");
    }
    $val_str = substr(val_str,0,(strlen($val_str) - 1));
    return execute("UPDATE `".$table_str."` SET ".$val_str." WHERE ($position_str)");
}

function select($table_str,$limit_str = "*",$type = "num"){       #查
    return query("SELECT ".$limit_str." FROM `".$table_str."`",$type);
}

//字符加密
class encryption{
    private $cipher_num = 10;	#密码盐数量
    private $qu_num = 10;		#编码偏移量

    function __construct($qu_num = 10, $cipher_num = 10){
        $this->qu_num = $qu_num;
        $this->cipher_num = $cipher_num;
        return $this;
    }

    public function encode($str){ 	#加密
        $res_str = "";
        $cipher = "";
        foreach(str_split($str) as $char){
            $c = ord($char);
            $c += $this->qu_num;
            if($c>126) $c -= 93;
            $res_str .= chr($c);
        }
        for($i = 0;$i<$this->cipher_num;$i++){
            $e = chr(rand(33,126));
            $cipher .= $e;
        }
        $res_str = $cipher.$res_str;
        return $res_str;
    }

    public function decode($str){	#解密
        $old_str = substr($str,$this->cipher_num);
        $res_str = "";
        foreach(str_split($old_str) as $char){
            $c = ord($char);
            $c -= $this->qu_num;
            if($c<=(33+$this->qu_num)) $c += 93;
            $res_str .= chr($c);
        }
        return $res_str;
    }
}

class redistool{
    private $host = '127.0.0.1';    #Redis主机
    private $port = 6379;           #端口
    private $dbindex = 0;           #默认数据库
    private $timeout = 0.0;         #生命周期
    public $radis = false;         #dedis实例
    public static $tool = false;    #唯一实例

    #唯一实例
    public static function newredis($host = "",$port = "",$dbindex = "",$timeout = ""){
        if (!self::$tool)
            return self::$tool = new self($host,$port,$dbindex,$timeout);
        else
            return self::$tool;
    }

    #构造函数
    private function __construct($host = "",$port = "",$dbindex = "",$timeout = "")
    {
        $this->redis = new Redis();
        if ($host) $this->host = $host;
        if ($port) $this->port = $port;
        if ($dbindex) $this->dbindex = $dbindex;
        if ($timeout) $this->timeout = $timeout;
        try {
            $this->redis->connect($this->host,$this->port,$this->dbindex,$this->timeout);
            return $this;
        }catch (Exception $e){
            return $e;
        }
    }

    #连接重构
    public function setNewConection($host,$port = 6379,$dbindex = 1,$timeout = 0.0){
        return self::newredis($host,$port,$dbindex,$timeout);
    }

    public function array_insert($table_name,$array = "can be an object"){
        if(is_object($array)) $array = (array)$array;
        if(!is_array($array)){
//            throw new error("the second parameter must be an array or object");
            return false;
        }
        foreach ($array as $v){
            if (!$this->redis->zAdd($table_name,array(),(int)$v[0],json_encode($v))){
//                throw new error("redis add error");
                return false;
            }
        }
        return true;
    }

    public function async_mysql_table($mysql_table_name,$array_type = "assoc",$redis_table_name = false){
        switch ($array_type){
            case "num":
                $res = query("select * from ".$mysql_table_name,"num");
                break;
            case "both":
                $res = query("select * from ".$mysql_table_name,"both");
                break;
            default:
                $res = query("select * from ".$mysql_table_name,"assoc");
                break;
        }
        if($res){
            if (!$redis_table_name) $redis_table_name = $mysql_table_name;
            foreach ($res as $v){
                if (!$this->redis->zAdd($redis_table_name,array(),(int)$v[0],json_encode($v))){
                    error("redis add error");
                    return false;
                }
            }
            return true;
        }else{
//            throw new error("mysql table ".$mysql_table_name." not exist");
            return false;
        }
    }

    public function query_all($table_name,$retuen_type = "array"){
        if(!$val = $this->redis->zRange($table_name,0,-1)) return false;
        $res_arr = array();
        switch ($retuen_type){
            case "array":
                foreach ($val as $v) $res_arr[] = json_decode($v);
                break;
            case "json":
                $arr = $this->redis->zRange("the",0,-1);
                $res_arr = "[";
                for ($i = 0;$i < count($arr);$i++){
                    $res_arr .= $arr[$i];
                    if ($i == count($arr)-1) continue;
                    $res_arr .= ",";
                }
                $res_arr .= "]";
                break;
            case "obj":
                foreach ($val as $v) $res_arr[] = (object)json_decode($v);
                $res_arr = (object)$res_arr;
                break;
            default:
//                throw new error("unknown return type at the second parameter");
                return false;
        }
        return $res_arr;
    }

    public function query($table_name,$retuen_type = "array",$start = 0,$end = 2000){
        if(!$val = $this->redis->zRangeByScore($table_name,$start,$end)) return false;
        $res_arr = array();
        switch ($retuen_type){
            case "array":
                foreach ($val as $v) $res_arr[] = json_decode($v);
                break;
            case "json":
                $arr = $this->redis->zRange("the",0,-1);
                $res_arr = "[";
                for ($i = 0;$i < count($arr);$i++){
                    $res_arr .= $arr[$i];
                    if ($i == count($arr)-1) continue;
                    $res_arr .= ",";
                }
                $res_arr .= "]";
                break;
            case "obj":
                foreach ($val as $v) $res_arr[] = (object)json_decode($v);
                $res_arr = (object)$res_arr;
                break;
            default:
//                throw new error("unknown return type at the second parameter");
                return false;
        }
        return $res_arr;
    }

    public function delete($table_name,$start_index_str,$end_index_str = false){
        if (!$end_index_str) $end_index_str = $start_index_str;
        return $this->redis->zRemRangeByScore($table_name,$start_index_str,$end_index_str);
    }

    public function update($table_name,$index_str,$update_data){
        if (is_array($update_data)) $update_data = json_encode($update_data);
        $backup = json_encode($this->query($table_name,"array",$index_str,$index_str));
        if (!$this->redis->zRemRangeByScore($table_name,$index_str,$index_str)) return false;
            if ($this->redis->zAdd($table_name,array(),$index_str,$update_data))
            return true;
        else{
            $this->redis->zAdd($table_name,array(),$index_str,$backup);
            return false;
        }
    }
}
?>