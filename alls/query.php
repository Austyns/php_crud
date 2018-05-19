<?php
if (defined('DEVELOPMENT') && DEVELOPMENT === true)
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

define('DB_USERNAME', 'root');
// define('DB_PASSWORD', 'newpassword');
define('DB_PASSWORD', 'pass');
define('DB_HOST', 'localhost');
define('DB_NAME', 'ncto_dump');

// Database setting constants [DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD]
class dbHelper {
    private $db;
    private $err;
    function __construct() {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
        try {
            $this->db = new PDO($dsn, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            $response["status"] = "error";
            $response["message"] = 'Connection failed: ' . $e->getMessage();
            $response["data"] = null;
            exit;
        }
    }
    
    // Method to select from a table
    function select($table, $where){
        try{
            $a = array();
            $w = "";
            foreach ($where as $key => $value) {
                $w .= " and " .$key. " like :".$key;
                $a[":".$key] = $value;
            }
            $stmt = $this->db->prepare("select * from ".$table." where 1=1 ". $w);
            $stmt->execute($a);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($rows)<=0){
                $response["status"] = "warning";
                $response["message"] = "No data found.";
            }else{
                // print_r( $rows);
                $response["status"] = "success";
                $response["message"] = "Data selected from database";
            }
                $response["data"] = $rows;
        }catch(PDOException $e){
            $response["status"] = "error";
            $response["message"] = 'Select Failed: ' .$e->getMessage();
            $response["data"] = null;
        }
        return $response;
    }
    // Method to select from a table and  order the result by a colunms entitiess
    function selectAndOrder($table, $columns, $where, $order){
        try{
            $a = array();
            $w = "";
            foreach ($where as $key => $value) {
                $w .= " and " .$key. " like :".$key;
                $a[":".$key] = $value;
            }
            $stmt = $this->db->prepare("select ".$columns." from ".$table." where 1=1 ". $w." ".$order);
            $stmt->execute($a);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($rows)<=0){
                $response["status"] = "warning";
                $response["message"] = "No data found.";
            }else{
                $response["status"] = "success";
                $response["message"] = "Data selected from database";
            }
                $response["data"] = $rows;
        }catch(PDOException $e){
            $response["status"] = "error";
            $response["message"] = 'Select Failed: ' .$e->getMessage();
            $response["data"] = null;
        }
        return $response;
    }
    // Method to insert into a table 
    function insert($table, $columnsArray, $requiredColumnsArray) {
        $this->verifyRequiredParams($columnsArray, $requiredColumnsArray);
        
        try{
            $a = array();
            $c = "";
            $v = "";
            foreach ($columnsArray as $key => $value) {
                $c .= $key. ", ";
                $v .= ":".$key. ", ";
                $a[":".$key] = $value;
            }
            $c = rtrim($c,', ');
            $v = rtrim($v,', ');
            $stmt =  $this->db->prepare("INSERT INTO $table($c) VALUES($v)");
            $stmt->execute($a);
            $lastInsertId = $this->db->lastInsertId();
            $affected_rows = $stmt->rowCount();
            $response["status"] = "success";
            $response["last_id"] = $lastInsertId;
            $response["message"] = "success";
            $response["info"] = $affected_rows." row inserted into database";
        }catch(PDOException $e){
            $response["status"] = "error";
            $response["message"] = 'Insert Failed: ' .$e->getMessage();
        }
        return $response;
    }
    // Method to update a table row 
    function update($table, $columnsArray, $where, $requiredColumnsArray){ 
        $this->verifyRequiredParams($columnsArray, $requiredColumnsArray);
        try{
            $a = array();
            $w = "";
            $c = "";
            foreach ($where as $key => $value) {
                $w .= " and " .$key. " = :".$key;
                $a[":".$key] = $value;
            }
            foreach ($columnsArray as $key => $value) {
                $c .= $key. " = :".$key.", ";
                $a[":".$key] = $value;
            }
                $c = rtrim($c,", ");

            $stmt =  $this->db->prepare("UPDATE $table SET $c WHERE 1=1 ".$w);
            $stmt->execute($a);
            $affected_rows = $stmt->rowCount();
            if($affected_rows<=0){
                $response["status"] = "warning";
                $response["message"] = "No row updated";
            }else{
                $response["status"] = "success";
                $response["message"] = $affected_rows." row(s) updated in database";
            }
        }catch(PDOException $e){
            $response["status"] = "error";
            $response["message"] = "Update Failed: " .$e->getMessage();
        }
        return $response;
    }
    //  Method to delete a table row 
    function delete($table, $where){
        if(count($where)<=0){
            $response["status"] = "warning";
            $response["message"] = "Delete Failed: At least one condition is required";
        }else{
            try{
                $a = array();
                $w = "";
                foreach ($where as $key => $value) {
                    $w .= " and " .$key. " = :".$key;
                    $a[":".$key] = $value;
                }
                $stmt =  $this->db->prepare("DELETE FROM $table WHERE 1=1 ".$w);
                $stmt->execute($a);
                $affected_rows = $stmt->rowCount();
                if($affected_rows<=0){
                    $response["status"] = "warning";
                    $response["message"] = "No row deleted";
                }else{
                    $response["status"] = "success";
                    $response["message"] = $affected_rows." row(s) deleted from database";
                }
            }catch(PDOException $e){
                $response["status"] = "error";
                $response["message"] = 'Delete Failed: ' .$e->getMessage();
            }
        }
        return $response;
    }

    //  This method Verifies that the required Colunms for insert and update are not empty
    
    function verifyRequiredParams($inArray, $requiredColumns) {
        $error = false;
        $errorColumns = "";
        foreach ($requiredColumns as $field) {
            if (!isset($inArray[$field]) || strlen(trim($inArray[$field])) <= 0) {
                $error = true;
                $errorColumns .= $field . ', ';
            }
        }

        if ($error) {
            $response = array();
            $response["status"] = "error";
            $response["message"] = 'Required field(s) ' . rtrim($errorColumns, ', ') . ' is missing or empty';
            print_r($response);
            exit;
        }
    }

    // function getWards(lga){
    //     $query = $this->db->query("SELECT DISTINCT wards FROM mytable WHERE lga = lga ");
    //     $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    //     if (count($rows) != 0) {
    //         $response["status"] = "success";
    //         echo "string";
    //         exit();
    //         $response["data"] = $rows;
    //     }
    //         return $response;
    // }
}



