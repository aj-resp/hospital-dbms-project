<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           p.PatientNo, 
           p.Name as PatientName,
           COUNT(c.ComplaintID) as ComplaintCount
    FROM patient p
    INNER JOIN complaint c ON p.PatientNo = c.PatientNo
    GROUP BY p.PatientNo, p.Name
    HAVING COUNT(c.ComplaintID) > 1
    ORDER BY COUNT(c.ComplaintID) DESC";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Get complaints for this patient
    $sql2 = "SELECT Description FROM complaint WHERE PatientNo = ?";
    $stmt2 = sqlsrv_query($conn, $sql2, array($row['PatientNo']));
    $complaints = [];
    while ($c = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
        $complaints[] = $c['Description'];
    }
    $row['ComplaintsList'] = implode('; ', $complaints);
    
    // Get treatments for this patient
    $sql3 = "SELECT DISTINCT t.TreatmentType FROM complaint c2 LEFT JOIN Treatment t ON c2.ComplaintID = t.ComplaintID WHERE c2.PatientNo = ?";
    $stmt3 = sqlsrv_query($conn, $sql3, array($row['PatientNo']));
    $treatments = [];
    while ($t = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
        if ($t['TreatmentType']) $treatments[] = $t['TreatmentType'];
    }
    $row['TreatmentsList'] = implode('; ', $treatments);
    $results[] = $row;
}
?>

<h2>📊 Report 7: Patients with More Than One Complaint and Their Treatments</h2>

<?php if (count($results) == 0): ?>
    <div class="alert alert-info">No patients with multiple complaints found.</div>
<?php else: ?>
    <table style="width: 100%;">
        <thead>
            <tr><th>Patient No</th><th>Patient Name</th><th>Number of Complaints</th><th>Complaints</th><th>Treatments</th></tr>
        </thead>
        <tbody>
            <?php foreach($results as $row): ?>
            <tr>
                <td><?php echo $row['PatientNo']; ?></td>
                <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                <td style="text-align: center;"><?php echo $row['ComplaintCount']; ?></td>
                <td><?php echo htmlspecialchars($row['ComplaintsList']); ?></td>
                <td><?php echo htmlspecialchars($row['TreatmentsList']) ?: 'No treatments'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>