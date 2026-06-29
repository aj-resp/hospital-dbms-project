<?php
require_once 'database.php';
include 'header.php';

$results = [];
$selected_complaint = '';
$start_date = '';
$end_date = '';
$searched = false;

// Get complaints for dropdown
$sql = "SELECT ComplaintID, Description FROM complaint ORDER BY ComplaintID";
$stmt = sqlsrv_query($conn, $sql);
$complaints = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $complaints[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $searched = true;
    $selected_complaint = $_POST['complaint_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $sql = "SELECT 
               t.TreatmentID, 
               t.TreatmentType, 
               CONVERT(varchar, t.TreatmentDate, 23) as TreatmentDate,
               CONVERT(varchar, t.TreatmentEnd, 23) as TreatmentEnd,
               c.Description as Complaint, 
               d.Name as DoctorName
            FROM Treatment t
            INNER JOIN complaint c ON t.ComplaintID = c.ComplaintID
            INNER JOIN doctor d ON t.DoctorStaffNo = d.StaffNo
            WHERE t.ComplaintID = ? 
            AND t.TreatmentDate BETWEEN ? AND ?
            ORDER BY t.TreatmentDate";
    
    $stmt = sqlsrv_query($conn, $sql, array($selected_complaint, $start_date, $end_date));
    if ($stmt === false) {
        die("Error: " . print_r(sqlsrv_errors(), true));
    }
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
}
?>

<h2>📊 Report 11: Treatments Given for a Complaint Between Two Dates</h2>

<form method="POST" action="" style="max-width: 600px; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px;">
    <div class="form-group">
        <label>Select Complaint:</label>
        <select name="complaint_id" required>
            <option value="">-- Select Complaint --</option>
            <?php foreach($complaints as $c): ?>
                <option value="<?php echo $c['ComplaintID']; ?>" <?php echo ($selected_complaint == $c['ComplaintID']) ? 'selected' : ''; ?>>
                    <?php echo $c['ComplaintID'] . ' - ' . htmlspecialchars(substr($c['Description'], 0, 50)); ?>...
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Start Date:</label>
        <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
    </div>
    <div class="form-group">
        <label>End Date:</label>
        <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
    </div>
    <button type="submit">🔍 Search Treatments</button>
</form>

<?php if ($searched): ?>
    <?php if (count($results) > 0): ?>
        <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <strong>✅ Found <?php echo count($results); ?> treatment(s)</strong> for Complaint #<?php echo $selected_complaint; ?> between <?php echo $start_date; ?> and <?php echo $end_date; ?>.
        </div>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Treatment ID</th>
                    <th>Treatment Type</th>
                    <th>Treatment Date</th>
                    <th>Treatment End</th>
                    <th>Doctor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($results as $row): ?>
                <tr>
                    <td><?php echo $row['TreatmentID']; ?></td>
                    <td><?php echo htmlspecialchars($row['TreatmentType']); ?></td>
                    <td><?php echo $row['TreatmentDate']; ?></td>
                    <td><?php echo $row['TreatmentEnd'] ?: 'Ongoing'; ?></td>
                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No treatments found for Complaint #<?php echo $selected_complaint; ?> between <?php echo $start_date; ?> and <?php echo $end_date; ?>.</div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>