<?php
error_reporting(-1);
ini_set('display_errors', 'On');
error_reporting( error_reporting() & ~E_NOTICE );
$conn =  new mysqli('localhost', 'chealth_user', 'ch34l7h_u53r', 'chealth');

//Check connection
if($conn->connect_error){
    die("Connection failed : " . $conn->connect_error);
}
//    echo "Connected!";
?>