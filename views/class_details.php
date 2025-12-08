<?php
// Include config and require lecturer role
require_once 'config.php';
requireRole('lecturer');


// -------------------------------------------
// Get class_id from URL parameter
// -------------------------------------------
if (!isset($_GET['class_id'])) {
    die("Class not specified.");
}
$class_id = intval($_GET['class_id']);


// -------------------------------------------
// Verify lecturer owns this class
// -------------------------------------------
$lecturer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM classes WHERE class_id = ? AND lecturer_id = ?");
$stmt->bind_param("ii", $class_id, $lecturer_id);
$stmt->execute();
$class_data = $stmt->get_result()->fetch_assoc();

if (!$class_data) {
    die("You do not have access to this class.");
}


// -------------------------------------------
// Handle grade update
// -------------------------------------------
$message = '';

if (isset($_POST['update_grade'])) {
    $grade = sanitize($_POST['grade']);
    $enrollment_id = intval($_POST['enrollment_id']);

    $stmt = $conn->prepare("UPDATE enrollments SET grade = ? WHERE enrollment_id = ?");
    $stmt->bind_param("si", $grade, $enrollment_id);

    if ($stmt->execute()) {
        $message = "<div class='success-message'>Grade updated successfully.</div>";
    } else {
        $message = "<div class='error-message'>Failed to update grade.</div>";
    }
}


// -------------------------------------------
// Handle attendance marking
// -------------------------------------------
if (isset($_POST['mark_attendance'])) {
    $enrollment_id = intval($_POST['attendance_enrollment_id']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO attendance (enrollment_id, date, status, notes) VALUES (?, CURDATE(), ?, ?)");
    $stmt->bind_param("iss", $enrollment_id, $status, $notes);

    if ($stmt->execute()) {
        $message = "<div class='success-message'>Attendance marked successfully.</div>";
    } else {
        $message = "<div class='error-message'>Failed to mark attendance.</div>";
    }
}


// -------------------------------------------
// Get enrolled students with attendance stats
// -------------------------------------------
$query = "
    SELECT e.enrollment_id, u.full_name, u.email, e.grade,
        (SELECT COUNT(*) FROM attendance WHERE enrollment_id = e.enrollment_id AND status='present') AS present_days,
        (SELECT COUNT(*) FROM attendance WHERE enrollment_id = e.enrollment_id) AS total_days
    FROM enrollments e
    INNER JOIN users u ON e.student_id = u.id
    WHERE e.class_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students = $stmt->get_result();

include 'header.php';
?>

<div class="dashboard">

    <!-- Breadcrumb -->
    <p><a href="lecturer_dashboard.php">Dashboard</a> → <?php echo $class_data['name']; ?></p>

    <!-- Class Info -->
    <h2><?php echo $class_data['name']; ?> (<?php echo $class_data['class_code']; ?>)</h2>
    <p><?php echo $class_data['description']; ?></p>
    <hr><br>

    <!-- Success or error messages -->
    <?php echo $message; ?>

    <h3>Enrolled Students</h3>

    <table class="data-table">
        <tr>
            <th>Student</th>
            <th>Email</th>
            <th>Attendance</th>
            <th>Grade</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $students->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['full_name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td>
                    <?php
                        if ($row['total_days'] > 0) {
                            $rate = round(($row['present_days'] / $row['total_days']) * 100);
                            echo "$rate% attendance";
                        } else {
                            echo "No records";
                        }
                    ?>
                </td>

                <!-- Grade Update Form -->
                <td>
                    <form method="POST">
                        <input type="hidden" name="enrollment_id" value="<?php echo $row['enrollment_id']; ?>">
                        <input type="text" name="grade" value="<?php echo $row['grade']; ?>" placeholder="Grade" style="width:70px;">
                        <button type="submit" name="update_grade" class="btn btn-primary">Save</button>
                    </form>
                </td>

                <!-- Attendance Button -->
                <td>
                    <button class="btn btn-secondary" onclick="openModal(<?php echo $row['enrollment_id']; ?>)">
                        Mark Attendance
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</div>

<!-- Attendance Modal -->
<div id="attendanceModal" style="
    display:none;
    position:fixed;
    top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
">
    <div style="
        background:white; padding:20px;
        width:350px; border-radius:8px;
    ">
        <h3>Mark Attendance</h3>

        <form method="POST">
            <input type="hidden" id="attendance_enrollment_id" name="attendance_enrollment_id">

            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes:</label>
                <textarea name="notes"></textarea>
            </div>

            <button type="submit" name="mark_attendance" class="btn btn-primary">Submit</button>
            <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openModal(enrollment_id) {
    document.getElementById('attendanceModal').style.display = 'flex';
    document.getElementById('attendance_enrollment_id').value = enrollment_id;
}

function closeModal() {
    document.getElementById('attendanceModal').style.display = 'none';
}
</script>

</body>
</html>
