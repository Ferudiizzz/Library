<?php
include 'config.php';
include 'header.php';

// Function to get penalty status class
function getPenaltyStatusClass($status) {
    return $status == 'paid' ? 'bg-success' : 'bg-danger';
}

// Function to get penalty reason class
function getPenaltyReasonClass($reason) {
    switch($reason) {
        case 'overdue':
            return 'bg-warning';
        case 'damage':
            return 'bg-danger';
        case 'loss':
            return 'bg-dark';
        default:
            return 'bg-secondary';
    }
}

// Base query
$where_clause = "1=1";
if (isset($_GET['type'])) {
    $type = $conn->real_escape_string($_GET['type']);
    $where_clause .= " AND p.penalty_reason = '$type'";
}
if (isset($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where_clause .= " AND p.status = '$status'";
}

// Main query
$query = "
    SELECT 
        p.*,
        s.first_name,
        s.last_name,
        b.title as book_title,
        bt.book_condition,
        bt.due_date,
        bt.return_date
    FROM penalties p
    LEFT JOIN students s ON p.borrower_id = s.student_id
    LEFT JOIN books b ON p.book_id = b.book_id
    LEFT JOIN borrowingtransactions bt ON p.transaction_id = bt.transaction_id
    WHERE $where_clause
    ORDER BY p.created_at DESC
";

$result = $conn->query($query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid,
    SUM(CASE WHEN penalty_reason = 'overdue' THEN 1 ELSE 0 END) as overdue,
    SUM(CASE WHEN penalty_reason IN ('damage', 'loss') THEN 1 ELSE 0 END) as damages
    FROM penalties";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

    <style>
        .penalty-card {
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        .penalty-card.overdue { border-left-color: #ffc107; }
        .penalty-card.damage { border-left-color: #dc3545; }
        .penalty-card.loss { border-left-color: #212529; }
        .badge { font-size: 0.9em; }
        .details-row {
            font-size: 0.9em;
            margin-bottom: 0.5rem;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .sort-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .sort-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .sort-card.active {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-exclamation-triangle text-warning"></i> Library Alerts & Penalties</h2>
            </div>
        </div>

        <!-- Filter Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Sort & Filter</h5>
                        <div class="row g-2">
                            <div class="col-md-2">
                                <div class="card cursor-pointer sort-card <?php echo !isset($_GET['type']) && !isset($_GET['status']) ? 'active' : ''; ?>" 
                                     onclick="window.location.href='alerts.php'">
                                    <div class="card-body text-center">
                                        <i class="fas fa-list fa-2x mb-2"></i>
                                        <h6>All Penalties</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card cursor-pointer sort-card <?php echo isset($_GET['type']) && $_GET['type'] == 'overdue' ? 'active' : ''; ?>" 
                                     onclick="window.location.href='alerts.php?type=overdue'">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock fa-2x mb-2 text-warning"></i>
                                        <h6>Overdue Only</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card cursor-pointer sort-card <?php echo isset($_GET['type']) && $_GET['type'] == 'damage' ? 'active' : ''; ?>" 
                                     onclick="window.location.href='alerts.php?type=damage'">
                                    <div class="card-body text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i>
                                        <h6>Damaged Books</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card cursor-pointer sort-card <?php echo isset($_GET['type']) && $_GET['type'] == 'loss' ? 'active' : ''; ?>" 
                                     onclick="window.location.href='alerts.php?type=loss'">
                                    <div class="card-body text-center">
                                        <i class="fas fa-times-circle fa-2x mb-2 text-dark"></i>
                                        <h6>Lost Books</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card cursor-pointer sort-card <?php echo isset($_GET['status']) && $_GET['status'] == 'paid' ? 'active' : ''; ?>" 
                                     onclick="window.location.href='alerts.php?status=paid'">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check fa-2x mb-2 text-success"></i>
                                        <h6>Paid Penalties</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card cursor-pointer sort-card <?php echo isset($_GET['status']) && $_GET['status'] == 'unpaid' ? 'active' : ''; ?>" 
                                     onclick="window.location.href='alerts.php?status=unpaid'">
                                    <div class="card-body text-center">
                                        <i class="fas fa-times fa-2x mb-2 text-danger"></i>
                                        <h6>Unpaid Penalties</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penalties List -->
        <div class="row">
            <div class="col-12">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="card penalty-card <?php echo $row['penalty_reason']; ?>">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="card-title">
                                            <span class="badge <?php echo getPenaltyReasonClass($row['penalty_reason']); ?>">
                                                <?php echo ucfirst($row['penalty_reason']); ?>
                                            </span>
                                            <span class="badge <?php echo getPenaltyStatusClass($row['status']); ?> ms-2">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </h5>
                                        <div class="details-row">
                                            <strong><i class="fas fa-user"></i> Student:</strong>
                                            <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </div>
                                        <div class="details-row">
                                            <strong><i class="fas fa-book"></i> Book:</strong>
                                            <?php echo htmlspecialchars($row['book_title']); ?>
                                        </div>
                                        <div class="details-row">
                                            <strong><i class="fas fa-money-bill"></i> Fee:</strong>
                                            ₱<?php echo number_format($row['penalty_fee'], 2); ?>
                                        </div>
                                        <?php if(isset($row['overdue_days'])): ?>
                                            <div class="details-row">
                                                <strong><i class="fas fa-clock"></i> Overdue Days:</strong>
                                                <?php echo $row['overdue_days']; ?> days
                                            </div>
                                        <?php endif; ?>
                                        <?php if($row['book_condition']): ?>
                                            <div class="details-row">
                                                <strong><i class="fas fa-info-circle"></i> Condition:</strong>
                                                <?php echo ucfirst($row['book_condition']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="details-row">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                Created: <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                            </small>
                                        </div>
                                        <?php if($row['status'] == 'unpaid'): ?>
                                            <button class="btn btn-primary btn-sm mt-2" 
                                                    onclick="markAsPaid(<?php echo $row['penalty_id']; ?>)">
                                                <i class="fas fa-check"></i> Mark as Paid
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No penalties found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsPaid(penaltyId) {
            if(confirm('Are you sure you want to mark this penalty as paid?')) {
                fetch('update_penalty.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'penalty_id=' + penaltyId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update penalty status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the penalty status');
                });
            }
        }
    </script>
<?php include 'footer.php'; ?>
