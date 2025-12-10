<?php
if (!defined('APP_INIT')) { http_response_code(403); exit('Forbidden'); }

$currentUser = $_SESSION['user'] ?? null;
$userId = $currentUser['id'] ?? 0;
$userName = $currentUser['name'] ?? 'Employee';
$userRole = $currentUser['role'] ?? 'employee';

// Fetch employee-specific data
require_once __DIR__ . '/../controllers/EmployeeController.php';
require_once __DIR__ . '/../controllers/LeaveController.php';

$employeeData = null;
if ($userRole === 'employee') {
    $employeeData = \CampusLite\Controllers\EmployeeController::getByUserId($userId);
}

// Get leaves for this employee
$leaves = \CampusLite\Controllers\LeaveController::getByUserId($userId);
$leaveStats = [
    'total' => count($leaves),
    'approved' => count(array_filter($leaves, fn($l) => ($l['status'] ?? '') === 'approved')),
    'pending' => count(array_filter($leaves, fn($l) => ($l['status'] ?? '') === 'pending')),
    'rejected' => count(array_filter($leaves, fn($l) => ($l['status'] ?? '') === 'rejected')),
];

// Get today's attendance status
$todayAttendance = null;
$today = date('Y-m-d');
$sql = "SELECT * FROM attendance WHERE entity_type = 'employee' AND entity_id = ? AND date = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'is', $userId, $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$todayAttendance = mysqli_fetch_assoc($result);
?>

<div class="container-fluid dashboard-container fade-in">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-1">Welcome, <?= htmlspecialchars($userName) ?>!</h3>
            <p class="text-muted">Your personal dashboard</p>
        </div>
    </div>

    <div class="row g-3">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&size=120&background=0D8ABC&color=fff" 
                         alt="Profile" class="rounded-circle mb-3">
                    <h5 class="card-title"><?= htmlspecialchars($userName) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $userRole))) ?></p>
                    <?php if ($employeeData): ?>
                        <div class="text-start mt-3">
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($employeeData['email'] ?? 'N/A') ?></p>
                            <p class="mb-1"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($employeeData['mobile'] ?? 'N/A') ?></p>
                            <p class="mb-1"><i class="fas fa-id-card me-2"></i><?= htmlspecialchars($employeeData['employee_id'] ?? 'N/A') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-calendar-check me-2"></i>Today's Attendance</h6>
                            <h3 class="mb-0">
                                <?php if ($todayAttendance): ?>
                                    <?= $todayAttendance['status'] === 'present' ? '✓ Present' : '✗ Absent' ?>
                                <?php else: ?>
                                    Not Marked
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-umbrella-beach me-2"></i>Total Leaves</h6>
                            <h3 class="mb-0"><?= $leaveStats['total'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card text-bg-warning text-dark">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clock me-2"></i>Pending Leaves</h6>
                            <h3 class="mb-0"><?= $leaveStats['pending'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card text-bg-info">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-check-circle me-2"></i>Approved Leaves</h6>
                            <h3 class="mb-0"><?= $leaveStats['approved'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leaves -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Leave Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($leaves)): ?>
                        <p class="text-muted text-center">No leave requests found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Requested On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($leaves, 0, 10) as $leave): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($leave['from_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($leave['to_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($leave['reason'] ?? '') ?></td>
                                            <td>
                                                <?php
                                                $status = $leave['status'] ?? 'applied';
                                                $badgeClass = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning');
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($leave['applied_on'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
