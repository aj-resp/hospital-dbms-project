<?php
include 'header.php';

$cons_stmt = sqlsrv_query($conn, "SELECT d.StaffNo, d.Name, c.SpecialtyName FROM consultant c JOIN doctor d ON c.StaffNo = d.StaffNo");

$consultant = null;
$team_members = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staff_no'])) {
    $staff_no = $_POST['staff_no'];
    
    $sql = "SELECT d.*, c.SpecialtyName FROM consultant c JOIN doctor d ON c.StaffNo = d.StaffNo WHERE d.StaffNo = $staff_no";
    $stmt = sqlsrv_query($conn, $sql);
    $consultant = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    $team_sql = "SELECT d.StaffNo, d.Name, d.Position, btt.DateJoinedTeam, btt.DateLeaveTeam FROM belongs_to_team btt JOIN doctor d ON btt.DoctorStaffNo = d.StaffNo WHERE btt.ConsultantStaffNo = $staff_no ORDER BY btt.DateJoinedTeam DESC";
    $team_stmt = sqlsrv_query($conn, $team_sql);
    while($member = sqlsrv_fetch_array($team_stmt, SQLSRV_FETCH_ASSOC)) {
        // Get experience for this member
        $exp_sql = "SELECT * FROM previous_experience WHERE DoctorStaffNo = " . $member['StaffNo'] . " ORDER BY FromDate DESC";
        $exp_stmt = sqlsrv_query($conn, $exp_sql);
        $experience = [];
        while($exp = sqlsrv_fetch_array($exp_stmt, SQLSRV_FETCH_ASSOC)) {
            $experience[] = $exp;
        }
        
        // Get grades for this member
        $grade_sql = "SELECT g.ReviewDate, g.Grade, d.Name as ConsName FROM grades g JOIN consultant c ON g.ConsultantStaffNo = c.StaffNo JOIN doctor d ON c.StaffNo = d.StaffNo WHERE g.DoctorStaffNo = " . $member['StaffNo'] . " ORDER BY g.ReviewDate DESC";
        $grade_stmt = sqlsrv_query($conn, $grade_sql);
        $grades = [];
        while($grade = sqlsrv_fetch_array($grade_stmt, SQLSRV_FETCH_ASSOC)) {
            $grades[] = $grade;
        }
        
        $member['experience'] = $experience;
        $member['grades'] = $grades;
        $team_members[] = $member;
    }
}
?>

<h2>👨‍⚕️ CONSULTANT TEAM RECORD</h2>

<form method="POST" action="">
    <div class="form-group">
        <label>Select Consultant:</label>
        <select name="staff_no" required>
            <option value="">-- Select Consultant --</option>
            <?php while($c = sqlsrv_fetch_array($cons_stmt, SQLSRV_FETCH_ASSOC)): ?>
                <option value="<?php echo $c['StaffNo']; ?>"><?php echo $c['StaffNo'] . ' - ' . $c['Name']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit">🔍 View Team Record</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $consultant): ?>
    <div class="record-card">
        <h3>Consultant Information</h3>
        <table>
            <tr><td><strong>Staff No:</strong></td><td><?php echo $consultant['StaffNo']; ?></td>
            <td><strong>Name:</strong></td><td><?php echo htmlspecialchars($consultant['Name']); ?></td></tr>
            <tr><td><strong>Specialty:</strong></td><td><?php echo $consultant['SpecialtyName']; ?></td>
            <td><strong>Phone:</strong></td><td><?php echo $consultant['Phone']; ?></td></tr>
            <tr><td><strong>Position:</strong></td><td colspan="3"><?php echo $consultant['Position']; ?></td></tr>
        </table>
    </div>
    
    <h3>Team Members</h3>
    <?php foreach($team_members as $member): ?>
        <div style="border:1px solid #ddd; padding:15px; margin:10px 0; border-radius:5px;">
            <h4><?php echo htmlspecialchars($member['Name']); ?> (Staff No: <?php echo $member['StaffNo']; ?>)</h4>
            <p><strong>Position:</strong> <?php echo $member['Position']; ?></p>
            <p><strong>Date Joined Team:</strong> <?php echo $member['DateJoinedTeam']->format('Y-m-d'); ?></p>
            <?php if($member['DateLeaveTeam']): ?>
                <p><strong>Date Left:</strong> <?php echo $member['DateLeaveTeam']->format('Y-m-d'); ?></p>
            <?php endif; ?>
            
            <h5>Previous Experience</h5>
            <table>
                <thead><tr><th>From</th><th>To</th><th>Position</th><th>Establishment</th></tr></thead>
                <tbody>
                    <?php foreach($member['experience'] as $exp): ?>
                    <tr>
                        <td><?php echo $exp['FromDate']->format('Y-m-d'); ?></td>
                        <td><?php echo $exp['ToDate'] ? $exp['ToDate']->format('Y-m-d') : 'Present'; ?></td>
                        <td><?php echo $exp['Position']; ?></td>
                        <td><?php echo htmlspecialchars($exp['Establishment']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h5>Performance Grades</h5>
            <table>
                <thead><tr><th>Review Date</th><th>Grade</th><th>Given By</th></tr></thead>
                <tbody>
                    <?php foreach($member['grades'] as $grade): ?>
                    <tr>
                        <td><?php echo $grade['ReviewDate']->format('Y-m-d'); ?></td>
                        <td><?php echo $grade['Grade']; ?></td>
                        <td><?php echo htmlspecialchars($grade['ConsName']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>