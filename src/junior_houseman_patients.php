<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           d.StaffNo as DoctorStaffNo, 
           d.Name as DoctorName,
           p.PatientNo, 
           p.Name as PatientName, 
           ISNULL(p.CareUnitNo, 'N/A') as CareUnitNo,
           ISNULL(n.Name, 'No nurse assigned') as StaffNurseName, 
           n.StaffNo as NurseStaffNo
    FROM doctor d
    INNER JOIN patient p ON d.StaffNo = p.DoctorStaffNo
    LEFT JOIN care_unit cu ON p.CareUnitNo = cu.CareUnitNo
    LEFT JOIN staff_nurse sn ON cu.CareUnitNo = sn.CareUnitNo
    LEFT JOIN nurse n ON sn.StaffNo = n.StaffNo
    WHERE d.Position = 'Junior Houseman'
    ORDER BY d.Name, p.Name";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
?>

<h2>📊 Report 4: Junior Houseman and Their Patients (with Staff Nurse for Care Unit)</h2>

<?php if (count($results) == 0): ?>
    <div class="alert alert-info">No Junior Houseman doctors found with assigned patients.</div>
<?php else: ?>
    <table style="width: 100%;">
        <thead>
            <tr><th>Doctor Name</th><th>Doctor Staff No</th><th>Patient No</th><th>Patient Name</th><th>Care Unit No</th><th>Staff Nurse Name</th><th>Nurse Staff No</th></tr>
        </thead>
        <tbody>
            <?php foreach($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                <td><?php echo $row['DoctorStaffNo']; ?></td>
                <td><?php echo $row['PatientNo']; ?></td>
                <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                <td><?php echo $row['CareUnitNo']; ?></td>
                <td><?php echo htmlspecialchars($row['StaffNurseName']); ?></td>
                <td><?php echo $row['NurseStaffNo']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>