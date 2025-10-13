<?php include __DIR__.'/../header.php'; require_role('admin');

// Get users
$stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

// Get lessons
$stmt = $pdo->query('SELECT l.*, u.name AS teacher_name FROM lessons l JOIN users u ON l.teacher_id = u.id ORDER BY l.created_at DESC');
$lessons = $stmt->fetchAll();

// Get login history
$stmt = $pdo->query('SELECT lh.*, u.name AS user_name FROM login_history lh LEFT JOIN users u ON lh.user_id = u.id ORDER BY lh.login_at DESC LIMIT 20');
$logins = $stmt->fetchAll();

// Get access logs
$stmt = $pdo->query('SELECT a.*, u.name AS user_name FROM access_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.accessed_at DESC LIMIT 20');
$accesses = $stmt->fetchAll();

?>
<div class="box">
<h2>Admin Dashboard</h2>
<div class="grid">
<?php foreach($users as $u): ?>
<div class="card">
<h4><?=htmlspecialchars($u['name'])?></h4>
<p><?=htmlspecialchars($u['email'])?></p>
<p>Role: <?=$u['role']?></p>
</div>
<?php endforeach; ?>
</div>
<h3>Lessons</h3>
<div class="grid">
<?php foreach($lessons as $l): ?>
<div class="card">
<h4><?=htmlspecialchars($l['title'])?></h4>
<p>By <?=htmlspecialchars($l['teacher_name'])?></p>
</div>
<?php endforeach; ?>
</div>
<h3>Login History (most recent)</h3>
<div class="box">
<table style="width:100%; border-collapse:collapse">
<tr><th>User</th><th>IP</th><th>Login</th><th>Logout</th><th>Duration (s)</th></tr>
<?php foreach($logins as $lh): ?>
<tr>
<td><?=htmlspecialchars($lh['user_name'])?></td>
<td><?=htmlspecialchars($lh['ip'])?></td>
<td><?=htmlspecialchars($lh['login_at'])?></td>
<td><?=htmlspecialchars($lh['logout_at'])?></td>
<td><?=htmlspecialchars($lh['duration_seconds'])?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<h3>Recent Access Logs</h3>
<div class="box">
<table style="width:100%; border-collapse:collapse">
<tr><th>User</th><th>Page</th><th>Time</th><th>IP</th></tr>
<?php foreach($accesses as $a): ?>
<tr>
<td><?=htmlspecialchars($a['user_name'] ?? 'Guest')?></td>
<td><?=htmlspecialchars($a['page'])?></td>
<td><?=htmlspecialchars($a['accessed_at'])?></td>
<td><?=htmlspecialchars($a['ip'])?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
<?php include __DIR__.'/../footer.php'; ?>
