<?php
// Include config and require student role
include 'config.php';
requireRole('student');

// Get enrollment_id from URL parameter
if (!isset($_GET['id'])) {
    header("Location: student_dashboard.php?error=Missing enrollment ID");
    exit;
}

$enrollment_id = sanitize($_GET['id']);

// Verify student owns this enrollment
$stmt = $conn->prepare("
    SELECT e.*, c.class_name, c.class_code, c.description, u.full_name AS lecturer_name
    FROM enrollments e
    JOIN classes c ON e.class_id = c.id
    JOIN users u ON c.lecturer_id = u.id
    WHERE e.id = ? AND e.student_id = ?
");
$stmt->bind_param("ii", $enrollment_id, $_SESSION['user_id']);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    header("Location: student_dashboard.php?error=Unauthorized access");
    exit;
}

// Get attendance records
$stmt2 = $conn->prepare("
    SELECT * FROM attendance 
    WHERE enrollment_id = ?
    ORDER BY date DESC
");
$stmt2->bind_param("i", $enrollment_id);
$stmt2->execute();
$attendance_records = $stmt2->get_result();

// Calculate attendance statistics
$total_classes = $attendance_records->num_rows;
$present = 0; 
$absent = 0;
$late = 0;

foreach ($attendance_records as $record) {
    if ($record['status'] === 'present') $present++;
    if ($record['status'] === 'absent') $absent++;
    if ($record['status'] === 'late') $late++;
}

$attendance_rate = ($total_classes > 0)
    ? round(($present / $total_classes) * 100)
    : 0;

include 'header.php';
?>

<div class="dashboard">

    <!-- Breadcrumb -->
    <p><a href="student_dashboard.php">Dashboard</a> › <strong>Attendance Details</strong></p>

    <!-- Class Header -->
    <h2><?php echo $enrollment['class_code'] . " - " . $enrollment['class_name']; ?></h2>
    <p><strong>Lecturer:</strong> <?php echo $enrollment['lecturer_name']; ?></p>

    <!-- Cards Section -->
    <div class="classes-grid">
        <div class="class-card">
            <h3>Total Classes</h3>
            <p><?php echo $total_classes; ?></p>
        </div>
        <div class="class-card">
            <h3>Attendance Rate</h3>
            <p><?php echo $attendance_rate; ?>%</p>
        </div>
        <div class="class-card">
            <h3>Your Grade</h3>
            <p><?php echo $enrollment['grade'] ?? 'Not Graded'; ?></p>
        </div>
    </div>

    <h3 style="margin-top:30px;">Attendance History</h3>

    <!-- Attendance Table -->
    <table class="data-table">
        <tr>
            <th>Date</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>

        <?php if ($total_classes == 0): ?>
            <tr>
                <td colspan="3" style="text-align:center;">No attendance records yet.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($attendance_records as $row): ?>
                <tr>
                    <td><?php echo $row['date']; ?></td>
                    <td>
                        <?php
                            $status = ucfirst($row['status']);
                            if ($row['status'] === 'present') echo "<span style='color:green;'>$status</span>";
                            elseif ($row['status'] === 'absent') echo "<span style='color:red;'>$status</span>";
                            else echo "<span style='color:orange;'>$status</span>";
                        ?>
                    </td>
                    <td><?php echo $row['notes'] ?: '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

</div>

</body>
</html>
