<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$students = q('SELECT u.id, u.name, u.email, u.created_at, COUNT(r.id) AS attempts, AVG(r.percentage) AS average
               FROM users u LEFT JOIN results r ON r.user_id = u.id
               WHERE u.role = "student" GROUP BY u.id, u.name, u.email, u.created_at ORDER BY u.name')->fetchAll();

$title = 'Students';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Students</h1></div>
<div class="card">
  <?php if ($students): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Name</th><th>Email</th><th>Registered</th><th>Exams taken</th><th>Average</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
      <tr>
        <td><?= e($s['name']) ?></td><td><?= e($s['email']) ?></td>
        <td><?= e(date('d M Y', strtotime($s['created_at']))) ?></td>
        <td><?= (int)$s['attempts'] ?></td>
        <td><?= $s['attempts'] ? num($s['average']) . '%' : '-' ?></td>
        <td class="row-actions"><a class="btn btn-secondary btn-sm" href="<?= url('admin/results.php?student=' . (int)$s['id']) ?>">Results</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><p class="empty">No students have registered yet.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
