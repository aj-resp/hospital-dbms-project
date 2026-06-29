<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           c.ComplaintID, 
           c.Description as Complaint,
           t.TreatmentType
    FROM complaint c
    INNER JOIN Treatment t ON c.ComplaintID = t.ComplaintID
    GROUP BY c.ComplaintID, c.Description, t.TreatmentType
    ORDER BY c.ComplaintID, t.TreatmentType";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Get patients for this complaint
    $sql2 = "SELECT DISTINCT p.Name FROM patient p INNER JOIN complaint c2 ON p.PatientNo = c2.PatientNo WHERE c2.ComplaintID = ?";
    $stmt2 = sqlsrv_query($conn, $sql2, array($row['ComplaintID']));
    $patients = [];
    while ($p = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
        $patients[] = $p['Name'];
    }
    $row['PatientsList'] = implode(', ', $patients);
    $results[] = $row;
}
?>

<h2>📊 Report 8: Patients Grouped by Treatment Within Complaint</h2>

<?php
if (count($results) == 0) {
    echo '<div class="alert alert-info">No treatment data found.</div>';
} else {
    $current_complaint = '';
    foreach($results as $row):
        if($current_complaint != $row['ComplaintID']):
            if($current_complaint != ''): echo '</tbody></table></div>'; endif;
            $current_complaint = $row['ComplaintID'];
?>
            <div style="margin-top: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h3 style="color: #667eea;">Complaint <?php echo $row['ComplaintID']; ?>: <?php echo htmlspecialchars(substr($row['Complaint'], 0, 60)); ?>...</h3>
                <table style="width: 100%;">
                    <thead>
                        <tr><th>Treatment</th><th>Patients</th></tr>
                        </thead>
                    <tbody>
<?php endif; ?>
            <tr>
                <td><?php echo htmlspecialchars($row['TreatmentType']); ?></td>
                <td><?php echo htmlspecialchars($row['PatientsList']); ?></td>
            </tr>
<?php 
    endforeach;
    echo '</tbody></table></div>';
}
?>

<?php include 'footer.php'; ?>