<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           d1.Name as ConsultantName, 
           d1.StaffNo as ConsultantStaffNo,
           ISNULL(c.SpecialtyName, 'N/A') as SpecialtyName,
           ISNULL(d2.Name, 'No doctors assigned') as DoctorName, 
           d2.StaffNo as DoctorStaffNo,
           ISNULL(d2.Position, 'N/A') as Position,
           CONVERT(varchar, btt.DateJoinedTeam, 23) as DateJoinedTeam
    FROM consultant c
    JOIN doctor d1 ON c.StaffNo = d1.StaffNo
    LEFT JOIN belongs_to_team btt ON c.StaffNo = btt.ConsultantStaffNo
    LEFT JOIN doctor d2 ON btt.DoctorStaffNo = d2.StaffNo
    ORDER BY d1.Name, d2.Name";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
?>

<h2>📊 Report 1: Consultants and Their Team Doctors</h2>

<?php
if (count($results) == 0) {
    echo '<div class="alert alert-info">No data found.</div>';
} else {
    $current_consultant = '';
    foreach($results as $row):
        if($current_consultant != $row['ConsultantName']):
            if($current_consultant != ''): echo '</tbody></table></div>'; endif;
            $current_consultant = $row['ConsultantName'];
?>
            <div style="margin-top: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h3 style="color: #667eea;">👨‍⚕️ Consultant: <?php echo htmlspecialchars($row['ConsultantName']); ?> 
                    (Staff: <?php echo $row['ConsultantStaffNo']; ?>) - <?php echo $row['SpecialtyName']; ?></h3>
                <table style="width: 100%;">
                    <thead>
                        <tr><th>Doctor Name</th><th>Staff No</th><th>Position</th><th>Date Joined Team</th></tr>
                        </thead>
                    <tbody>
<?php endif; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                            <td><?php echo $row['DoctorStaffNo']; ?></td>
                            <td><?php echo $row['Position']; ?></td>
                            <td><?php echo $row['DateJoinedTeam'] ?: 'N/A'; ?></td>
                        </tr>
<?php 
    endforeach;
    echo '</tbody></table></div>';
}
?>

<?php include 'footer.php'; ?>