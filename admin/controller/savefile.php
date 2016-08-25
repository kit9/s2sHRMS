<?php
$files = $_FILES['std_photo'];

$targetfolder = '../uploads/student/';
    $filename = "uin_image". rand(1, 99999). basename($_FILES['std_photo']['name']);
    //$newfilename = rand(1,99999).end(explode(".",$_FILES["std_photo"]["name"]));
    $targetfolder = $targetfolder .$filename;
// Save the uploaded files

   
        move_uploaded_file($_FILES['std_photo']['tmp_name'],$targetfolder );
       $array= array("output"=>"uploaded","u_image"=>$filename);
       echo json_encode($array);