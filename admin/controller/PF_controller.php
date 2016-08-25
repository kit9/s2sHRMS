<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT p.*,c.company_title,sh.PSH_header_title FROM provident_fund p LEFT JOIN company c on p.company_id=c.company_id LEFT JOIN payroll_salary_header sh on sh.PSH_id=p.salary_component_id order by company_id ASC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $PF_id = $request_vars["PF_id"];
    $array = array("PF_id" => $PF_id);
    $con->delete("provident_fund", $array);
}
//if ($verb == "POST") {
//    //declaring variables 
//    $company_id = '';
//    $PF_start = '';
//    $PF_after_1y = '';
//    $PF_after_2y = '';
//    $PF_after_3y = '';
//    $salary_component_id = '';
//    $pf_main = '';
//    //Form values
//    extract($_POST);
//    $open = $con->open();
//    $errors = array();
//    $query1 = "SELECT PF_start FROM provident_fund WHERE company_id='$company_id' AND salary_component_id='$salary_component_id' AND pf_main='$pf_main'";
//    $resul = mysqli_query($open, $query1);
//
//    if (mysqli_num_rows($resul) == '0') {
//        $query = "UPDATE provident_fund SET PF_start='$PF_start', PF_after_1y='$PF_after_1y',PF_after_2y='$PF_after_2y',PF_after_3y='$PF_after_3y'"
//                . " WHERE company_id='$company_id' AND salary_component_id='$salary_component_id'";
//        $rs = mysqli_query($open, $query);
//        if ($rs) {
//            echo json_encode($rs);
//            $con->close($open);
//        } else {
//            header("HTTP/1.1 500 Internal Server Error");
//            echo "Update failed one!";
//        }
//    } elseif (mysqli_num_rows($resul) == '1') {
//        $query = "UPDATE country SET country_name='$country_name', status='$status' WHERE country_id='$country_id'";
//        $rs = mysqli_query($open, $query);
//        if ($rs) {
//            echo json_encode($rs);
//            $con->close($open);
//        } else {
//            header("HTTP/1.1 500 Internal Server Error");
//            echo "Update failed two!";
//        }
//    } else {
//        $errors = array("error" => "yes", "message" => "Given Provident Fund Details Already Exists!");
//        echo json_encode($errors);
//    }
//}
//if ($verb == "PUT"){
//    $request_vars = Array();
//    parse_str(file_get_contents('php://input'), $request_vars);
//
//    $company_id = $request_vars["company_id"];
//    $PF_start = $request_vars["PF_start"];
//    $PF_after_1y = $request_vars["PF_after_1y"];
//    $PF_after_2y = $request_vars["PF_after_2y"];
//    $PF_after_3y = $request_vars["PF_after_3y"];
//    $salary_component_id = $request_vars["salary_component_id"];
//    $pf_main = $request_vars["pf_main"];
//    
//    $errors = array();
//    $open = $con->open();
//    $query1 = "SELECT PF_start FROM provident_fund WHERE ='" . mysqli_real_escape_string($open, $country_name) . "'";
//    $resul = mysqli_query($open, $query1);
//
//    if (mysqli_num_rows($resul) == '0') {
//        $query = "INSERT INTO country SET ";
//        $query .= "country_name='" . mysqli_real_escape_string($open, $country_name) . "',";
//        $query .= "status='" . mysqli_real_escape_string($open, $status) . "'";
//        $result = mysqli_query($open, $query);
//
//        if ($result) {
//            $country_id = mysqli_insert_id($con->open());
//            echo "" . $country_id . "";
//            $con->close($open);
//        } else {
//            header("HTTP/1.1 500 Internal Server Error");
//            echo "Insert Failed.";
//        }
//    } else {
//        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
//        echo json_encode($errors);
//    }
//}


?>