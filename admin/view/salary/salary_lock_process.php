<?php

/* Author : Rajan Hossain
 * Date: 16 March 15
 * Assumption: All salary information is ready before this process happens
 */
session_start();
//Importing class library
include ('../../config/class.config.php');
date_default_timezone_set('UTC');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

/*
* Salary lock process starts here.
* Must be update things: lock flag to be set to true
* PF company contribution based on the business logic
* Loan or advance, installment to be sanctioned accordingly 
 */

if (isset($_POST["salary_lock"])){
    
    //Extract post array
    extract($_POST);
    
    //set variable conditions
    
    
}


