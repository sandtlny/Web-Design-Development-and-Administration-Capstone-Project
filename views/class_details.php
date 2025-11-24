<?php
// ---------------------------------------------------------
// Include config and require lecturer role
// ---------------------------------------------------------
require_once 'config.php';
requireRole('lecturer');

// ---------------------------------------------------------
// Get class_id from URL parameter
// ---------------------------------------------------------
if (!isset($_GET['class_id'])) {
    header("Location: dashboard_lecturer.php");
    exit();
}

$class_id = sanitize($_GET['class_id']);

// ---------------------------------------------------------
// Verify lecturer owns this class
// ---------------------------------------------------------
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ? AND lecturer_id = ?");
$stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
$stmt->execute();
$classResult = $stmt->get_result();

if ($classResult->num_rows === 0) {
    header("Location: unauthorized.php");
    exit();
}

$classData = $classResult->fetch_assoc();

// ---------------------------------------------------------
// Handle grade update
// ---------------------------------------------------------
$message = "";

if (isset($_POST['update_grade'])) {
    $student_id = sanitize($_POST['student_id']);
    $grade      = sanitize($_POST['grade']);

    $update = $conn->prepare("UPDATE enrollments SET grade = ? WHERE student_id = ? AND class_id = ?");
    $update->bind_param("sii", $grade, $student_id, $class_id);

    if ($update->execute()) {
        $message = "<div class='card' style='background:#d4f8d4;'>Grade updated successfully.</div>";
    } else {
        $message = "<div class='card' style='background:#ffd4d4;'>Failed to update grade.</div>";
    }
}

// ---------------------------------------------------------
// Handle attendance marking
// ---------------------------------------------------------
if (isset($_POST['mark_attendance'])) {
    $student_id = sanitize($_POST['student_id']);
    $status     = sanitize($_POST['status']); // Present / Absent

    $mark = $conn->prepare("INSERT INTO attendance (student_id, class_id, status, date_marked) VALUES (?, ?, ?, NOW())");
    $mark->bind_param("iis", $student_id, $class_id, $status);

    if ($mark->execute()) {
        $message = "<div class='card' style='background:#d4f8d4;'>Attendance recorded.</div>";
    } else {
        $message = "<div class='card' style='background:#ffd4d4;'>Failed to record attendance.</div>";
    }
}

// ---------------------------------------------------------
// Get enrolled students with attendance statistics
// ---------------------------------------------------------
$query = "
    SELECT 
        u.id AS student_id,
        u.full_name,
        u.email,
        e.grade,
        (SELECT COUNT(*) FROM attendance WHERE student_id = e.student_id AND class_id = e.class_id AND status = 'Present') 
            AS present_count,
        (SELECT COUNT(*) FROM attendance WHERE student_id = e.student_id AND class_id = e.class_id) 
            AS total_attendance
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    WHERE e.class_id = ?
";

$students = $conn->prepare($query);
$students->bind_param("i", $class_id);
$students->execute();
$studentsResult = $students->get_result();

include 'header.php';
?>

<div class="dashboard-container">

    <!-- Breadcrumb -->
    <p><a href="dashboard_lecturer.php" class="btn">← Back to Classes</a></p>

    <!-- Class Information Header -->
    <div class="card">
        <h2><?php echo $classData['class_name']; ?></h2>
        <p><?php echo $classData['description']; ?></p>
        <p><strong>Schedule:</strong> <?php echo $classData['schedule']; ?></p>
        <p><strong>Room:</strong> <?php echo $classData['room']; ?></p>
    </div>

    <!-- Success / Error Messages -->
    <?php echo $message; ?>

    <h3>Enrolled Students</h3>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Attendance Rate</th>
                    <th>Grade</th>
                    <th>Update Grade</th>
                    <th>Mark Attendance</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $studentsResult->fetch_assoc()): ?>
                    <?php
                        $attendanceRate = ($row['total_attendance'] > 0) 
                            ? round(($row['present_count'] / $row['total_attendance']) * 100) 
                            : 0;
                    ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $attendanceRate; ?>%</td>

                        <!-- Grade Display -->
                        <td><?php echo $row['grade'] ?: "Not Assigned"; ?></td>

                        <!-- Grade Update Form -->
                        <td>
                            <form method="POST">
                                <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                <input type="text" name="grade" placeholder="A, B+, C..." required>
                                <button type="submit" name="update_grade" class="btn">Update</button>
                            </form>
                        </td>

                        <!-- Mark Attendance Button -->
                        <td>
                            <button class="btn" 
                                    onclick="openModal(<?php echo $row['student_id']; ?>)">
                                Mark
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" 
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">

    <div class="card" style="max-width:400px; width:90%; padding:20px;">
        <h3>Mark Attendance</h3>

        <form method="POST">
            <input type="hidden" id="modalStudentId" name="student_id">

            <label>Status:</label>
            <select name="status" required>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
            </select>

            <button name="mark_attendance" class="btn" style="margin-top:15px;">Submit</button>
            <button type="button" class="btn-danger" style="margin-top:15px;" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- JavaScript for Modal -->
<script>
function openModal(studentId) {
    document.getElementById('modalStudentId').value = studentId;
    document.getElementById('attendanceModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('attendanceModal').style.display = 'none';
}
</script>

</body>
</html>
