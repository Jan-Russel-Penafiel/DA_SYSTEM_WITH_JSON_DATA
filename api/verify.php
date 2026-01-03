<?php
/**
 * API Endpoint: Verify Beneficiary QR Code
 * 
 * POST /api/verify.php
 * 
 * Request body (JSON):
 * - farmer_id: int (optional if using token only)
 * - rsbsa_id: string (optional if using token only)
 * - token: string (required)
 * 
 * Response (JSON):
 * - valid: boolean
 * - message: string
 * - farmer: object|null (farmer details if valid)
 * - status: string (Approved/Pending/Rejected)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../classes/Farmer.php';
require_once __DIR__ . '/../classes/FarmerRepository.php';
require_once __DIR__ . '/../classes/QRCodeGenerator.php';

// Get request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['valid' => false, 'message' => 'Invalid request body']);
    exit;
}

$repository = new FarmerRepository();

// Method 1: Verify with farmer_id, rsbsa_id, and token
if (isset($input['farmer_id']) && isset($input['rsbsa_id']) && isset($input['token'])) {
    $farmerId = (int)$input['farmer_id'];
    $rsbsaId = $input['rsbsa_id'];
    $token = $input['token'];
    
    $result = $repository->verifyFarmerToken($farmerId, $rsbsaId, $token);
    
    if ($result['valid'] && $result['farmer']) {
        echo json_encode([
            'valid' => true,
            'message' => $result['message'],
            'farmer' => $result['farmer']->toArray(),
            'status' => $result['farmer']->getStatus(),
            'verified_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'valid' => false,
            'message' => $result['message'],
            'farmer' => null
        ]);
    }
    exit;
}

// Method 2: Verify with token only
if (isset($input['token'])) {
    $token = trim($input['token']);
    
    if (empty($token)) {
        echo json_encode(['valid' => false, 'message' => 'Token is required']);
        exit;
    }
    
    // Find farmer by token
    $farmer = $repository->getFarmerByToken($token);
    
    if (!$farmer) {
        echo json_encode([
            'valid' => false,
            'message' => 'Beneficiary not found - Invalid token',
            'farmer' => null
        ]);
        exit;
    }
    
    // Verify the token
    $isValid = QRCodeGenerator::verifyToken($farmer->getId(), $farmer->getRsbsaId(), $token);
    
    if (!$isValid) {
        echo json_encode([
            'valid' => false,
            'message' => 'Token verification failed',
            'farmer' => null
        ]);
        exit;
    }
    
    echo json_encode([
        'valid' => true,
        'message' => 'Beneficiary verified successfully',
        'farmer' => $farmer->toArray(),
        'status' => $farmer->getStatus(),
        'verified_at' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Missing required parameters
http_response_code(400);
echo json_encode([
    'valid' => false,
    'message' => 'Missing required parameters (token required)'
]);
