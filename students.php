<?php include 'login_first.php' ?>

<?php

include 'config.php';
include 'header.php';

// Initialize search and filter variables
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Handle Add Student
if (isset($_POST['add_student'])) {
    // Retrieve form inputs
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'] ?? '';
    $grade_section = $_POST['grade_section'];
    $membership_status = $_POST['membership_status'];
    $max_books = (int)$_POST['max_books'];

    // Prepare the insert statement
    $stmt = $conn->prepare("INSERT INTO `students` (
        `first_name`, 
        `middle_name`, 
        `last_name`, 
        `email`, 
        `contact_number`, 
        `grade_section`, 
        `membership_status`, 
        `max_books`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssssi", 
        $first_name, 
        $middle_name, 
        $last_name, 
        $email, 
        $contact_number, 
        $grade_section, 
        $membership_status, 
        $max_books
    );

    if ($stmt->execute()) {
        header("Location: students.php?message=Student+added+successfully");
        exit();
    } else {
        header("Location: students.php?error=Error+adding+student:+" . urlencode($stmt->error));
        exit();
    }

}

// Handle Edit Student
if (isset($_POST['edit_student'])) {
    // Retrieve form inputs
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $grade_section = $_POST['grade_section'];
    $membership_status = $_POST['membership_status'];
    $max_books = (int)$_POST['max_books'];

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE `students` SET 
                                `first_name` = ?, 
                                `middle_name` = ?, 
                                `last_name` = ?, 
                                `email` = ?, 
                                `contact_number` = ?, 
                                `grade_section` = ?, 
                                `membership_status` = ?, 
                                `max_books` = ? 
                             WHERE `student_id` = ?");
    $stmt->bind_param("sssssssii", $first_name, $middle_name, $last_name, $email, $contact_number, $grade_section, $membership_status, $max_books, $student_id);

    if ($stmt->execute()) {
        header("Location: students.php?message=Student+updated+successfully");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating student: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
// Handle Delete Student
if (isset($_POST['delete_student'])) {
    // Retrieve the student ID
    $student_id = $_POST['student_id'];

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM `students` WHERE `student_id` = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        header("Location: students.php?message=Student+deleted+successfully");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting student: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Fetch students with current borrowed count and total penalties
$query = "SELECT 
            s.*,
            (SELECT COUNT(*) FROM `borrowingtransactions` bt WHERE bt.borrower_id = s.`student_id` AND bt.status = 'borrowed') AS `current_borrowed`,
            s.`total_penalties`
          FROM `students` s
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (
        `first_name` LIKE '%$search%' OR 
        `middle_name` LIKE '%$search%' OR 
        `last_name` LIKE '%$search%' OR 
        `email` LIKE '%$search%' OR 
        `student_id` LIKE '%$search%' OR 
        `grade_section` LIKE '%$search%'
    )";
}

if (!empty($status_filter)) {
    $query .= " AND `membership_status` = '$status_filter'";
}

$query .= " ORDER BY `created_at` DESC";
$result = mysqli_query($conn, $query);
?>
<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_GET['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Student list</h3>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus"></i> Add New Student
    </button>
</div>

<div class="mb-4">
    <form method="GET" class="d-flex">
        <input type="text" class="form-control" name="search" 
               placeholder="Search by Grade & Section, Student's Name" 
               id="searchInput" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary ms-2">Search</button>
        <?php if($search): ?>
            <a href="students.php" class="btn btn-secondary ms-2">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Add Student Modal -->
<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="addFirstName" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addMiddleName" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="addMiddleName" name="middle_name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="addLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="addLastName" name="last_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="addEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addContactNumber" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="addContactNumber" name="contact_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="addGradeSection" class="form-label">Grade & Section</label>
                        <input type="text" class="form-control" id="addGradeSection" name="grade_section" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addMembershipStatus" class="form-label">Membership Status</label>
                        <select class="form-select" id="addMembershipStatus" name="membership_status" required>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addMaxBooks" class="form-label">Max Books</label>
                        <input type="number" class="form-control" id="addMaxBooks" name="max_books" min="1" value="3" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editStudentForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="editStudentId">
                    
                    <div class="mb-3">
                        <label for="editFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editMiddleName" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="editMiddleName" name="middle_name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="editLastName" name="last_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editContactNumber" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="editContactNumber" name="contact_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGradeSection" class="form-label">Grade & Section</label>
                        <input type="text" class="form-control" id="editGradeSection" name="grade_section" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editMembershipStatus" class="form-label">Membership Status</label>
                        <select class="form-select" id="editMembershipStatus" name="membership_status" required>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editMaxBooks" class="form-label">Max Books</label>
                        <input type="number" class="form-control" id="editMaxBooks" name="max_books" min="1" required>
                    </div>
                    
                    <!-- Add other necessary fields as required -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_student" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Borrowed Books</h6>
                <div id="borrowedBooks">
        
                </div>
                <hr>
             
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div><div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Grade & Section</th>
                        <th>Contact Info</th>
                        <th>Status</th>
                        <th>Books</th>
                        <th>Max Books</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td>
                                    <?php 
                                        $full_name = htmlspecialchars($row['first_name']);
                                        if (!empty($row['middle_name'])) {
                                            $full_name .= ' ' . htmlspecialchars($row['middle_name']);
                                        }
                                        $full_name .= ' ' . htmlspecialchars($row['last_name']);
                                        echo $full_name;
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['grade_section']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['email']); ?><br>
                                    <small><?php echo htmlspecialchars($row['contact_number']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        if ($row['membership_status'] === 'active') {
                                            echo 'success';
                                        } elseif ($row['membership_status'] === 'expired') {
                                            echo 'warning';
                                        } else {
                                            echo 'danger';
                                        }
                                    ?>">
                                        <?php echo ucfirst($row['membership_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo (int)$row['current_borrowed']; ?> books</td>
                                <td><?php echo htmlspecialchars($row['max_books']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" 
                                        onclick='viewDetails(<?php echo $row['student_id']; ?>)'>
                                        View Details
                                    </button>
                                    <button class="btn btn-sm btn-primary" 
                                        onclick='editStudent(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        Edit
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                                        <button type="submit" name="delete_student" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this student?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Borrowed Books</h6>
                <div id="borrowedBooks">
                    <!-- Borrowed books will be populated here -->
                </div>
                <hr>
                <h6>Total Penalties</h6>
                <p id="totalPenalties">$0.00</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
function viewDetails(studentId) {
    console.log('Fetching details for student ID:', studentId); // Debugging log

    fetch('fetch_student_details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'student_id=' + encodeURIComponent(studentId)
    })
    .then(response => {
        console.log('Received response:', response);
        return response.json();
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.error) {
            alert(data.error);
            return;
        }

        // Populate borrowed books
        const borrowedBooksDiv = document.getElementById('borrowedBooks');
        borrowedBooksDiv.innerHTML = ''; // Clear previous content

        if (data.borrowed_books && data.borrowed_books.length > 0) {
            let table = '<table class="table table-striped">';
            table += '<thead><tr><th>Title</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr></thead><tbody>';

            data.borrowed_books.forEach(book => {
                table += `<tr>
                            <td>${escapeHtml(book.title)}</td>
                            <td>${escapeHtml(book.borrow_date)}</td>
                            <td>${escapeHtml(book.due_date)}</td>
                            <td>${escapeHtml(book.status)}</td>
                          </tr>`;
            });

            table += '</tbody></table>';
            borrowedBooksDiv.innerHTML = table;
        } else {
            borrowedBooksDiv.innerHTML = '<p>No books currently borrowed.</p>';
        }

        // Removed Populate total penalties

        // Show the modal
        const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
        detailsModal.show();
    })
    .catch(error => {
        console.error('Error fetching student details:', error);
        alert('An error occurred while fetching student details.');
    });
}

// Utility function to escape HTML to prevent XSS
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

document.getElementById('searchInput').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var rows = document.querySelectorAll('table tbody tr');

    rows.forEach(function(row) {
        var cells = row.getElementsByTagName('td');
        var match = false;

        for (var i = 1; i < cells.length - 1; i++) {
            if (cells[i].innerText.toLowerCase().includes(searchTerm)) {
                match = true;
                break;
            }
        }

        row.style.display = match ? '' : 'none';
    });
});

// Utility function to escape HTML to prevent XSS
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
document.getElementById('searchInput').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var rows = document.querySelectorAll('table tbody tr');

    rows.forEach(function(row) {
        var cells = row.getElementsByTagName('td');
        var match = false;

        for (var i = 1; i < cells.length - 1; i++) {
            if (cells[i].innerText.toLowerCase().includes(searchTerm)) {
                match = true;
                break;
            }
        }

        row.style.display = match ? '' : 'none';
    });
});
</script>

<script>
function editStudent(studentData) {
    // Parse the student data if it's a JSON string
    if (typeof studentData === 'string') {
        studentData = JSON.parse(studentData);
    }

    // Populate the form fields with the student's current data
    document.getElementById('editStudentId').value = studentData.student_id;
    document.getElementById('editFirstName').value = studentData.first_name;
    document.getElementById('editMiddleName').value = studentData.middle_name;
    document.getElementById('editLastName').value = studentData.last_name;
    document.getElementById('editEmail').value = studentData.email;
    document.getElementById('editContactNumber').value = studentData.contact_number;
    document.getElementById('editGradeSection').value = studentData.grade_section;
    document.getElementById('editMembershipStatus').value = studentData.membership_status;
    document.getElementById('editMaxBooks').value = studentData.max_books;

    // Initialize and show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
    editModal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Remove duplicate Bootstrap JS and CSS includes -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="path/to/your/custom.js"></script>