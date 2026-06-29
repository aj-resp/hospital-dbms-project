<?php
require_once 'database.php';
include 'header.php';

// Get all doctors (non-consultants)
$sql = "SELECT StaffNo, Name FROM doctor WHERE ISNULL(Position, '') != 'Consultant' ORDER BY Name";
$stmt = sqlsrv_query($conn, $sql);
$doctors = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $doctors[] = $row;
}

$results = [];
$doctor_name = '';
$selected_doctor = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doctor_staff_no'])) {
    $selected_doctor = $_POST['doctor_staff_no'];
    
    $sql = "SELECT 
               CONVERT(varchar, g.ReviewDate, 23) as ReviewDate, 
               g.Grade, 
               d.Name as ConsultantName
            FROM grades g
            INNER JOIN consultant c ON g.ConsultantStaffNo = c.StaffNo
            INNER JOIN doctor d ON c.StaffNo = d.StaffNo
            WHERE g.DoctorStaffNo = ?
            ORDER BY g.ReviewDate DESC";
    
    $stmt = sqlsrv_query($conn, $sql, array($selected_doctor));
    if ($stmt === false) {
        die("Error: " . print_r(sqlsrv_errors(), true));
    }
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    
    $sql2 = "SELECT Name FROM doctor WHERE StaffNo = ?";
    $stmt2 = sqlsrv_query($conn, $sql2, array($selected_doctor));
    $doctor_row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    $doctor_name = $doctor_row['Name'];
}
?>

<h2>📊 Report 9: Performance History for a Particular Doctor</h2>

<form method="POST" action="" style="max-width: 400px; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px;">
    <div class="form-group">
        <label>Select Doctor:</label>
        <select name="doctor_staff_no" required>
            <option value="">-- Select Doctor --</option>
            <?php foreach($doctors as $d): ?>
                <option value="<?php echo $d['StaffNo']; ?>" <?php echo ($selected_doctor == $d['StaffNo']) ? 'selected' : ''; ?>>
                    <?php echo $d['StaffNo'] . ' - ' . htmlspecialchars($d['Name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit">🔍 View Performance History</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <?php if (count($results) > 0): ?>
        <h3>Performance History for: <?php echo htmlspecialchars($doctor_name); ?></h3>
        <table style="width: 100%;">
            <thead>
                <tr><th>Review Date</th><th>Grade</th><th>Assigned By Consultant</th></tr>
            </thead>
            <tbody>
                <?php foreach($results as $row): ?>
                <tr>
                    <td><?php echo $row['ReviewDate']; ?></td>
                    <td>
                        <?php 
                        $grade = $row['Grade'];
                        $color = ($grade == 'A') ? '#28a745' : (($grade == 'B' || $grade == 'C') ? '#ffc107' : '#dc3545');
                        echo '<span style="background: ' . $color . '; color: white; padding: 3px 10px; border-radius: 20px;">' . htmlspecialchars($grade) . '</span>';
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['ConsultantName']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No performance records found for this doctor.</div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>