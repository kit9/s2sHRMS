<?php
$con=mysql_connect("localhost", "root", "");
mysql_select_db("doc_track",$con);
$arr = array();
$rs = mysql_query("SELECT country_id as country_id, country_name as country_name FROM tbl_country");
 
while($obj = mysql_fetch_object($rs)) {
	$arr[] = $obj;
}

header("Content-type: application/json"); 

echo "{\"data\":" .json_encode($arr). "}";
?>
