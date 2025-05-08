<?php
session_start();


try {
    $conn = new PDO("mysql:host=localhost;dbname=crud_one_to_many", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


if (isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user = $_SESSION['username'];
    $stmt = $conn->prepare("INSERT INTO posts (title, content, created_by, updated_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $content, $user, $user]);
}


if (isset($_POST['update_post'])) {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user = $_SESSION['username'];
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, updated_by = ? WHERE id = ?");
    $stmt->execute([$title, $content, $user, $post_id]);
}


if (isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    $conn->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
    $conn->prepare("DELETE FROM posts WHERE id = ?")->execute([$post_id]);
}


if (isset($_POST['add_comment'])) {
    $post_id = $_POST['comment_post_id'];
    $comment = $_POST['comment'];
    $user = $_SESSION['username'];
    $stmt = $conn->prepare("INSERT INTO comments (post_id, comment, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $comment, $user]);
}

$posts = $conn->query("SELECT * FROM posts ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>One to Many CRUD</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f9; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
        form { margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 10px; margin-top: 5px; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .post { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        .comment-box { margin-top: 10px; background: #f9f9f9; padding: 10px; border-radius: 5px; }
        .logout-btn { background: #dc3545; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome, <?= $_SESSION['username'] ?> | <a href="?logout=1" class="logout-btn">Logout</a></h2>
    <h3>Create Post</h3>
    <form method="post">
        <input type="text" name="title" placeholder="Post Title" required>
        <textarea name="content" placeholder="Post Content" required></textarea>
        <button type="submit" name="create_post">Post</button>
    </form>
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <form method="post">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>">
                <textarea name="content"><?= htmlspecialchars($post['content']) ?></textarea>
                <button name="update_post">Update</button>
                <a href="?delete_post=<?= $post['id'] ?>" onclick="return confirm('Delete post?')">Delete</a>
            </form>
            <small>Created by: <?= $post['created_by'] ?> | Last updated by: <?= $post['updated_by'] ?></small>
            <div class="comment-box">
                <h4>Comments</h4>
                <?php
                $comments = $conn->prepare("SELECT * FROM comments WHERE post_id = ?");
                $comments->execute([$post['id']]);
                foreach ($comments as $comment): ?>
                    <p><?= htmlspecialchars($comment['comment']) ?> <small>(by <?= $comment['created_by'] ?>)</small></p>
                <?php endforeach; ?>
                <form method="post">
                    <input type="hidden" name="comment_post_id" value="<?= $post['id'] ?>">
                    <input type="text" name="comment" placeholder="Add a comment...">
                    <button name="add_comment">Comment</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
