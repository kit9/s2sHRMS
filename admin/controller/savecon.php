<?php
$files = $_FILES['files'];

$targetfolder = '../uploads/consultant_author/';
    $filename = basename($_FILES['files']['name']);
    $targetfolder = $targetfolder . $filename;
// Save the uploaded files

   
        move_uploaded_file($_FILES['files']['tmp_name'],$targetfolder );
       $array= array("output"=>"uploaded","con_auth_img"=>$filename);
         echo json_encode($array)
        
//$stmt = $dbh->prepare("INSERT INTO tbl_university (u_image) VALUES ($u_image)");
//$stmt->bindParam('u_image', $targetfolder);
//$stmt->execute();
        

?>

