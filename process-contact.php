<?php
// Include database connection
require_once './config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize response array
    $response = array(
        'success' => false,
        'message' => 'An error occurred while processing your message.'
    );
    
    try {
        // Get all active form fields
        $stmt = $conn->query("SELECT field_name, is_required FROM contact_form_fields WHERE is_active = 1");
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Validate required fields
        $error = false;
        $errorFields = array();
        
        foreach ($fields as $field) {
            // Skip validation for checkbox fields
            if (isset($_POST[$field['field_name']]) || !$field['is_required']) {
                continue;
            }
            
            $error = true;
            $errorFields[] = $field['field_name'];
        }
        
        // If validation fails
        if ($error) {
            $response['message'] = 'Please fill in all required fields.';
            $response['errorFields'] = $errorFields;
            echo json_encode($response);
            exit;
        }
        
        // Prepare data for insertion
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
        $subject = isset($_POST['msg_subject']) ? trim($_POST['msg_subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Insert into contact_submissions table
        $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone_number, subject, message, ip_address, user_agent) 
                                VALUES (:name, :email, :phone, :subject, :message, :ip, :user_agent)");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
        
        $stmt->execute();
        
        // Success response
        $response['success'] = true;
        $response['message'] = 'Thank you for your message. We will get back to you soon!';
        
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        // Error response
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }
} else {
    // Not a POST request, redirect to contact page
    header('Location: ./contact.php');
    exit;
}