<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           w.WardName, 
           w.Location, 
           w.Capacity, 
           ISNULL(w.SpecialtyName, 'General') as SpecialtyName,
           ISNULL((SELECT TOP 1 n.Name FROM nurse n WHERE n.WardName = w.WardName AND n.StaffNo IN (SELECT StaffNo FROM day_sister)), 'Not Assigned') as DaySister,
           ISNULL((SELECT TOP 1 n.Name FROM nurse n WHERE n.WardName = w.WardName AND n.StaffNo IN (SELECT StaffNo FROM night_sister)), 'Not Assigned') as NightSister,
           ISNULL(CAST(cu.CareUnitNo AS varchar), 'N/A') as CareUnitNo, 
           ISNULL((SELECT TOP 1 n.Name FROM nurse n WHERE n.StaffNo IN (SELECT StaffNo FROM staff_nurse WHERE CareUnitNo = cu.CareUnitNo)), 'Not Assigned') as StaffNurseInCharge
    FROM ward w
    LEFT JOIN care_unit cu ON cu.WardName = w.WardName
    ORDER BY w.WardName, cu.CareUnitNo";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("<div class='alert alert-error'>Query Error: " . print_r(sqlsrv_errors(), true) . "</div>");
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
?>

<h2>📊 Report 2: Wards with Sisters, Care Units and Staff Nurses in Charge</h2>

<?php if (count($results) == 0): ?>
    <div class="alert alert-info">No ward data found.</div>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="background: #667eea; color: white; padding: 12px; text-align: left;">Ward Name</th>
                <th style="background: #667eea; color: white; padding: 12px; text-align: left;">Specialty</th>
                <th style="background: #667eea; color: white; padding: 12px; text-align: left;">Day Sister</th>
                <th style="background: #667eea; color: white; padding: 12px; text-align: left;">Night Sister</th>
                <th style="background: #667eea; color: white; padding: 12px; text-align: left;">Care Unit No</th>
                <th style="background: #667eea; color: white; padding: 12px; text-align: left;">Staff Nurse In Charge</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($results as $row): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><?php echo htmlspecialchars($row['WardName'] ?? 'N/A'); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($row['SpecialtyName'] ?? 'N/A'); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($row['DaySister'] ?? 'Not Assigned'); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($row['NightSister'] ?? 'Not Assigned'); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($row['CareUnitNo'] ?? 'N/A'); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($row['StaffNurseInCharge'] ?? 'Not Assigned'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>