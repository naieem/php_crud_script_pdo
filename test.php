<?php
/*
 * DB Class
 * This class is used for database related (connect, insert, update, and delete) operations
 * with PHP Data Objects (PDO)
 * @author    CodexWorld.com
 * @url       http://www.codexworld.com
 * @license   http://www.codexworld.com/license
 */
class DBCONNECTION{
    private $dbHost     = "localhost";
    private $dbUsername = "root";
    private $dbPassword = "";
    private $dbName     = "inventory";
    // private $dbHost     = "208.113.131.151";
    // private $dbUsername = "dev1";
    // private $dbPassword = "SSapaKtWFuzwca7D";
    // private $dbName     = "inventory";

    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            try{
                $conn = new PDO('mysql:host='.$this->dbHost.';dbname='.$this->dbName, $this->dbUsername, $this->dbPassword);
                $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn -> setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES 'utf8'");
                // $conn ->set_charset("utf8");
                $this->db = $conn;
                // print_r("connected");
            }catch(PDOException $e){
                die("Failed to connect with MySQL: " . $e->getMessage());
            }
        }
    }

    /*
     * Returns rows from the database based on the conditions
     * @param string name of the table
     * @param array select, where, order_by, limit and return_type conditions
     */
    // public function getRows($table,$conditions = array()){
    //  $sql = 'SELECT ';
    //  $sql .= array_key_exists("select",$conditions)?$conditions['select']:'*';
    //  $sql .= ' FROM '.$table;
    //  if(array_key_exists("where",$conditions)){
    //      $sql .= ' WHERE ';
    //      $i = 0;
    //      foreach($conditions['where'] as $key => $value){
    //          $pre = ($i > 0)?' AND ':'';
    //          $sql .= $pre.$key." = '".$value."'";
    //          $i++;
    //      }
    //  }

    //  if(array_key_exists("order_by",$conditions)){
    //      $sql .= ' ORDER BY '.$conditions['order_by']; 
    //  }

    //  if(array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
    //      $sql .= ' LIMIT '.$conditions['start'].','.$conditions['limit']; 
    //  }elseif(!array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
    //      $sql .= ' LIMIT '.$conditions['limit']; 
    //  }

    //  $query = $this->db->prepare($sql);
    //  $query->execute();

    //  if(array_key_exists("return_type",$conditions) && $conditions['return_type'] != 'all'){
    //      switch($conditions['return_type']){
    //          case 'count':
    //          $data = $query->rowCount();
    //          break;
    //          case 'single':
    //          $data = $query->fetch(PDO::FETCH_ASSOC);
    //          break;
    //          default:
    //          $data = '';
    //      }
    //  }else{
    //      if($query->rowCount() > 0){
    //          $data = $query->fetchAll();
    //      }
    //  }
    //  return !empty($data)?$data:false;
    // }
    
    /*
     * Insert data into the database
     * @param string name of the table
     * @param array the data for inserting into the table
     */
    // public function insert($table,$data){
    //     if(!empty($data) && is_array($data)){
    //         $columns = '';
    //         $values  = '';
    //         $i = 0;
    //         if(!array_key_exists('created',$data)){
    //             $data['created'] = date("Y-m-d H:i:s");
    //         }
    //         if(!array_key_exists('modified',$data)){
    //             $data['modified'] = date("Y-m-d H:i:s");
    //         }

    //         $columnString = implode(',', array_keys($data));
    //         $valueString = ":".implode(',:', array_keys($data));
    //         $sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
    //         $query = $this->db->prepare($sql);
    //         foreach($data as $key=>$val){
    //              $query->bindValue(':'.$key, $val);
    //         }
    //         $insert = $query->execute();
    //         return $insert?$this->db->lastInsertId():false;
    //     }else{
    //         return false;
    //     }
    // }
    public function insert($table,$data){
        // echo $table;
        if(!empty($data) && is_array($data)){
            $k='';
            $v='';
            $count=0;
            foreach($data as $key => $value) {
                $count++;
                $value=utf8_decode($value);
                if($count == count($data)){
                    $k.=$key;
                    $v.='"'.$value.'"'; 
                }
                else{
                    $k.=$key.",";
                    $v.='"'.$value.'",';    
                }
                
            }
            $sql = "INSERT INTO ".$table." (".$k.") VALUES (".$v.")";
            $query = $this->db->prepare($sql);
            $insert = $query->execute();
            // return $insert?$this->db->lastInsertId():false;
            return $insert?1:false;
        }
    }
    /*
    *get all data from a table
    */
    public function get_data($config){
        // var_dump($config);
        $table=$config['tables'];
        $fields=$config['fields'];
        $join=$config['join'];
        $condition=$config['condition'];

        if(count($table)> 1 && count($table)<3 && !empty($join) && $join!='default'){
            $sql = "SELECT $fields FROM $table[0] $join JOIN $table[1] $condition";
        }elseif (count($table)> 1 && count($table)<3 && $join=='default') {
            $sql = "SELECT $fields FROM $table[0] JOIN $table[1] $condition";
        }else{
            $sql = "SELECT $fields FROM $table[0] $condition";
        }
        // $this->db->prepare("SET NAMES UTF8");
        $query = $this->db->prepare($sql);
        $q = $query->execute();
        if($q){
            $data=$query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $key => $value) {
                $arr[]=array_map('utf8_encode',$value);
            }
            return $arr;
        }else
        return false;
    }
    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     */
    public function update($table,$data,$conditions){
        if(!empty($data) && is_array($data)){
            $colvalSet = '';
            $whereSql = '';
            $i = 0;
            // if(!array_key_exists('modified',$data)){
            //  $data['modified'] = date("Y-m-d H:i:s");
            // }
            foreach($data as $key=>$val){
                $val=utf8_decode($val);
                // $val=htmlspecialchars_decode($val);
                $pre = ($i > 0)?', ':'';
                $colvalSet .= $pre.$key."='".$val."'";
                $i++;
            }
            if(!empty($conditions) && is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$value."'";
                    $i++;
                }
            }
            $sql = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
            $query = $this->db->prepare($sql);
            $update = $query->execute();
            return $update?$query->rowCount():false;
        }else{
            return false;
        }
    }
    
    /*
     * Delete data from the database
     * @param string name of the table
     * @param array where condition on deleting data
     */
    public function delete($table,$conditions){
        $whereSql = '';
        if(!empty($conditions)&& is_array($conditions)){
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $whereSql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }
        $sql = "DELETE FROM ".$table.$whereSql;
        $query = $this->db->prepare($sql);
        $delete = $query->execute();
        return $delete?1:false;
    }
}

// $data=array(
//  'parent_id' => 1,
//  'name' =>"naieem"
//  );
/* Insert */
// $v=$db->insert("roles",$data);
// var_dump($v);
/* get all,get specific data*/
// $query=array(
//  'tables' => array('roles','rooms'),
//  'fields'=> "roles.name,rooms.secret_code",
//  'join'=>"INNER",
//  'condition'=>"ON roles.id=rooms.id" 
//  );
// $v=$db->get_data($query);
/* update data*/
// $data=array(
//  'parent_id' => "10" 
//  );
// $condition=array(
//  'id'=>4,
//  'name'=>'supto'
//  );
// $v=$db->update("roles",$data,$condition);
// var_dump($v);
