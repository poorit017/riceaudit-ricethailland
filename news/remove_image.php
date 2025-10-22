<?php
include "../config/config_news.php";
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}

if (isset($_POST['id']) && isset($_POST['news_id'])) {
    $id = intval($_POST['id']);
    $news_id = intval($_POST['news_id']);

    $res = $conn->query("SELECT image_url FROM news_images WHERE id = $id");
    $img = $res->fetch_assoc();
    if ($img && file_exists($img['image_url'])) unlink($img['image_url']);

    $conn->query("DELETE FROM news_images WHERE id = $id");

    header("Location: edit_news.php?id=$news_id");
    exit;
}
?>
