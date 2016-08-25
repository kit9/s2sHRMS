<?php
// connect
$cs = mssql_connect("SYSTECH-DEV-PC\SQLEXPRESS",  "[sa]", "[sa@admin]");
exit();
// select
mssql_select_db ( '[database_name]', $cs ) or die ( 'Can not select database' );
//query
$sql = "SELECT * FROM [TABLENAME]";
$r = mssql_query ( $sql, $cs ) or die ( 'Query Error' );
// loop the result
while ( $row = mssql_fetch_array ( $r ) )
{
	/* do stuff */
}
