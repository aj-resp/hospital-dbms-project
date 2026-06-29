<?php
require_once 'database.php';
include 'header.php';

// Count doctors by position
$sql = "SELECT 
           ISNULL(Position, 'Unknown') as Position, 
           COUNT(*) as Count
        FROM doctor
        GROUP BY Position
        ORDER BY CASE ISNULL(Position, '')
            WHEN 'Consultant' THEN 1
            WHEN 'Registrar' THEN 2
            WHEN 'Assistant Registrar' THEN 3
            WHEN 'Senior Houseman' THEN 4
            WHEN 'Junior Houseman' THEN 5
            WHEN 'Student' THEN 6
            ELSE 7
        END";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}
$doctor_positions = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $doctor_positions[] = $row;
}

// Count nurses by type
$sql = "SELECT 'Day Sister' as Position, COUNT(*) as Count FROM day_sister
        UNION ALL
        SELECT 'Night Sister', COUNT(*) FROM night_sister
        UNION ALL
        SELECT 'Staff Nurse', COUNT(*) FROM staff_nurse
        UNION ALL
        SELECT 'Non-Registered Nurse', COUNT(*) FROM non_registered_nurse";

$stmt = sqlsrv_query($conn, $sql);
$nurse_positions = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $nurse_positions[] = $row;
}

// Count beds by status
$sql = "SELECT Status, COUNT(*) as Count FROM bed GROUP BY Status";
$stmt = sqlsrv_query($conn, $sql);
$bed_status = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $bed_status[] = $row;
}

// Count patients by ward
$sql = "SELECT 
           ISNULL(w.WardName, 'Unassigned') as WardName, 
           COUNT(p.PatientNo) as PatientCount
        FROM ward w
        LEFT JOIN patient p ON w.WardName = p.WardName
        GROUP BY w.WardName
        ORDER BY PatientCount DESC";
$stmt = sqlsrv_query($conn, $sql);
$patients_by_ward = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $patients_by_ward[] = $row;
}

$total_doctors = array_sum(array_column($doctor_positions, 'Count'));
$total_nurses = array_sum(array_column($nurse_positions, 'Count'));
$total_staff = $total_doctors + $total_nurses;
$total_beds = array_sum(array_column($bed_status, 'Count'));
?>

<h2>📊 Report 12: Different Positions Held by Staff and Count of Staff in Each Position</h2>

<div style="display: flex; gap: 30px; flex-wrap: wrap; margin-bottom: 30px;">
    <div style="flex: 1; min-width: 300px; background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #667eea; margin-top: 0;">👨‍⚕️ Medical Staff (Doctors)</h3>
        <?php if (count($doctor_positions) > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr><th>Position</th><th>Count</th><th>%</th></tr>
                </thead>
                <tbody>
                    <?php foreach($doctor_positions as $p): ?>
                    <tr>
                        <td><?php echo $p['Position']; ?></td>
                        <td style="text-align: center;"><strong><?php echo $p['Count']; ?></strong></td>
                        <td style="text-align: center;"><?php echo round(($p['Count'] / $total_doctors) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background: #f0f4ff; font-weight: bold;">
                        <td>Total Doctors</td>
                        <td style="text-align: center;"><?php echo $total_doctors; ?></td>
                        <td style="text-align: center;">100%</td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No doctor data found.</div>
        <?php endif; ?>
    </div>
    
    <div style="flex: 1; min-width: 300px; background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #667eea; margin-top: 0;">👩‍⚕️ Nursing Staff</h3>
        <?php if (count($nurse_positions) > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr><th>Position</th><th>Count</th><th>%</th></tr>
                </thead>
                <tbody>
                    <?php foreach($nurse_positions as $p): ?>
                    <tr>
                        <td><?php echo $p['Position']; ?></td>
                        <td style="text-align: center;"><strong><?php echo $p['Count']; ?></strong></td>
                        <td style="text-align: center;"><?php echo round(($p['Count'] / $total_nurses) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background: #f0f4ff; font-weight: bold;">
                        <td>Total Nurses</td>
                        <td style="text-align: center;"><?php echo $total_nurses; ?></td>
                        <td style="text-align: center;">100%</td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No nurse data found.</div>
        <?php endif; ?>
    </div>
</div>

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 10px; color: white; margin-bottom: 30px;">
    <h3 style="color: white; margin-top: 0;">📊 Hospital Staff Summary</h3>
    <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 15px;">
        <div style="flex: 1; text-align: center;">
            <p style="font-size: 14px; opacity: 0.9;">Total Medical Staff</p>
            <p style="font-size: 48px; font-weight: bold; margin: 5px 0;"><?php echo $total_doctors; ?></p>
            <p style="font-size: 12px;">Doctors</p>
        </div>
        <div style="flex: 1; text-align: center;">
            <p style="font-size: 14px; opacity: 0.9;">Total Nursing Staff</p>
            <p style="font-size: 48px; font-weight: bold; margin: 5px 0;"><?php echo $total_nurses; ?></p>
            <p style="font-size: 12px;">Nurses</p>
        </div>
        <div style="flex: 1; text-align: center;">
            <p style="font-size: 14px; opacity: 0.9;">Grand Total</p>
            <p style="font-size: 48px; font-weight: bold; margin: 5px 0;"><?php echo $total_staff; ?></p>
            <p style="font-size: 12px;">All Staff</p>
        </div>
    </div>
</div>

<div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <div style="flex: 1; background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #667eea; margin-top: 0;">🛏️ Bed Status</h3>
        <?php if (count($bed_status) > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr><th>Status</th><th>Count</th><th>Percentage</th></tr>
                </thead>
                <tbody>
                    <?php foreach($bed_status as $b): ?>
                    <tr>
                        <td><?php echo $b['Status']; ?></td>
                        <td style="text-align: center;"><strong><?php echo $b['Count']; ?></strong></td>
                        <td style="text-align: center;"><?php echo round(($b['Count'] / $total_beds) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No bed data found.</div>
        <?php endif; ?>
    </div>
    
    <div style="flex: 1; background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #667eea; margin-top: 0;">🏥 Patients by Ward</h3>
        <?php if (count($patients_by_ward) > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr><th>Ward Name</th><th>Patients</th></tr>
                </thead>
                <tbody>
                    <?php foreach($patients_by_ward as $w): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($w['WardName']); ?></td>
                        <td style="text-align: center;"><strong><?php echo $w['PatientCount']; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No patient ward data found.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>