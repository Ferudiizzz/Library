<?php include 'login_first.php' ?>
<?php
include 'config.php';
include 'header.php';
require_once 'functions.php';

// Get summary statistics
function getDashboardStats($conn) {
    $stats = [];
    
    // Total Books
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM books");
    $row = mysqli_fetch_assoc($result);
    $stats['total_books'] = $row['count'];
    
    // Total Students
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
    $row = mysqli_fetch_assoc($result);
    $stats['total_students'] = $row['count'];
    
    // Total Active Borrowings (including overdue)
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM borrowingtransactions WHERE status IN ('borrowed', 'overdue')");
    $row = mysqli_fetch_assoc($result);
    $stats['active_borrowings'] = $row['count'];
    
    // Total Overdue Books
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM borrowingtransactions WHERE status = 'overdue'");
    $row = mysqli_fetch_assoc($result);
    $stats['overdue_books'] = $row['count'];
    
    // Total Lost Books
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM borrowingtransactions WHERE book_condition = 'lost'");
    $row = mysqli_fetch_assoc($result);
    $stats['lost_books'] = $row['count'];
    
    // Damaged Books
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM borrowingtransactions WHERE book_condition = 'damaged'");
    $row = mysqli_fetch_assoc($result);
    $stats['damage_books'] = $row['count'];
    
    return $stats;
}

$stats = getDashboardStats($conn);
?>

<style>
    .dashboard-card {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        margin: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
</style>

<div class="container mt-4">
    <h2>Dashboard</h2>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="dashboard-card">
                <h4>Total Books</h4>
                <h2 class="text-primary"><?php echo $stats['total_books']; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h4>Total Borrowers</h4>
                <h2 class="text-success"><?php echo $stats['total_students']; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h4>Active Borrowings</h4>
                <h2 class="text-info"><?php echo $stats['active_borrowings']; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h4>Overdue Books</h4>
                <h2 class="text-danger"><?php echo $stats['overdue_books']; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h4>Lost Books</h4>
                <h2 class="text-danger"><?php echo $stats['lost_books']; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h4>Damage Books</h4>
                <h2 class="text-danger"><?php echo $stats['damage_books']; ?></h2>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>Recent Books</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT b.title, a.name as author_name, b.status 
                                 FROM books b 
                                 JOIN authors a ON b.author_id = a.author_id 
                                 ORDER BY b.book_id DESC LIMIT 5";
                        $result = mysqli_query($conn, $query);
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>{$row['title']}</td>
                                <td>{$row['author_name']}</td>
                                <td>{$row['status']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>Recent Students</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT first_name, last_name, contact_number, created_at 
                                 FROM students 
                                 ORDER BY student_id DESC LIMIT 5";
                        $result = mysqli_query($conn, $query);
                        while($row = mysqli_fetch_assoc($result)) {
                            $fullName = $row['first_name'] . ' ' . $row['last_name'];
                            $date = date('Y-m-d', strtotime($row['created_at']));
                            echo "<tr>
                                <td>{$fullName}</td>
                                <td>{$row['contact_number']}</td>
                                <td>{$date}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
mysqli_close($conn);
?>