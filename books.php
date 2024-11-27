<?php include 'login_first.php' ?>
<?php
include 'config.php';
include 'header.php';

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Check if book can be deleted
    $check_query = "SELECT COUNT(*) as count FROM borrowingtransactions WHERE book_id = '$id' AND status = 'borrowed'";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] == 0) {
        $delete_query = "DELETE FROM books WHERE book_id = '$id'";
        mysqli_query($conn, $delete_query);
        $_SESSION['success'] = "Book deleted successfully";
    } else {
        $_SESSION['error'] = "Cannot delete book - currently borrowed";
    }
    header('Location: books.php');
    exit();
}

// Handle Add Book
if (isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author_id = mysqli_real_escape_string($conn, $_POST['author_id']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $publication_year = mysqli_real_escape_string($conn, $_POST['publication_year']);
    $copies = mysqli_real_escape_string($conn, $_POST['copies']);
    
    $query = "INSERT INTO books (
                title, 
                author_id, 
                category_id, 
                publication_year,
                total_copies, 
                available_copies,
                status
              ) VALUES (
                '$title', 
                '$author_id', 
                '$category_id', 
                '$publication_year',
                '$copies', 
                '$copies',
                'available'
              )";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Book added successfully";
    } else {
        $_SESSION['error'] = "Error adding book";
    }
    header('Location: books.php');
    exit();
}

// Handle Edit Book
if (isset($_POST['edit_book'])) {
    $book_id = mysqli_real_escape_string($conn, $_POST['book_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author_id = mysqli_real_escape_string($conn, $_POST['author_id']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $publication_year = mysqli_real_escape_string($conn, $_POST['publication_year']);
    $copies = mysqli_real_escape_string($conn, $_POST['copies']);
    
    $query = "UPDATE books SET 
              title = '$title',
              author_id = '$author_id',
              category_id = '$category_id',
              publication_year = '$publication_year',
              total_copies = '$copies',
              available_copies = '$copies'
              WHERE book_id = '$book_id'";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Book updated successfully";
    } else {
        $_SESSION['error'] = "Error updating book";
    }
    header('Location: books.php');
    exit();
}

// Handle Search
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

?>

<div class="container mt-4">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Books Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
            Add New Book
        </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <form method="GET" class="d-flex">
            <input type="text" class="form-control" name="search" 
                   placeholder="Search by Title, Author, or Category" 
                   id="searchInput" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary ms-2">Search</button>
            <?php if($search): ?>
                <a href="books.php" class="btn btn-secondary ms-2">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Sort Dropdown -->
    <div class="mb-4">
        <label for="sort" class="form-label">Sort by</label>
        <select id="sort" class="form-select" onchange="sortBooks()">
            <option value="title">Title</option>
            <option value="author">Author</option>
            <option value="publication_year">Publication Year</option>
            <option value="total_copies">Total Copies</option>
            <option value="available_copies">Available Copies</option>
        </select>
    </div>

    <!-- Books Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Year</th>
                            <th>Total Copies</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookTableBody">
                        <?php
                        $query = "SELECT b.*, a.name as author_name, c.name as category_name 
                                 FROM books b 
                                 JOIN authors a ON b.author_id = a.author_id 
                                 JOIN categories c ON b.category_id = c.category_id";
                        
                        if ($search) {
                            $query .= " WHERE b.title LIKE '%$search%' 
                                      OR a.name LIKE '%$search%' 
                                      OR c.name LIKE '%$search%'";
                        }
                        
                        $query .= " ORDER BY b.book_id DESC";
                        $result = mysqli_query($conn, $query);

                        while ($row = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td><?php echo $row['book_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo $row['publication_year']; ?></td>
                                <td><?php echo $row['total_copies']; ?></td>
                                <td><?php echo $row['available_copies']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'available' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-book" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editBookModal"
                                            data-book='<?php echo json_encode($row); ?>'>
                                        Edit
                                    </button>
                                    <a href="books.php?delete=<?php echo $row['book_id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this book?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="book_id" id="edit_book_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <select class="form-select" name="author_id" id="edit_author_id" required>
                            <?php
                            $authors = mysqli_query($conn, "SELECT * FROM authors ORDER BY name");
                            while ($author = mysqli_fetch_assoc($authors)):
                                echo "<option value='" . $author['author_id'] . "'>" . htmlspecialchars($author['name']) . "</option>";
                            endwhile;
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id" id="edit_category_id" required>
                            <?php
                            $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                            while ($category = mysqli_fetch_assoc($categories)):
                                echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                            endwhile;
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Publication Year</label>
                        <input type="text" class="form-control" name="publication_year" id="edit_publication_year" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Copies</label>
                        <input type="number" class="form-control" name="copies" id="edit_copies" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="edit_book">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <select class="form-select" name="author_id" required>
                                <?php
                                $authors = mysqli_query($conn, "SELECT * FROM authors ORDER BY name");
                                while ($author = mysqli_fetch_assoc($authors)):
                                    echo "<option value='" . $author['author_id'] . "'>" . htmlspecialchars($author['name']) . "</option>";
                                endwhile;
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <?php
                                $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                                while ($category = mysqli_fetch_assoc($categories)):
                                    echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                                endwhile;
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="text" class="form-control" name="publication_year" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Copies</label>
                            <input type="number" class="form-control" name="copies" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_book">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Book Modal (similar to Add Book Modal) -->

<script>
    // Sorting with AJAX
    function sortBooks() {
        var sortBy = document.getElementById("sort").value;

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "books.php?sort_by=" + sortBy, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("bookTableBody").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>

<script>
// Handle Edit Book Button Click
document.querySelectorAll('.edit-book').forEach(button => {
    button.addEventListener('click', function() {
        const bookData = JSON.parse(this.getAttribute('data-book'));
        
        // Populate the edit form
        document.getElementById('edit_book_id').value = bookData.book_id;
        document.getElementById('edit_title').value = bookData.title;
        document.getElementById('edit_author_id').value = bookData.author_id;
        document.getElementById('edit_category_id').value = bookData.category_id;
        document.getElementById('edit_publication_year').value = bookData.publication_year;
        document.getElementById('edit_copies').value = bookData.total_copies;
    });
});

// Search filtering in the table
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

// Sorting function
function sortBooks() {
    var sortBy = document.getElementById("sort").value;
    var tbody = document.getElementById("bookTableBody");
    var rows = Array.from(tbody.getElementsByTagName("tr"));

    rows.sort(function(a, b) {
        var aValue = a.getElementsByTagName("td")[getColumnIndex(sortBy)].textContent;
        var bValue = b.getElementsByTagName("td")[getColumnIndex(sortBy)].textContent;
        
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return Number(aValue) - Number(bValue);
        }
        return aValue.localeCompare(bValue);
    });

    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}

function getColumnIndex(sortBy) {
    switch(sortBy) {
        case 'title': return 1;
        case 'author': return 2;
        case 'publication_year': return 4;
        case 'total_copies': return 5;
        case 'available_copies': return 6;
        default: return 1;
    }
}

// Alert auto-close
window.addEventListener('load', function() {
    // Auto close alerts after 3 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
});
</script>
