
<?php

use CampusLite\Controllers\{BranchController, CourseController, SubjectController};

if (!defined('APP_INIT')) { http_response_code(403); exit('Forbidden'); }
$courses = CourseController::getAll();
$branches = BranchController::getAll();
$branchMap = [];
foreach ($branches as $b) {
    $branchMap[$b['id']] = $b['name'];
}
$subjects = SubjectController::getAll();
?>

<div class="container-fluid dashboard-container fade-in">
    <?php
    // Use shared page header partial for consistency
    $page_icon = 'fas fa-book';
    $page_title = 'Courses';
    $show_actions = false;
    $add_button = ['label' => 'Add New Course', 'onclick' => 'showAddCourseModal()'];
    include __DIR__ . '/partials/page-header.php';
    ?>
    <div class="advanced-table-container">
        <div class="table-responsive table-compact" id="tableContainer">
            <table class="table data-table" id="courses-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center"><input type="checkbox" id="select-all-courses"></th>
                        <th width="80">ID</th>
                        <th>Branch</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Total Fee</th>
                        <th>Duration (months)</th>
                        <th>File</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No courses found</h4>
                                    <p>No courses match your search criteria</p>
                                    <button class="btn btn-primary" onclick="showAddCourseModal()">
                                        <i class="fas fa-plus"></i> Add First Course
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="text-center" data-label="Select"><input type="checkbox" class="row-select" data-id="<?= htmlspecialchars($course['id'] ?? '') ?>"></td>
                                <td data-label="ID"><?= htmlspecialchars($course['id'] ?? '') ?></td>
                                <td data-label="Branch">
                                    <?php
                                    $bid = $course['branch_id'] ?? $course['branch'] ?? null;
                                    $branchName = '';
                                    if ($bid && isset($branchMap[$bid])) $branchName = $branchMap[$bid];
                                    elseif (!empty($course['branch'])) $branchName = $course['branch'];
                                    ?>
                                    <?= htmlspecialchars($branchName) ?>
                                </td>
                                <td data-label="Title"><?= htmlspecialchars($course['title'] ?? '') ?></td>
                                <td data-label="Description"><?= htmlspecialchars($course['description'] ?? '') ?></td>
                                <td data-label="Total Fee"><?= htmlspecialchars($course['total_fee'] ?? '') ?></td>
                                <td data-label="Duration"><?= htmlspecialchars($course['duration_months'] ?? '') ?> months</td>
                                <td data-label="File">
                                    <?php if (!empty($course['file_path'])): ?>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewCourseFile(<?= $course['id'] ?? 0 ?>)" title="View File">
                                            <i class="fas fa-file"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Actions">
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-table" onclick="editCourse(<?= $course['id'] ?? 0 ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewCourse(<?= $course['id'] ?? 0 ?>)" title="View"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteCourse(<?= $course['id'] ?? 0 ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-book me-2"></i> Add/Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm">
                    <input type="hidden" name="id" id="courseId" value="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Branch</label>
                            <select class="form-control" name="branch_id" required>
                                <option value="0">-- Select Branch --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= intval($b['id']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Fee</label>
                            <input type="number" class="form-control" name="total_fee" min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (months)</label>
                            <input type="number" class="form-control" name="duration_months" min="0">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subjects</label>
                            <div id="courseSubjectsContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addSubjectRowBtn"><i class="fas fa-plus"></i> Add Subject</button>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Course Materials/Documents</label>
                            <input type="file" class="form-control" name="course_file" id="courseFile" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                            <small class="text-muted">Upload PDF, Word, PowerPoint, or Excel files (Max 10MB)</small>
                            <div id="currentFileDisplay" class="mt-2"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCourseBtn" onclick="saveCourse()">Save Course</button>
            </div>
        </div>
    </div>
</div>

<!-- File Viewer Modal -->
<div class="modal fade" id="fileViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file me-2"></i> Course File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fileViewerContent" style="min-height: 500px;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="downloadFileBtn" href="#" download class="btn btn-primary" target="_blank">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inject CSRF token for API calls
    window.__csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
</script>
<script src="/public/assets/js/courses.js"></script>
