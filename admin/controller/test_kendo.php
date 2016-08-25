<?php

include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT * FROM testkendo");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $Id = mysqli_real_escape_string($_POST["Id"]);
    $Name = mysqli_real_escape_string($_POST["Name"]);
    $ImageUrl = mysqli_real_escape_string($_POST["ImageUrl"]);
    $FileName = mysqli_real_escape_string($_POST["FileName"]);
    
$rs = mysqli_query($con->open(), "UPDATE testkendo SET Name = '" . $Name . "',ImageUrl  = '" . $ImageUrl. "', FileName = '" . $FileName . "',  WHERE Id = " . $Id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for ID: " . $Id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);

//    echo "<pre>";
//    print_r($_FILES["files"]);
//    echo "</pre>";
    $Name = mysql_real_escape_string($request_vars["Name"]);
    $ImageUrl = mysql_real_escape_string($request_vars["ImageUrl"]);
    $FileName = mysql_real_escape_string($request_vars["FileName"]);
    

 $sql = "insert into testkendo(Name,ImageUrl,FileName) values('$Name','$ImageUrl','$FileName')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $Id = mysqli_insert_id($con->open());
        echo "" . $Id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $Id = mysqli_real_escape_string($request_vars["Id"]);

    $sql = "DELETE FROM wing WHERE Id = '" . $Id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $Id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>