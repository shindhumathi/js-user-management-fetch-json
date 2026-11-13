<?php
$conn = new mysqli("hostname","username","password","dbname");
if($conn->connect_error)
    {
        http_response_code(500);
        echo json_encode(["status"=>"Error","message"=>"Db connectio fail"]);
        exit;
    }
?>