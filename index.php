<?php
require 'config.php';

if (isset($_POST['add'])) {
    $task = trim($_POST['task']);
    $priority = $_POST['priority'] ?? 'Normal';
    if ($task !== '') {
        $stmt = $conn->prepare("INSERT INTO todos (task, priority) VALUES (?, ?)");
        $stmt->bind_param("ss", $task, $priority);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

if (isset($_POST['delete']) && isset($_POST['confirm_delete'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM todos WHERE id = $id");
    header("Location: index.php");
    exit();
}

if (isset($_POST['edit']) && isset($_POST['newtask'])) {
    $id = intval($_POST['id']);
    $newtask = trim($_POST['newtask']);
    if ($newtask !== '') {
        $stmt = $conn->prepare("UPDATE todos SET task = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $newtask, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

if (isset($_POST['mark_done'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE todos SET is_done = 1 WHERE id = $id");
    header("Location: index.php");
    exit();
}

if (isset($_POST['unmark_done'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE todos SET is_done = 0 WHERE id = $id");
    header("Location: index.php");
    exit();
}

$todos = $conn->query("SELECT * FROM todos ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List - Alex</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header-container">
         <h1>ALEX | To-Do List</h1>
    </div>
    <div class="form-container">
        <form action="" method="POST" class="form-todo">
            <input type="text" name="task" placeholder="Tugas baru..." required>
            <select name="priority">
                <option value="Tinggi">Tinggi</option>
                <option value="Normal" selected>Normal</option>
                <option value="Rendah">Rendah</option>
            </select>
            <button type="submit" name="add">Tambah</button>
        </form>
    </div>
    <div class="list-container">
        <?php if ($todos->num_rows > 0): ?>
        <table class="todo-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>List/Nama</th>
                    <th>Prioritas</th>
                    <th>Jam</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; ?>
            <?php while ($task = $todos->fetch_assoc()): ?>
                <tr class="<?= $task['is_done'] ? 'done' : '' ?>">
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($task['task']) ?></td>
                    <td><?= $task['priority'] ?></td>
                    <td><?= date('H:i:s', strtotime($task['updated_at'])) ?></td>
                    <td><?= date('d M Y', strtotime($task['updated_at'])) ?></td>
                    <td><?= $task['is_done'] ? 'Selesai' : 'Belum' ?></td>
                    <td>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <input type="text" name="newtask" placeholder="Edit tugas" required>
                            <button type="submit" name="edit" class="edit-button">Edit</button>
                        </form>
                        <form method="POST" class="inline-form" onsubmit="return confirm('Hapus tugas ini?')">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="confirm_delete" value="1">
                            <button type="submit" name="delete" class="delete-button">Hapus</button>
                        </form>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <?php if ($task['is_done']): ?>
                                <button type="submit" name="unmark_done" class="mark-button">Batal</button>
                            <?php else: ?>
                                <button type="submit" name="mark_done" class="mark-button">Selesai</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty">Belum ada tugas.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
