<?php

class SSP {

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

    ////////////////////////////

    static function search($request, $columns) {
        $globalSearch = array();
        $dtColumns = self::pluck($columns, 'dt');

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

    ////////////////////////////

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




    ////////////////////////////

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

    ////////////////////////////

    static function pluck($array, $property) {
        $out = array();
        foreach ($array as $item) {
            $out[] = $item[$property];
        }
        return $out;
    }


    ////////////////////////////

    static function simple($request, $sql_details, $table, $primaryKey, $columns, $partitions) {
        $db = new mysqli($sql_details['host'], $sql_details['user'], $sql_details['pass'], $sql_details['db']);
        if ($db->connect_error) {
            die('Connect Error: ' . $db->connect_error);
        }

        $offset = isset($request['start']) ? intval($request['start']) : 0;
        $length = isset($request['length']) ? intval($request['length']) : 10;

        $limitClause = "LIMIT $offset, $length";
        $where = self::search($request, $columns);

        $partitionClause = count($partitions) > 0 ? "PARTITION (" . implode(', ', $partitions) . ")" : '';

        
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

        return array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => self::data_output($columns, $data)
        );
    }


}

?>
