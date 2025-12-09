<?php
session_start();

// Initialize viewed_notulen array if not exists
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id'])) {
    $id = (int) $input['id'];
    
    // Add to viewed list if not already there
    if (!in_array($id, $_SESSION['viewed_notulen'])) {
        $_SESSION['viewed_notulen'][] = $id;
    }
    
    echo json_encode(['success' => true, 'message' => 'Notulen marked as viewed']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID not provided']);
}
?>
