<?php
// Include database connection
require_once './config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the email (required field)
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Invalid email, redirect with error
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?newsletter=error&message=' . urlencode('Please provide a valid email address.'));
        exit;
    }
    
    try {
        // Get all active form fields
        $stmt = $conn->query("SELECT field_name FROM bulletin_fields WHERE is_active = 1 ORDER BY position");
        $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Prepare data for insertion
        $data = array();
        foreach ($fields as $field) {
            if ($field === 'email') {
                $data[$field] = $email;
            } else {
                $data[$field] = isset($_POST[$field]) ? trim($_POST[$field]) : '';
            }
        }
        
        // Insert into newsletter_subscribers table
        $sql = "INSERT INTO newsletter_subscribers (email, name, subscribed_at, ip_address, user_agent) 
                VALUES (:email, :name, NOW(), :ip_address, :user_agent)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':name', isset($data['name']) ? $data['name'] : '');
        $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR']);
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT']);
        
        $stmt->execute();
        
        // Success, redirect back with success message
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?newsletter=success&message=' . urlencode('Thank you for subscribing!'));
        exit;
    } catch (PDOException $e) {
        // Check if it's a duplicate entry error
        if ($e->getCode() == 23000) {
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '?newsletter=error&message=' . urlencode('You are already subscribed to our newsletter.'));
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '?newsletter=error&message=' . urlencode('An error occurred. Please try again later.'));
        }
        exit;
    }
} else {
    // Not a POST request, redirect to home
    header('Location: ./');
    exit;
}