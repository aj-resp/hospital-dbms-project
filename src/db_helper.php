<?php
// Database helper functions for sqlsrv driver

// Execute query and return statement
function db_query($conn, $sql, $params = array()) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die("Query Error: " . print_r(sqlsrv_errors(), true));
    }
    return $stmt;
}

// Fetch all rows as associative array
function db_fetch_all($stmt) {
    $results = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    return $results;
}

// Fetch single row
function db_fetch_one($stmt) {
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

// Get row count
function db_num_rows($stmt) {
    return sqlsrv_num_rows($stmt);
}

// Get last insert ID (for identity columns)
function db_last_insert_id($conn) {
    $stmt = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() as id");
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ? $row['id'] : 0;
}

// Begin transaction
function db_begin_transaction($conn) {
    return sqlsrv_begin_transaction($conn);
}

// Commit transaction
function db_commit($conn) {
    return sqlsrv_commit($conn);
}

// Rollback transaction
function db_rollback($conn) {
    return sqlsrv_rollback($conn);
}
?>