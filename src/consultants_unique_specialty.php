<?php
require_once 'database.php';
include 'header.php';

$sql = "SELECT 
           d.StaffNo, 
           d.Name, 
           c.SpecialtyName
    FROM consultant c
    JOIN doctor d ON c.StaffNo = d.StaffNo
    WHERE c.SpecialtyName IN (
        SELECT SpecialtyName
        FROM consultant
        WHERE SpecialtyName IS NOT NULL
        GROUP BY SpecialtyName
        HAVING COUNT(*) = 1
    )
    ORDER BY c.SpecialtyName";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Error: " . print_r(sqlsrv_errors(), true));
}

$results = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}
?>

<h2>📊 Report 5: Consultants with a Unique Specialty</h2>
<p style="margin-bottom: 20px;">These consultants work in specialties that have only one consultant in the hospital.</p>

<?php if (count($results) == 0): ?>
    <div class="alert alert-info">No consultants with unique specialties found.</div>
<?php else: ?>
    <table style="width: 100%;">
        <thead>
            <tr><th>Staff No</th><th>Consultant Name</th><th>Unique Specialty</th></tr>
        </thead>
        <tbody>
            <?php foreach($results as $row): ?>
            <tr>
                <td><?php echo $row['StaffNo']; ?></td>
                <td><?php echo htmlspecialchars($row['Name']); ?></td>
                <td><?php echo $row['SpecialtyName']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'footer.php'; ?>