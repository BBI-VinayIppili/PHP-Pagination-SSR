<?php

require('sql-connection.php');
require('ssp.class.php');

$table = 'employee_records';
$primaryKey = 'id';

// Columns for DataTables (matching the table structure)
$columns = array(
    array('db' => 'id', 'dt' => 0, 'searchable' => true),
    array('db' => 'first_name', 'dt' => 1, 'searchable' => true),
    array('db' => 'last_name', 'dt' => 2, 'searchable' => true),
    array('db' => 'email', 'dt' => 3, 'searchable' => true),
    array('db' => 'gender', 'dt' => 4, 'searchable' => true),
);

// SQL connection details
$sql_details = array(
    'user' => 'root',
    'pass' => 'root',
    'db'   => 'employee_db',
    'host' => 'localhost',
);


function getPartitions($dbConn, $table) {
    $partitions = [];
    $result = $dbConn->query("
        SELECT PARTITION_NAME 
        FROM information_schema.PARTITIONS 
        WHERE TABLE_SCHEMA = '{$dbConn->real_escape_string($dbConn->database)}' 
        AND TABLE_NAME = '{$dbConn->real_escape_string($table)}' 
        AND PARTITION_NAME IS NOT NULL
    ");

    while ($row = $result->fetch_assoc()) {
        $partitions[] = $row['PARTITION_NAME'];
    }
    return $partitions;
}

$db = new mysqli($sql_details['host'], $sql_details['user'], $sql_details['pass'], $sql_details['db']);
$partitions = getPartitions($db, $table);
$db->close();


echo json_encode(
    SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $partitions)
);
?>
