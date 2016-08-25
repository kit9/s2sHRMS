<?php
if (isset($_POST['btnLogin'])) {
    extract($_POST);
    if (empty($uName)) {
        $err = "Please Enter Username!";
    } elseif (empty($password)) {
        $err = "Password field is empty!";
    } else {

        $employee = $con->SelectAllByCondition("tmp_employee", " emp_code='$uName'");
        $password_hash =  $employee{0}->password;

        if(crypt($password, $password_hash) == $password_hash) {
            $_SESSION["user_type"] = $employee{0}->user_type;

            //Format today 
            $today = date("Y/m/d");
            $sys_date = date_create($today);
            $formatted_today = date_format($sys_date, 'Y-m-d');
            $zero = "0000-00-00";

            //Modified to look to main company table with history
            $companies_query = "SELECT
            ec_company_id
            FROM
            emp_company
            WHERE
            ec_emp_code = '$uName'
            AND (
            (
            ec_effective_start_date <= '$formatted_today'
            AND ec_effective_end_date >= '$formatted_today'
            )
            OR (
            ec_effective_start_date <= '$formatted_today'
            AND ec_effective_end_date = '$zero'
            )
            )";
            
            $companies = $con->QueryResult($companies_query);
            $_SESSION["company_id"] = $companies{0}->ec_company_id;
            $_SESSION["is_super"] = $employee{0}->is_super;
            
            foreach ($employee as $emp) {
                $emp_code = $emp->emp_code;
                $_SESSION["emp_code"] = $emp_code;
                $_SESSION["emp_code_nav"] = $emp_code;
            }
            $con->redirect("view/dashboard/index.php");
        } else {
            $err = 'Invalid User Name and Password';
        } 
    }
}
