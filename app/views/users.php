<?php
// app/views/users.php
require_once __DIR__ . '/../controllers/UserController.php';
$users = UserController::getAll();
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container dashboard-container">
    <h2>Users</h2>
    <div class="mb-3">
        <button class="btn btn-success btn-export" onclick="exportTable('users-table')">Export to Excel</button>
        <button class="btn btn-primary btn-print" onclick="printTable('users-table')">Print</button>
    </div>
    <table class="table table-bordered" id="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Branch</th>
                <th>Role</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['branch_id']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['mobile']) ?></td>
                <td><?= $user['status'] ? 'Active' : 'Inactive' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
