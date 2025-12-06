<?php

use CampusLite\Controllers\{BranchController, EmployeeController};

if (!defined('APP_INIT')) { http_response_code(403); exit('Forbidden'); }
// app/views/employee.php
$employees = EmployeeController::getAll();
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$totalEmployees = count($employees);
$totalPages = 1;
$branches = BranchController::getAll();
$branchMap = [];
foreach ($branches as $b) {
    $branchMap[intval($b['id'])] = $b['name'] ?? ($b['branch'] ?? '');
}
?>

<div class="container-fluid dashboard-container fade-in">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-user-tie"></i> Employee</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fas fa-plus"></i> Add New Employee
        </button>
    </div>
    <!-- Table Container -->
    <div class="advanced-table-container">
        <!-- Table Controls -->
        <!-- table-controls removed (search/actions removed) -->
        <!-- Table -->
        <div class="table-responsive table-compact" id="tableContainer">
            <table class="table data-table" id="employee-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th width="90">Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Branch</th>
                        <th>Gender</th>
                        <th>Joining</th>
                        <th>In/Out</th>
                        <th>Docs</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No employees found</h4>
                                    <p>No employees match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                        <i class="fas fa-plus"></i> Add First Employee
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= htmlspecialchars($employee['id'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($employee['profile_photo'])): ?>
                                        <a href="/public/uploads/employees/<?= htmlspecialchars($employee['profile_photo']) ?>" class="media-preview-link" data-preview-url="/public/uploads/employees/<?= htmlspecialchars($employee['profile_photo']) ?>" data-preview-title="Employee Photo - <?= htmlspecialchars($employee['name'] ?? '') ?>" data-preview-type="image">
                                            <img src="/public/uploads/employees/<?= htmlspecialchars($employee['profile_photo']) ?>" alt="Photo" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($employee['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($employee['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($employee['mobile'] ?? $employee['phone'] ?? '') ?></td>
                                <td>
                                    <?php
                                    $bid = $employee['branch_id'] ?? null;
                                    $branchName = '';
                                    if ($bid && isset($branchMap[$bid])) {
                                        $branchName = $branchMap[$bid];
                                    } elseif (!empty($employee['branch'])) {
                                        $branchName = $employee['branch'];
                                    }
                                    echo htmlspecialchars($branchName);
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($employee['gender'] ?? '') ?></td>
                                <td><?= htmlspecialchars($employee['joining_date'] ?? '') ?></td>
                                <td><?php echo htmlspecialchars(($employee['in_time'] ?? '') . ' / ' . ($employee['out_time'] ?? '')); ?></td>
                                <td>
                                    <?php if (!empty($employee['aadhar_card'])): ?><a href="/public/uploads/employees/<?= htmlspecialchars($employee['aadhar_card']) ?>" class="media-preview-link" data-preview-url="/public/uploads/employees/<?= htmlspecialchars($employee['aadhar_card']) ?>" data-preview-title="Aadhar - <?= htmlspecialchars($employee['name'] ?? '') ?>" title="Aadhar"><i class="fas fa-id-card"></i></a><?php endif; ?>
                                    <?php if (!empty($employee['pan_card'])): ?><a href="/public/uploads/employees/<?= htmlspecialchars($employee['pan_card']) ?>" class="media-preview-link ms-2" data-preview-url="/public/uploads/employees/<?= htmlspecialchars($employee['pan_card']) ?>" data-preview-title="PAN - <?= htmlspecialchars($employee['name'] ?? '') ?>" title="PAN"><i class="fas fa-address-card"></i></a><?php endif; ?>
                                    <?php if (!empty($employee['passport'])): ?><a href="/public/uploads/employees/<?= htmlspecialchars($employee['passport']) ?>" class="media-preview-link ms-2" data-preview-url="/public/uploads/employees/<?= htmlspecialchars($employee['passport']) ?>" data-preview-title="Passport - <?= htmlspecialchars($employee['name'] ?? '') ?>" title="Passport"><i class="fas fa-passport"></i></a><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($employee['status'])): ?>
                                        <?php $isActive = ($employee['status'] === 'active' || intval($employee['status']) === 1); $statusLabel = $isActive ? 'Active' : 'Inactive'; ?>
                                        <span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>"><?= $statusLabel ?></span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-table" onclick="editEmployee(<?= $employee['id'] ?? 0 ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewEmployee(<?= $employee['id'] ?? 0 ?>)" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteEmployee(<?= $employee['id'] ?? 0 ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <?= count($employees) ?> of <?= $totalEmployees ?> employees
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=employee&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=employee&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=employee&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="employeeId" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Set initial password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" disabled>
                                <option value="employee" selected>Employee</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="mobile" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <select class="form-select" name="branch_id" id="employeeBranch" required>
                                <option value="0">-- Select Branch --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= intval($b['id']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="employeePhotoPreview" src="" alt="Preview" style="width:64px;height:64px;object-fit:cover;border-radius:6px;display:none;">
                                <input type="file" class="form-control" name="profile_photo" id="employeePhotoInput" accept="image/*">
                                <button type="button" class="btn btn-outline-danger btn-sm" id="removeEmployeePhoto" style="display:none;">Remove</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Birthdate</label>
                            <input type="date" class="form-control" name="dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">-- Select --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Marital Status</label>
                            <select class="form-select" name="marital_status">
                                <option value="">-- Select --</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" class="form-control" name="joining_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Resign Date</label>
                            <input type="date" class="form-control" name="resign_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IN Time</label>
                            <input type="time" class="form-control" name="in_time">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">OUT Time</label>
                            <input type="time" class="form-control" name="out_time">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" placeholder="Street address">
                        </div>
                        <div class="col-md-4"><input type="text" class="form-control" name="area" placeholder="Area"></div>
                        <div class="col-md-4"><input type="text" class="form-control" name="city" placeholder="City"></div>
                        <div class="col-md-4"><input type="text" class="form-control" name="pincode" placeholder="Pincode"></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="state" placeholder="State"></div>
                        <div class="col-md-6"><input type="text" class="form-control" name="country" placeholder="Country"></div>
                        <div class="col-md-6">
                            <label class="form-label">Aadhar Card (Attachment)</label>
                            <input type="file" class="form-control" name="aadhar_card" accept="image/*,application/pdf">
                            <small id="aadharFileInfo" class="form-text text-muted" style="display:none;"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN Card (Attachment)</label>
                            <input type="file" class="form-control" name="pan_card" accept="image/*,application/pdf">
                            <small id="panFileInfo" class="form-text text-muted" style="display:none;"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Passport (Attachment)</label>
                            <input type="file" class="form-control" name="passport" accept="image/*,application/pdf">
                            <small id="passportFileInfo" class="form-text text-muted" style="display:none;"></small>
                        </div>
                    </div>
                    <hr>
                    <h6>Educational Details</h6>
                    <div id="educationList" class="mb-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addEducationRow">Add Education</button>
                    <hr>
                    <h6>Employment Details</h6>
                    <div id="employmentList" class="mb-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addEmploymentRow">Add Employment</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEmployee()">Save Employee</button>
            </div>
        </div>
    </div>
</div>
<script src="/public/assets/js/employee.js"></script>

