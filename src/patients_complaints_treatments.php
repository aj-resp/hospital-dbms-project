<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           p.PatientNo, 
           p.Name as PatientName,
           c.ComplaintID, 
           c.Description as Complaint,
           ISNULL(t.TreatmentType, 'No treatment recorded') as TreatmentType, 
           CONVERT(varchar, t.TreatmentDate, 23) as TreatmentDate,
           CONVERT(varchar, t.TreatmentEnd, 23) as TreatmentEnd,
           ISNULL(d.Name, 'Not assigned') as DoctorName
    FROM patient p
    INNER JOIN complaint c ON p.PatientNo = c.PatientNo
    LEFT JOIN Treatment t ON c.ComplaintID = t.ComplaintID
    LEFT JOIN doctor d ON t.DoctorStaffNo = d.StaffNo
    ORDER BY p.PatientNo, c.ComplaintID, t.TreatmentDate";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
?>

<h2>📊 Report 3: Patients, Complaints, Treatments and Treatment Dates</h2>

<?php if (count($results) == 0): ?>
    <div class="alert alert-info">No patient data found.</div>
<?php else: ?>
    <table style="width: 100%;">
        <thead>
            <tr><th>Patient No</th><th>Patient Name</th><th>Complaint ID</th><th>Complaint</th><th>Treatment</th><th>Treatment Date</th><th>Treatment End</th><th>Doctor</th></tr>
        </thead>
        <tbody>
            <?php foreach($results as $row): ?>
            <tr>
                <td><?php echo $row['PatientNo']; ?></td>
                <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                <td><?php echo $row['ComplaintID']; ?></td>
                <td><?php echo htmlspecialchars($row['Complaint']); ?></td>
                <td><?php echo htmlspecialchars($row['TreatmentType']); ?></td>
                <td><?php echo $row['TreatmentDate'] ?: 'N/A'; ?></td>
                <td><?php echo $row['TreatmentEnd'] ?: 'Ongoing'; ?></td>
                <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>