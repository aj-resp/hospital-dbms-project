<?php
include 'header.php';

// Get all wards for dropdown
$wards_sql = "SELECT WardName FROM ward ORDER BY WardName";
$wards_stmt = sqlsrv_query($conn, $wards_sql);

$ward = null;
$day_sister = null;
$night_sister = null;
$staff_nurses = [];
$non_reg_nurses = [];
$patients = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ward_name'])) {
    $ward_name = $_POST['ward_name'];
    
    $sql = "SELECT w.*, s.SpecialtyName FROM ward w LEFT JOIN specialty s ON w.SpecialtyName = s.SpecialtyName WHERE w.WardName = ?";
    $stmt = sqlsrv_query($conn, $sql, array($ward_name));
    $ward = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    $ds = sqlsrv_query($conn, "SELECT n.Name FROM day_sister ds JOIN nurse n ON ds.StaffNo = n.StaffNo WHERE n.WardName = ?", array($ward_name));
    $day_sister = sqlsrv_fetch_array($ds, SQLSRV_FETCH_ASSOC);
    
    $ns = sqlsrv_query($conn, "SELECT n.Name FROM night_sister ns JOIN nurse n ON ns.StaffNo = n.StaffNo WHERE n.WardName = ?", array($ward_name));
    $night_sister = sqlsrv_fetch_array($ns, SQLSRV_FETCH_ASSOC);
    
    $sn = sqlsrv_query($conn, "SELECT n.Name, sn.CareUnitNo FROM staff_nurse sn JOIN nurse n ON sn.StaffNo = n.StaffNo WHERE n.WardName = ?", array($ward_name));
    while($row = sqlsrv_fetch_array($sn, SQLSRV_FETCH_ASSOC)) {
        $staff_nurses[] = $row;
    }
    
    $nrn = sqlsrv_query($conn, "SELECT n.Name FROM non_registered_nurse nrn JOIN nurse n ON nrn.StaffNo = n.StaffNo WHERE n.WardName = ?", array($ward_name));
    while($row = sqlsrv_fetch_array($nrn, SQLSRV_FETCH_ASSOC)) {
        $non_reg_nurses[] = $row;
    }
    
    $pat = sqlsrv_query($conn, "SELECT p.PatientNo, p.Name, p.CareUnitNo, p.BedNo, p.DateAdmitted, d.Name as ConsultantName FROM patient p LEFT JOIN doctor d ON p.DoctorStaffNo = d.StaffNo WHERE p.WardName = ?", array($ward_name));
    while($row = sqlsrv_fetch_array($pat, SQLSRV_FETCH_ASSOC)) {
        $patients[] = $row;
    }
}
?>

<h2>🏥 WARD RECORD</h2>

<form method="POST" action="">
    <div class="form-group">
        <label>Select Ward:</label>
        <select name="ward_name" required>
            <option value="">-- Select Ward --</option>
            <?php while($w = sqlsrv_fetch_array($wards_stmt, SQLSRV_FETCH_ASSOC)): ?>
                <option value="<?php echo $w['WardName']; ?>"><?php echo $w['WardName']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit">🔍 View Ward Record</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $ward): ?>
    <div class="record-card">
        <h3>Ward Information</h3>
        <table>
            <tr><td><strong>Ward Name:</strong></td><td><?php echo $ward['WardName']; ?></td>
            <td><strong>Specialty:</strong></td><td><?php echo $ward['SpecialtyName']; ?></td></tr>
            <tr><td><strong>Location:</strong></td><td><?php echo $ward['Location']; ?></td>
            <td><strong>Capacity:</strong></td><td><?php echo $ward['Capacity']; ?></td></tr>
            <tr><td><strong>Day Sister:</strong></td><td><?php echo $day_sister ? $day_sister['Name'] : 'Not Assigned'; ?></td>
            <td><strong>Night Sister:</strong></td><td><?php echo $night_sister ? $night_sister['Name'] : 'Not Assigned'; ?></td></tr>
            <tr><td><strong>Staff Nurses:</strong></td><td colspan="3"><?php foreach($staff_nurses as $s) echo $s['Name'] . " (CU " . $s['CareUnitNo'] . ")<br>"; ?></td></tr>
            <tr><td><strong>Non-registered Nurses:</strong></td><td colspan="3"><?php foreach($non_reg_nurses as $n) echo $n['Name'] . "<br>"; ?></td></tr>
        </table>
    </div>
    
    <h3>Patient Information</h3>
    <table>
        <thead><tr><th>Patient No</th><th>Patient Name</th><th>Care Unit</th><th>Bed No</th><th>Consultant</th><th>Date Admitted</th></tr></thead>
        <tbody>
            <?php foreach($patients as $p): ?>
            <tr>
                <td><?php echo $p['PatientNo']; ?></td>
                <td><?php echo htmlspecialchars($p['Name']); ?></td>
                <td><?php echo $p['CareUnitNo']; ?></td>
                <td><?php echo $p['BedNo']; ?></td>
                <td><?php echo htmlspecialchars($p['ConsultantName']); ?></td>
                <td><?php echo ($p['DateAdmitted'] instanceof DateTime) ? $p['DateAdmitted']->format('Y-m-d') : ($p['DateAdmitted'] ?? 'N/A'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>