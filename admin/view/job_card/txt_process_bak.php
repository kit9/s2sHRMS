<?php
session_start();
ini_set('memory_limit', '-1'); // ini_set("memory_limit","12M"); '-1'
ini_set('max_execution_time', '-1');
/*
 * Author: Rajan Hossain
 * Page: Text File Process
 * Importing class library
 * Call main class 
 * Connection String
 */
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();
$f_date = "";
$rewrite_flag = '';
$permission_id = '';

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Collect rewrite flag
if (isset($_GET["rewrite_flag"])) {
    $rewrite_flag = $_GET["rewrite_flag"];
}


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        /*
         * Setting time zone to UTC than deafult GMT
         */
        date_default_timezone_set('UTC');

        //Read directory
        $FileArray = scandir("Attendance");
        $existsdate = array();
        foreach ($FileArray as $f) {
            $filename_array = explode(".", $f);
            $filename = $filename_array[0];

            if (strlen($filename) > 5) {
                $filename = "Attendance/" . $filename_array[0] . ".txt";
                $lines = file($filename); //file('http://192.168.1.165/payroll_attn/20111231.txt');

                foreach ($lines as $line) {
                    //Break down array elements
                    $values = explode("-", $line);
                    //Generate variables
                    $first_value = $values[0];
                    $date_in_process = substr($first_value, 1);
                    $second_value = $values[1];
                    $time_in_process_array = explode("]", $second_value);
                    $time_in_process = $time_in_process_array[0];
                    $emp_code_array = explode("/", $time_in_process_array[1]);
                    /*
                     * Add a zero to each employee code to make it 5 digit
                     * As per company requirements
                     */
                    $emp_code = $emp_code_array[0];
                    //Collect machine ID
                    $terminal_id = $emp_code_array[1];
                    $authentication_type = $emp_code_array[2];
                    /*
                     * Generate hour, minute, second
                     * implode hour, minute, second
                     * Store time in a variable
                     */

                    //Format time
                    $date = date("Y-m-d", strtotime($date_in_process));
                    $time = date("G:i:s", strtotime($time_in_process));

                    //Populate the array
                    $res = $con->existsByCondition("attendance_raw", " employee_id='$emp_code' AND date='$f_date AND time='$time'");
                    if ($res <= 0) {
                        $array = array(
                            "machine_id" => $terminal_id,
                            "date" => $date,
                            "time" => $time,
                            "employee_id" => $emp_code,
                            "result" => "1"
                        );
                        //Insert data into table :: dump as backup
                        $con->insert("attendance_raw", $array);
                    }
                }
                rename($filename, 'read/' . $filename);
                echo "file moved to specified directory.";
            }
        }
        
        /*
         * If rewrite flag is on : 
         * redirect to delete_records.php page
         * or else, redirect to jobcard.php page
         */
        if ($rewrite_flag == 0) {
            $con->redirect("jobcard.php?permission_id=" . $permission_id);
        } else {
            $con->redirect("delete_record.php?permission_id=" . $permission_id);
        }
        ?>
    </body>
</html>
