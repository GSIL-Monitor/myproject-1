<?php
class Mysql
{
    private $dbHost; // 数据库主机
    private $dbUser; // 数据库用户名
    private $dbPwd; // 数据库用户名密码
    private $dbDatabase; // 数据库名
    private $conn; // 数据库连接标识;
    private $queryRS; // 执行query命令的结果资源标识
    private $coding; // 数据库编码，GBK,UTF8,gb2312
    
    /* construct */
    public function __construct($dbHost, $dbUser, $dbPwd, $dbDatabase, $conn="", $coding="UTF8")
    {
        $this->db_host = $dbHost;
        $this->db_user = $dbUser;
        $this->db_pwd = $dbPwd;
        $this->db_database = $dbDatabase;
        $this->conn = $conn;
        $this->coding = $coding;
        $this->connect();
    }
    
    /* connect database */
    public function connect()
    {
        if($this->conn == "pconn")
        {
            // persistent connection 
            $this->conn = mysql_pconnect($this->db_host, $this->db_user, $this->db_pwd);
        }
        else
        {
            // connection
            $this->conn = mysqli_connect($this->db_host, $this->db_user, $this->db_pwd, $this->db_database);
            // mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
        }
        
//         if(! mysql_select_db($this->db_database, $this->conn))
//         {
//             throw new Exception("The database error.");
//         }
        mysqli_query($this->conn, "SET NAMES $this->coding");
        return true;
    }
    
    /* excute sql */
    public function query($sql)
    {
        if($sql == "")
        {
            throw new Exception("The sql string is empty.");
        }
        
        $this->queryRS = mysqli_query($this->conn,$sql);//mysql_query($sql, $this->conn);
        
        if(! $this->queryRS)
        {
            throw new Exception("Sql error: ".$sql);
        }
        return $this->queryRS;
    }
    
    public function queryResult($sql)
    {
        $this->queryRS=$this->query($sql);
        $result=array();
        while($row=mysqli_fetch_array($this->queryRS, MYSQLI_BOTH))
        {
            $result[]=$row;
        }
        return $result;
    }
    
    /* create database */
    public function createDatabase($databaseName)
    {
        $database = $databaseName;
        $sqlDatabase = 'create database ' . $database;
        $this->query($sqlDatabase);
    }
    
    // return all databases' name
    public function getDatabases()
    {
        $result = $this->queryResult("show databases");
        return $result;
    }
    
    /* get all tables of the given database */
    public function getTables($databaseName)
    {
         $result = $this->queryResult("show tables from ".$databaseName);
         return $result;
    }
    
    // select all
    public function findAll($table)
    {
        return $this->queryResult("SELECT * FROM $table");
    }
    
    // select
    public function select($table, $columnName = "*", $condition = '', $debug = '')
    {
        $condition = $condition ? ' Where ' . $condition : NULL;
        if($debug)
        {
            echo "SELECT $columnName FROM $table $condition";
        }
        else
        {
            $this->queryResult("SELECT $columnName FROM $table $condition");
        }
    }
    
    // delete
    public function delete($table, $condition)
    {
        return $this->queryResult("DELETE FROM $table WHERE $condition");
    }
    
    // insert
    public function insert($table, $columnName, $value)
    {
        return $this->queryResult("INSERT INTO $table ($columnName) VALUES ($value)");
    }
    
    // update
    public function update($table, $mod_content, $condition)
    {
        return $this->queryResult("UPDATE $table SET $mod_content WHERE $condition");
    }
    
    // get insert id
    public function getInsertId()
    {
        return mysqli_insert_id($this->conn);
    }
    
    // get affected rows
    public function getAffectedRows()
    {
        return mysqli_affected_rows($this->conn);
    }
    
    // free resource
    public function free()
    {
        @ mysqli_free_result($this->queryRS);
    }
    
    // select database
    public function selectDatabase($dbDatabase)
    {
        return mysqli_select_db($this->conn,$dbDatabase);
    }
    
    
    // get mysql server infomation
    public function getMysqlServerInfo($num = 0)
    {
        switch ($num)
        {
            case 1:
                return mysqli_get_server_info($this->conn); // MySQL 服务器信息
                break;
            
            case 2:
                return mysqli_get_host_info($this->conn); // 取得 MySQL 主机信息
                break;
            
            case 3:
                return mysqli_get_client_info($this->conn); // 取得 MySQL 客户端信息
                break;
            
            case 4:
                return mysqli_get_proto_info($this->conn); // 取得 MySQL 协议信息
                break;
            
            default:
                return mysqli_get_client_info($this->conn); // 默认取得mysql版本信息
        }
    }
    
    // 析构函数，自动关闭数据库,垃圾回收机制
    public function __destruct()
    {
        if(! empty($this->queryRS))
        {
            $this->free();
        }
        mysqli_close($this->conn);
    } 
    
    /* 获得客户端真实的IP地址 */
    function getip()
    {
        if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        else if(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        else if(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        {
            $ip = getenv("REMOTE_ADDR");
        }
        else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
            $ip = "unknown";
        }
        return ($ip);
    }

    function inject_check($sql_str)
    { // 防止注入
        $check = eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);
        if($check)
        {
            echo "输入非法注入内容！";
            exit();
        }
        else
        {
            return $sql_str;
        }
    }
}
?>