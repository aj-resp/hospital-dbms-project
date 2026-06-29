<?php
include 'header.php';
?>

<h2>Welcome to Ivor Paine Memorial Hospital Management System</h2>
<p style="margin: 20px 0;">This system provides comprehensive management of hospital operations including patient records, ward management, and staff tracking.</p>

<?php
$stmt = sqlsrv_query($conn, "SELECT COUNT(*) as count FROM patient");
$patients = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$stmt = sqlsrv_query($conn, "SELECT COUNT(*) as count FROM doctor");
$doctors = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$stmt = sqlsrv_query($conn, "SELECT COUNT(*) as count FROM nurse");
$nurses = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$stmt = sqlsrv_query($conn, "SELECT COUNT(*) as count FROM ward");
$wards = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
?>

<div class="stats-container">
    <div class="stats-card">
        <h3>👥 Patients</h3>
        <p><?php echo $patients['count']; ?></p>
    </div>
    <div class="stats-card">
        <h3>👨‍⚕️ Doctors</h3>
        <p><?php echo $doctors['count']; ?></p>
    </div>
    <div class="stats-card">
        <h3>👩‍⚕️ Nurses</h3>
        <p><?php echo $nurses['count']; ?></p>
    </div>
    <div class="stats-card">
        <h3>🏥 Wards</h3>
        <p><?php echo $wards['count']; ?></p>
    </div>
</div>

<?php include 'footer.php'; ?>