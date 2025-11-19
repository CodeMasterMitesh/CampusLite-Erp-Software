<?php
// app/views/dashboard.php
require_once __DIR__ . '/../controllers/BranchController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/CourseController.php';
$branches = BranchController::getAll();
$users = UserController::getAll();
$courses = CourseController::getAll();
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard Overview</h1>
            <div class="dashboard-controls">
                <button class="btn btn-light btn-export">
                    <i class="fas fa-file-export"></i> Export
                </button>
                <button class="btn btn-light btn-print">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card text-bg-primary">
                    <div class="card-body position-relative">
                        <i class="fas fa-code-branch card-icon"></i>
                        <h5 class="card-title">Total Branches</h5>
                        <p class="card-text">12</p>
                        <small><i class="fas fa-arrow-up me-1"></i> 2 new this month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card text-bg-success">
                    <div class="card-body position-relative">
                        <i class="fas fa-users card-icon"></i>
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text">245</p>
                        <small><i class="fas fa-arrow-up me-1"></i> 15 new this month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card text-bg-warning">
                    <div class="card-body position-relative">
                        <i class="fas fa-book card-icon"></i>
                        <h5 class="card-title">Total Courses</h5>
                        <p class="card-text">36</p>
                        <small><i class="fas fa-arrow-up me-1"></i> 3 new this month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card text-bg-danger">
                    <div class="card-body position-relative">
                        <i class="fas fa-user-graduate card-icon"></i>
                        <h5 class="card-title">Total Students</h5>
                        <p class="card-text">1,245</p>
                        <small><i class="fas fa-arrow-up me-1"></i> 42 new this month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card text-bg-info">
                    <div class="card-body position-relative">
                        <i class="fas fa-chalkboard-teacher card-icon"></i>
                        <h5 class="card-title">Total Faculty</h5>
                        <p class="card-text">86</p>
                        <small><i class="fas fa-arrow-up me-1"></i> 5 new this month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card text-bg-secondary">
                    <div class="card-body position-relative">
                        <i class="fas fa-money-bill-wave card-icon"></i>
                        <h5 class="card-title">Revenue</h5>
                        <p class="card-text">$245K</p>
                        <small><i class="fas fa-arrow-up me-1"></i> 12% increase</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/partials/footer.php'; ?>
