<?php
require_once 'database.php';
include 'header.php';

$patient_data = null;
$complaints = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['patient_no'])) {
    $searched = true;
    $patient_no = $_POST['patient_no'];
    
    $sql = "SELECT 
               p.PatientNo,
               p.Name,
               CONVERT(varchar, p.DateOfBirth, 23) as DateOfBirth,
               p.Address,
               p.Phone,
               CONVERT(varchar, p.DateAdmitted, 23) as DateAdmitted,
               ISNULL(d.Name, 'Not assigned') as DoctorName, 
               ISNULL(d.Position, 'N/A') as DoctorPosition,
               ISNULL(w.WardName, 'Not assigned') as WardName, 
               w.Location,
               ISNULL(s.SpecialtyName, 'General') as SpecialtyName,
               b.BedNo, 
               b.Status as BedStatus,
               p.CareUnitNo
            FROM patient p
            LEFT JOIN doctor d ON p.DoctorStaffNo = d.StaffNo
            LEFT JOIN ward w ON p.WardName = w.WardName
            LEFT JOIN specialty s ON w.SpecialtyName = s.SpecialtyName
            LEFT JOIN bed b ON p.BedNo = b.BedNo
            WHERE p.PatientNo = ?";
    
    $stmt = sqlsrv_query($conn, $sql, array($patient_no));
    if ($stmt === false) {
        die("Error: " . print_r(sqlsrv_errors(), true));
    }
    $patient_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if($patient_data) {
        $sql2 = "SELECT 
                   c.ComplaintID, 
                   c.Description as Complaint,
                   t.TreatmentID, 
                   ISNULL(t.TreatmentType, 'No treatment') as TreatmentType, 
                   CONVERT(varchar, t.TreatmentDate, 23) as TreatmentDate,
                   CONVERT(varchar, t.TreatmentEnd, 23) as TreatmentEnd,
                   ISNULL(d.Name, 'Not assigned') as TreatingDoctor
                FROM complaint c
                LEFT JOIN Treatment t ON c.ComplaintID = t.ComplaintID
                LEFT JOIN doctor d ON t.DoctorStaffNo = d.StaffNo
                WHERE c.PatientNo = ?
                ORDER BY c.ComplaintID, t.TreatmentDate";
        
        $stmt2 = sqlsrv_query($conn, $sql2, array($patient_no));
        while ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
            $complaints[] = $row;
        }
    }
}

function calculateAge($dob) {
    if (!$dob) return 'N/A';
    $today = new DateTime();
    $birthdate = new DateTime($dob);
    $age = $today->diff($birthdate);
    return $age->y;
}
?>

<h2>📊 Report 10: Full Medical Details for a Patient</h2>

<form method="POST" action="" style="max-width: 400px; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px;">
    <div class="form-group">
        <label>Enter Patient Number:</label>
        <input type="number" name="patient_no" required placeholder="Enter Patient No" value="<?php echo isset($_POST['patient_no']) ? $_POST['patient_no'] : ''; ?>">
    </div>
    <button type="submit">🔍 View Medical Details</button>
</form>

<?php if ($searched): ?>
    <?php if ($patient_data && $patient_data['PatientNo']): ?>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; margin-bottom: 20px; color: white;">
            <h3 style="color: white; margin: 0;">👤 Patient: <?php echo htmlspecialchars($patient_data['Name']); ?></h3>
            <p style="margin: 5px 0 0 0;">Patient No: <?php echo $patient_data['PatientNo']; ?></p>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #667eea; margin-top: 0;">📋 Personal Information</h3>
            <table style="width: 100%; background: white;">
                <tr><td style="padding: 8px;"><strong>Date of Birth:</strong></td><td><?php echo $patient_data['DateOfBirth']; ?></td>
                    <td style="padding: 8px;"><strong>Age:</strong></td><td><?php echo calculateAge($patient_data['DateOfBirth']); ?> years</td></tr>
                <tr><td style="padding: 8px;"><strong>Address:</strong></td><td colspan="3"><?php echo htmlspecialchars($patient_data['Address']); ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Phone:</strong></td><td><?php echo $patient_data['Phone']; ?></td>
                    <td style="padding: 8px;"><strong>Date Admitted:</strong></td><td><?php echo $patient_data['DateAdmitted']; ?></td></tr>
            </table>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #667eea; margin-top: 0;">🏥 Admission Details</h3>
            <table style="width: 100%; background: white;">
                <tr><td style="padding: 8px;"><strong>Ward:</strong></td><td><?php echo $patient_data['WardName']; ?></td>
                    <td style="padding: 8px;"><strong>Location:</strong></td><td><?php echo $patient_data['Location']; ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Specialty:</strong></td><td><?php echo $patient_data['SpecialtyName']; ?></td>
                    <td style="padding: 8px;"><strong>Bed No:</strong></td><td><?php echo $patient_data['BedNo'] . ' (' . $patient_data['BedStatus'] . ')'; ?></td></tr>
                <tr><td style="padding: 8px;"><strong>Care Unit:</strong></td><td><?php echo $patient_data['CareUnitNo'] ?: 'N/A'; ?></td>
                    <td style="padding: 8px;"><strong>Doctor in Charge:</strong></td><td><?php echo htmlspecialchars($patient_data['DoctorName']) . ' (' . $patient_data['DoctorPosition'] . ')'; ?></td></tr>
            </table>
        </div>
        
        <h3>🏥 Medical History</h3>
        <?php if (count($complaints) > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr><th>Complaint ID</th><th>Complaint</th><th>Treatment</th><th>Treatment Date</th><th>Treatment End</th><th>Doctor</th></tr>
                </thead>
                <tbody>
                    <?php foreach($complaints as $c): ?>
                    <tr>
                        <td><?php echo $c['ComplaintID']; ?></td>
                        <td><?php echo htmlspecialchars(substr($c['Complaint'], 0, 50)); ?>...</td>
                        <td><?php echo htmlspecialchars($c['TreatmentType']); ?></td>
                        <td><?php echo $c['TreatmentDate'] ?: 'N/A'; ?></td>
                        <td><?php echo $c['TreatmentEnd'] ?: 'Ongoing'; ?></td>
                        <td><?php echo htmlspecialchars($c['TreatingDoctor']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No medical history recorded for this patient.</div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-error">Patient not found! Please check the Patient Number.</div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>