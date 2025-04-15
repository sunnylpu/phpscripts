<?php
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function checkRole($requiredRole) {
    if (!isLoggedIn() || $_SESSION['role'] !== $requiredRole) {
        header("Location: ../login.php");
        exit();
    }
}

// Function to get current user's role
function getCurrentRole() {
    return $_SESSION['role'] ?? null;
}

// Function to get current user's ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?> 