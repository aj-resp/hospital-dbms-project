<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$serverName = "localhost\\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "IvorPaineHospital",
    "Uid" => "",
    "PWD" => "",
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("<div style='color:red; padding:20px;'>Connection Failed!<br>" . print_r(sqlsrv_errors(), true) . "</div>");
}

// Function to execute queries
function runQuery($conn, $sql) {
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die("<div style='color:red;'>Query Error: " . print_r(sqlsrv_errors(), true) . "</div>");
    }
    return $stmt;
}

// Function to fetch all rows
function fetchAll($stmt) {
    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

// Function to fetch single row
function fetchOne($stmt) {
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}
?>