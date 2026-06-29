<?php
include 'header.php';

$patient = null;
$complaints = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['patient_no'])) {
    $patient_no = $_POST['patient_no'];
    
    $sql = "SELECT p.*, d.Name as DoctorName, w.WardName, b.BedNo, cu.CareUnitNo 
            FROM patient p
            LEFT JOIN doctor d ON p.DoctorStaffNo = d.StaffNo
            LEFT JOIN ward w ON p.WardName = w.WardName
            LEFT JOIN bed b ON p.BedNo = b.BedNo
            LEFT JOIN care_unit cu ON p.CareUnitNo = cu.CareUnitNo
            WHERE p.PatientNo = ?";
    
    $stmt = sqlsrv_query($conn, $sql, array($patient_no));
    if ($stmt) {
        $patient = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
    
    if ($patient) {
        $sql2 = "SELECT c.ComplaintID, c.Description, t.TreatmentType, t.TreatmentDate, t.TreatmentEnd, d.Name as DoctorName
                 FROM complaint c
                 LEFT JOIN Treatment t ON c.ComplaintID = t.ComplaintID
                 LEFT JOIN doctor d ON t.DoctorStaffNo = d.StaffNo
                 WHERE c.PatientNo = ?
                 ORDER BY c.ComplaintID, t.TreatmentDate";
        
        $stmt2 = sqlsrv_query($conn, $sql2, array($patient_no));
        if ($stmt2) {
            while ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
                $complaints[] = $row;
            }
        }
    }
}
?>

<h2>👤 PATIENT RECORD</h2>

<form method="POST" action="">
    <div class="form-group">
        <label>Enter Patient Number:</label>
        <input type="number" name="patient_no" required placeholder="Enter Patient No">
    </div>
    <button type="submit">🔍 Search Patient</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <?php if ($patient): ?>
        <div class="record-card">
            <h3>Patient Information</h3>
            <table>
                <tr><td style="padding: 8px;"><strong>Patient No:</strong></td><td><?php echo $patient['PatientNo']; ?></td>
                <td style="padding: 8px;"><strong>Patient Name:</strong></td><td><?php echo htmlspecialchars($patient['Name']); ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Date of Birth:</strong></td><td><?php echo ($patient['DateOfBirth'] instanceof DateTime) ? $patient['DateOfBirth']->format('Y-m-d') : ($patient['DateOfBirth'] ?? 'N/A'); ?></td>
                <td style="padding: 8px;"><strong>Address:</strong></td><td><?php echo htmlspecialchars($patient['Address']); ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Phone:</strong></td><td><?php echo $patient['Phone']; ?></td>
                <td style="padding: 8px;"><strong>Doctor:</strong></td><td><?php echo htmlspecialchars($patient['DoctorName']); ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Ward:</strong></td><td><?php echo $patient['WardName']; ?></td>
                <td style="padding: 8px;"><strong>Bed No:</strong></td><td><?php echo $patient['BedNo']; ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Care Unit:</strong></td><td><?php echo $patient['CareUnitNo']; ?></td>
                <td style="padding: 8px;"><strong>Date Admitted:</strong></td><td><?php echo ($patient['DateAdmitted'] instanceof DateTime) ? $patient['DateAdmitted']->format('Y-m-d') : ($patient['DateAdmitted'] ?? 'N/A'); ?></td></tr>
            </table>
        </div>
        
        <h3>Medical History</h3>
        <?php if (count($complaints) > 0): ?>
        <table>
            <thead>
                <tr><th>Complaint ID</th><th>Complaint</th><th>Treatment</th><th>Doctor</th><th>Treatment Date</th><th>Treatment End</th></tr>
            </thead>
            <tbody>
                <?php foreach($complaints as $row): ?>
                <tr>
                    <td><?php echo $row['ComplaintID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Description']); ?></td>
                    <td><?php echo htmlspecialchars($row['TreatmentType']); ?></td>
                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                    <td><?php echo $row['TreatmentDate'] ? (($row['TreatmentDate'] instanceof DateTime) ? $row['TreatmentDate']->format('Y-m-d') : $row['TreatmentDate']) : ''; ?></td>
                    <td><?php echo $row['TreatmentEnd'] ? (($row['TreatmentEnd'] instanceof DateTime) ? $row['TreatmentEnd']->format('Y-m-d') : $row['TreatmentEnd']) : 'Ongoing'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No complaints recorded for this patient.</p>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-error">Patient not found!</div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>