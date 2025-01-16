<?php 
class SSP {

// Function to output data in the correct format for DataTables
static function data_output($columns, $data) {
    $out = array();
    foreach ($data as $row) {
        $rowData = array();
        foreach ($columns as $col) {
            $rowData[] = $row[$col['db']];
        }
        $out[] = $rowData;
    }
    return $out;
}

// Function to build the search query
static function search($request, $columns) {
    $globalSearch = array();
    if (isset($request['search']) && $request['search']['value'] != '') {
        $str = $request['search']['value'];
        foreach ($columns as $column) {
            if ($column['searchable'] == 'true') {
                $globalSearch[] = "`" . $column['db'] . "` LIKE '%" . $str . "%'";
            }
        }
    }

    $where = count($globalSearch) ? '(' . implode(' OR ', $globalSearch) . ')' : '';
    return $where !== '' ? 'WHERE ' . $where : '';
}

// Function to execute the SQL query and return the results
static function sql_exec($db, $sql) {
    $result = $db->query($sql);
    if (!$result) {
        die('Query Error: ' . $db->error);
    }
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Function to handle sorting in PHP
static function php_sort($data, $columns, $order) {
    if (!isset($order) || !count($order)) {
        return $data;
    }

    $orderData = $order[0];
    $columnIdx = intval($orderData['column']);
    $sortDir = $orderData['dir'] === 'asc' ? SORT_ASC : SORT_DESC;

    $sortColumn = $columns[$columnIdx]['db'];

    usort($data, function ($a, $b) use ($sortColumn, $sortDir) {
        $valA = $a[$sortColumn] ?? '';
        $valB = $b[$sortColumn] ?? '';

        if ($valA == $valB) {
            return 0;
        }

        return ($valA < $valB ? -1 : 1) * ($sortDir === SORT_ASC ? 1 : -1);
    });

    return $data;
}

// Function to pluck specific property from an array
static function pluck($array, $property) {
    $out = array();
    foreach ($array as $item) {
        $out[] = $item[$property];
    }
    return $out;
}

// Main function to query the database and handle partitioning
static function simple($request, $sql_details, $table, $primaryKey, $columns, $partitions) {
    $db = new mysqli($sql_details['host'], $sql_details['user'], $sql_details['pass'], $sql_details['db']);
    if ($db->connect_error) {
        die('Connect Error: ' . $db->connect_error);
    }

    $offset = isset($request['start']) ? intval($request['start']) : 0;
    $length = isset($request['length']) ? intval($request['length']) : 10;
    $limitClause = "LIMIT $offset, $length";

  
    $where = self::search($request, $columns);

    // Determine the partitions to query
    $partitionClause = '';
    if (!empty($partitions)) {
        
        if (isset($request['search']['value']) && is_numeric($request['search']['value'])) {
            
            $id = intval($request['search']['value']);
            $partition = self::determine_partition($id);
            if (in_array($partition, $partitions)) {
                $partitionClause = "PARTITION ($partition)";
            }
        }
    }

   
    $dataSql = "SELECT `" . implode("`, `", self::pluck($columns, 'db')) . "` 
                FROM `$table` $partitionClause $where $limitClause";
    $data = self::sql_exec($db, $dataSql);

   
    $data = self::php_sort($data, $columns, $request['order'] ?? []);


    $filterSql = "SELECT COUNT(`{$primaryKey}`) AS cnt FROM `$table` $partitionClause $where";
    $resFilterLength = self::sql_exec($db, $filterSql);
    $recordsFiltered = $resFilterLength[0]['cnt'];

  
    $totalSql = "SELECT COUNT(`{$primaryKey}`) AS cnt FROM `$table` $partitionClause";
    $resTotalLength = self::sql_exec($db, $totalSql);
    $recordsTotal = $resTotalLength[0]['cnt'];

    $db->close();

    // Return the response in DataTables format
    return array(
        "draw" => intval($request['draw']),
        "recordsTotal" => intval($recordsTotal),
        "recordsFiltered" => intval($recordsFiltered),
        "data" => self::data_output($columns, $data)
    );
}

static function determine_partition($id) {
  
    $partitionIndex = floor($id / 10000); 
    return 'p' . $partitionIndex; 
   
}
}
