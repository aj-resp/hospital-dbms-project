<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           c.ComplaintID, 
           c.Description as Complaint,
           t.TreatmentType,
           d.Name as DoctorName, 
           d.StaffNo as DoctorStaffNo,
           ISNULL(pe.Position, 'No previous experience') as ExperiencePosition, 
           ISNULL(pe.Establishment, 'N/A') as Establishment, 
           CONVERT(varchar, pe.FromDate, 23) as FromDate,
           CONVERT(varchar, pe.ToDate, 23) as ToDate
    FROM complaint c
    INNER JOIN Treatment t ON c.ComplaintID = t.ComplaintID
    INNER JOIN doctor d ON t.DoctorStaffNo = d.StaffNo
    LEFT JOIN previous_experience pe ON d.StaffNo = pe.DoctorStaffNo
    ORDER BY c.ComplaintID, t.TreatmentType, pe.FromDate";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
?>

<h2>📊 Report 6: Complaints, Treatments Given, and Doctor Experience History</h2>

<?php if (count($results) == 0): ?>
    <div class="alert alert-info">No complaints/treatments data found.</div>
<?php else: ?>
    <table style="width: 100%;">
        <thead>
            <tr><th>Complaint ID</th><th>Complaint</th><th>Treatment</th><th>Doctor Name</th><th>Previous Position</th><th>Previous Establishment</th><th>From Date</th><th>To Date</th></tr>
        </thead>
        <tbody>
            <?php foreach($results as $row): ?>
            <tr>
                <td><?php echo $row['ComplaintID']; ?></td>
                <td><?php echo htmlspecialchars(substr($row['Complaint'], 0, 50)); ?>...</td>
                <td><?php echo htmlspecialchars($row['TreatmentType']); ?></td>
                <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                <td><?php echo $row['ExperiencePosition']; ?></td>
                <td><?php echo htmlspecialchars($row['Establishment']); ?></td>
                <td><?php echo $row['FromDate'] ?: 'N/A'; ?></td>
                <td><?php echo $row['ToDate'] ?: 'Present'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>