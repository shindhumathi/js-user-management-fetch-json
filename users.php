<?php
header("Content-Type:application/json");
require "db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch($method)
{
    case 'GET':
        getusers($conn);
        break;
    case 'POST':
        createusers($conn);
        break;
    case 'PUT':
        updateusers($conn);
        break;
    case 'DELETE':
        deleteuser($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(["status"=>"error","msg"=>"Mthod not allowed"]);
}
function createusers($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'];
    $email = $data['email'];
    if(empty($data['name']) || empty($data['email']))
        {
            http_response_code(400);
            echo json_encode(["status"=>"error","msg"=>"Name and Email req"]);
            return;
        }
    $stmt=$conn->prepare("INSERT INTO users2(name,email) VALUES(?,?)");
    $stmt->bind_param("ss",$name,$email);
    if($stmt->execute())
        {
            http_response_code(200);
            echo json_encode(["status"=>"success","msg"=>"user created","id"=>$stmt->insert_id]);
        }
    else
        {
            http_response_code(500);
            echo json_encode(["status"=>"error","msg"=>"Email exsits"]);
        }
}

function getusers($conn)
{
    //pagination
    $page = isset($_GET['page'])?(int)$_GET['page']:1;
    $limit = isset($_GET['limit'])?(int)$_GET['limit']:5;
    if($page<1) $page=1;
    if($limit<1) $limit=5;

    $offset = ($page-1)*$limit;

    $totalres = $conn->query("SELECT COUNT(*) AS total FROM users2");

    $total = $totalres->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT * FROM users2 ORDER BY id DESC LIMIT ?,?");
    $stmt->bind_param("ii",$offset,$limit);
    $stmt->execute();
    $res=$stmt->get_result();
    $users=[];
    while($row=$res->fetch_assoc())
        {
            $users[]=$row;
        }
    echo json_encode(["status"=>"success","page"=>$page,"limit"=>$limit,
    "total"=>$total,"totalpages"=>ceil($total/$limit),"data"=>$users]);
}

function updateusers($conn)
{
    $data = json_decode(file_get_contents("php://input"),true);
    if(empty($data["id"]) || empty($data["name"]) || empty($data["email"]))
        {
            http_response_code(400);
            echo json_encode(["status"=>"error","msg"=>"ID and Name and Email req"]);
            return;
        }
    $id = $data["id"];
    $name = $data["name"];
    $email = $data["email"];
    
    $stmt=$conn->prepare("UPDATE users2 SET name=?,email=? WHERE id=?");
    $stmt->bind_param("ssi",$name,$email,$id);
    if($stmt->execute())
        {
            http_response_code(200);
            echo json_encode(["status"=>"success","msg"=>"user updated"]);
        }
    else
        {
            http_response_code(500);
            echo json_encode(["status"=>"error","msg"=>"update failed"]);
        }
}

function deleteuser($conn)
{
    $data=json_decode(file_get_contents("php://input"),true);
    $id = $data["id"];
    if(empty($data["id"]))
        {
            http_response_code(400);
            echo json_encode(["status"=>"error","msg"=>"user id required"]);
            return;
        }
    $stmt=$conn->prepare("DELETE FROM users2 WHERE id=?");
    $stmt->bind_param("i",$id);
    if($stmt->execute())
        {
            echo json_encode(["status"=>"success","msg"=>"user deleted"]);
        }
    else
        {
            http_response_code(500);
            echo json_encode(["status"=>"error","msg"=>"delete failed"]);
        }
}
?>