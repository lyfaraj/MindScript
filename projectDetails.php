<?php
// projectDetails.php

session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit(); 
}

$user_id = isset($_SESSION['unique_id']) ? $_SESSION['unique_id'] : null;

if ($user_id === null) {
    die('User ID is not set.');
}

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
$project_type = isset($_GET['type']) ? $_GET['type'] : null;

if ($project_id === null || $project_type === null) {
    die('Project ID or type is not set.');
}

// Redirect based on project type
if ($project_type === 'group') {
    header("location: groupProject.php?project_id=$project_id");
} elseif ($project_type === 'personal') {
    header("location: personalProject.php?project_id=$project_id");
} else {
    // Invalid project type, handle as needed
    die('Invalid project type.');
}
?>
