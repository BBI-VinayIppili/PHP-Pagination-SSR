<?php

require('sql-connection.php');
require('ssp.class.php');


$table = 'employee_records';


$primaryKey = 'id';

// Columns for DataTables (matching the table structure)
$columns = array(
    array('db' => 'id', 'dt' => 0,'searchable'=>true),
    array('db' => 'first_name', 'dt' => 1,'searchable'=>true),
    array('db' => 'last_name', 'dt' => 2,'searchable'=>true),
    array('db' => 'email', 'dt' => 3,'searchable'=>true),
    array('db' => 'gender', 'dt' => 4,'searchable'=>true),
);

// SQL connection details
$sql_details = array(
    'user' => 'root',
    'pass' => 'root',
    'db'   => 'employee_db',
    'host' => 'localhost',
);



// Generate JSON response for DataTables
echo json_encode(
SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns)
);
?>