/**
 * Database Helper Function templates
 */
/*
select(table name, where clause as associative array)
selectAndOrder(table name, where clause as associative array, where clause)
insert(table name, data as associative array, required column names as array)
update(table name, column names as associative array, where clause as associative array, required columns as array)
delete(table name, where clause as array)
*/

$db = new dbHelper();
    
    // echo "string";

    function sortLGAWard($db){
        // $std = $db->select("mytable", array() );
        $lga = $db->selectAndOrder('mytable', 'DISTINCT lga', array(), '');

        // print_r (json_encode($lga['data']));
        $datas = array();
        foreach ($lga['data'] as $key => $value) {
            $responce = array();
            $responce['lga'] = $value['lga'];

            $lga_array = array();
            $assess = $db->selectAndOrder('mytable', 'DISTINCT ward', array('lga'=>$value['lga']) );
            array_push($lga_array, $assess['data']);
            $responce['wards'] = $lga_array;
            // }
            array_push($datas, $responce);
        }

        print_r(json_encode($datas));
        exit();
    }    



    function sortWardCommunity($db){
        // $std = $db->select("mytable", array() );
        $ward = $db->selectAndOrder('mytable', 'DISTINCT ward', array(), '');

        // print_r($std['data']);
        // print_r (json_encode($lga['data']));
        $datas = array();
        foreach ($ward['data'] as $key => $value) {
            $responce = array();
            $responce['ward'] = $value['ward'];

            $ward_array = array();
            $assess = $db->selectAndOrder('mytable', ' community', array('ward'=>$value['ward']), ' GROUP BY community' );
            array_push($ward_array, $assess['data']);
            $responce['communities'] = $ward_array;
            // }
            array_push($datas, $responce);
        }

        print_r(json_encode($datas));
        exit();
    }

sortLGAWard($db);

