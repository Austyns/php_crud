<?php

	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', 'people');
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'test_class');
	
	// connect to database
	
	$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
        try {            
            $db = new PDO($dsn, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            // echo "WRONGGG";

            $response = 'Connection failed: ' . $e->getMessage();
            echo $response;

            exit;
        }
	

	// check that connection is estertblished
    // if ($db) {
    // 	echo "Good to go";
    // }else{
    // 	echo "Wrong";
    // }

	// carry out operation 
    // SELECT
    function getUsers($db){
    	$sql = "SELECT * FROM users";
	    $query = $db->query($sql);

	    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
	    print_r(json_encode($rows));
	    // $length = count($rows);
	    // echo $length;
    }
	   

    // INSERT 
    function createUser($db,$name,$email,$phone,$gender){
    	// echo "string";
    	$sql = "INSERT INTO users (name, email, phone, gender) values('$name', '$email', '$phone', '$gender')"; 

	    $query = $db->query($sql);

	    // print_r($query);
	    if ($query) {
	    	return "Inserted";	
	    }

    }

    // UPDATE
    function updateUser($db,$name,$email,$phone,$gender, $id){

    	$sql = "UPDATE users SET name = '$name', email='$email', phone='$phone', gender='$gender' WHERE id = '$id' "; 

	    $query = $db->query($sql);

	    if ($query) {
	    	echo "updated";	
	    }

    }

    // DELETE
    function deleteUser($db, $id){

    	$sql = "DELETE from user WHERE id = '$id' "; 

	    $query = $db->query($sql);

	    if ($query) {
	    	echo "deleted";	
	    }

    }

    // createUser($db,"Sam", "sam@gmail.com", "0909888", "male");
    // updateUser($db,"Abe", "abe@gmail.com", "09098788", "female", "5");
    // deleteUser($db,"4");
    
	// close database

    // echo $_SERVER['REQUEST_METHOD'];
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		// echo "string";
		return getUsers($db);	
	}elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
		// print_r($_POST);
		$name = $_POST['name'];
		$email = $_POST['email'];
		$phone = $_POST['phone'];
		$gender = $_POST['gender'];
		$status = createUser($db,$name,$email,$phone,$gender);
		print($status);
	}else{
		echo "Not allowed";
		exit();
	}

	exit();


 ?>