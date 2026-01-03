<?php
ob_start();
session_start();

require_once __DIR__ . '/classes/Region.php';
require_once __DIR__ . '/classes/Farmer.php';
require_once __DIR__ . '/classes/FarmerRepository.php';
require_once __DIR__ . '/classes/QRCodeGenerator.php';
require_once __DIR__ . '/includes/layout.php';

// Migrate existing farmers to have tokens
$repository = new FarmerRepository();
$migratedCount = $repository->migrateExistingFarmersTokens();

renderHeader('verify');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-da-dark-green flex items-center">
                <i class="fas fa-qrcode mr-3"></i>Beneficiary Verification
            </h1>
            <p class="text-gray-600 mt-1">Scan QR code to verify beneficiary status</p>
        </div>
        <a href="beneficiaries.php" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Beneficiaries
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- QR Scanner Section -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-da-dark-green to-da-green p-4 text-white">
                <h2 class="text-lg font-bold flex items-center">
                    <i class="fas fa-camera mr-2"></i>QR Code Scanner
                </h2>
            </div>
            <div class="p-6">
                <!-- Camera Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Camera</label>
                    <select id="cameraSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                        <option value="">Loading cameras...</option>
                    </select>
                </div>
                
                <!-- Video Preview -->
                <div class="relative bg-gray-900 rounded-lg overflow-hidden mb-4" style="aspect-ratio: 4/3;">
                    <video id="videoPreview" class="w-full h-full object-cover" autoplay playsinline></video>
                    <canvas id="qrCanvas" class="hidden"></canvas>
                    
                    <!-- Scanning Overlay -->
                    <div id="scanOverlay" class="absolute inset-0 flex items-center justify-center">
                        <div class="w-48 h-48 border-4 border-da-green rounded-lg relative">
                            <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-white rounded-tl"></div>
                            <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-white rounded-tr"></div>
                            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-white rounded-bl"></div>
                            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-white rounded-br"></div>
                            <div id="scanLine" class="absolute left-0 right-0 h-0.5 bg-da-green animate-pulse" style="animation: scanLine 2s ease-in-out infinite;"></div>
                        </div>
                    </div>
                    
                    <!-- Status Indicator -->
                    <div id="scanStatus" class="absolute bottom-4 left-4 right-4 text-center">
                        <span class="bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-sm">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Initializing camera...
                        </span>
                    </div>
                </div>
                
                <!-- Scanner Controls -->
                <div class="flex gap-3">
                    <button id="startScan" onclick="startScanning()" class="flex-1 px-4 py-3 bg-da-green text-white rounded-lg hover:bg-da-dark-green transition font-semibold">
                        <i class="fas fa-play mr-2"></i>Start Scanning
                    </button>
                    <button id="stopScan" onclick="stopScanning()" class="flex-1 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold hidden">
                        <i class="fas fa-stop mr-2"></i>Stop
                    </button>
                </div>
                
                <!-- Manual Input -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Or Enter Verification Token Manually</h3>
                    <div class="flex gap-2">
                        <input type="text" id="manualToken" placeholder="DA-BEN-XXXXXXXX" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green font-mono">
                        <button onclick="verifyManualToken()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Verification Result Section -->
        <div>
            <!-- Result Card -->
            <div id="resultCard" class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gray-100 p-4">
                    <h2 class="text-lg font-bold text-gray-700 flex items-center">
                        <i class="fas fa-clipboard-check mr-2"></i>Verification Result
                    </h2>
                </div>
                <div id="resultContent" class="p-6">
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-qrcode text-6xl mb-4"></i>
                        <p>Scan a QR code or enter a token to verify beneficiary</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Verifications -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden mt-6">
                <div class="bg-gray-100 p-4">
                    <h2 class="text-lg font-bold text-gray-700 flex items-center">
                        <i class="fas fa-history mr-2"></i>Recent Verifications
                    </h2>
                </div>
                <div id="recentVerifications" class="p-4 max-h-64 overflow-y-auto">
                    <p class="text-center text-gray-400 py-4">No recent verifications</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes scanLine {
    0%, 100% { top: 10%; }
    50% { top: 90%; }
}

.verification-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid #28a745;
}

.verification-failed {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border: 2px solid #dc3545;
}

.verification-pending {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border: 2px solid #ffc107;
}

/* Print Styles for Verification Result */
@media print {
    body * {
        visibility: hidden;
    }
    #printableResult, #printableResult * {
        visibility: visible;
    }
    #printableResult {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<!-- Include jsQR Library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
let videoStream = null;
let scanning = false;
let scanInterval = null;
const recentVerifications = [];

// Initialize camera list
async function initCameras() {
    try {
        // Request permission first
        await navigator.mediaDevices.getUserMedia({ video: true });
        
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(device => device.kind === 'videoinput');
        
        const select = document.getElementById('cameraSelect');
        select.innerHTML = '';
        
        if (videoDevices.length === 0) {
            select.innerHTML = '<option value="">No cameras found</option>';
            return;
        }
        
        videoDevices.forEach((device, index) => {
            const option = document.createElement('option');
            option.value = device.deviceId;
            option.text = device.label || `Camera ${index + 1}`;
            select.appendChild(option);
        });
        
        updateStatus('Ready to scan', 'info');
    } catch (err) {
        console.error('Error initializing cameras:', err);
        updateStatus('Camera access denied', 'error');
    }
}

// Start scanning
async function startScanning() {
    const cameraId = document.getElementById('cameraSelect').value;
    
    const constraints = {
        video: {
            deviceId: cameraId ? { exact: cameraId } : undefined,
            facingMode: cameraId ? undefined : 'environment',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        }
    };
    
    try {
        videoStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('videoPreview');
        video.srcObject = videoStream;
        
        scanning = true;
        document.getElementById('startScan').classList.add('hidden');
        document.getElementById('stopScan').classList.remove('hidden');
        
        updateStatus('Scanning for QR codes...', 'scanning');
        
        // Start scanning loop
        scanInterval = setInterval(scanFrame, 100);
    } catch (err) {
        console.error('Error starting camera:', err);
        updateStatus('Failed to start camera', 'error');
    }
}

// Stop scanning
function stopScanning() {
    scanning = false;
    
    if (scanInterval) {
        clearInterval(scanInterval);
        scanInterval = null;
    }
    
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    
    const video = document.getElementById('videoPreview');
    video.srcObject = null;
    
    document.getElementById('startScan').classList.remove('hidden');
    document.getElementById('stopScan').classList.add('hidden');
    
    updateStatus('Scanner stopped', 'info');
}

// Scan frame for QR code
function scanFrame() {
    if (!scanning) return;
    
    const video = document.getElementById('videoPreview');
    const canvas = document.getElementById('qrCanvas');
    const ctx = canvas.getContext('2d');
    
    if (video.readyState !== video.HAVE_ENOUGH_DATA) return;
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: "dontInvert",
    });
    
    if (code) {
        // Found QR code
        handleQRCode(code.data);
    }
}

// Handle scanned QR code
function handleQRCode(data) {
    // Pause scanning briefly to avoid multiple reads
    scanning = false;
    updateStatus('QR Code detected!', 'success');
    
    setTimeout(() => {
        scanning = true;
    }, 2000);
    
    // Parse and verify
    try {
        const qrData = JSON.parse(data);
        
        if (qrData.system !== 'DA_FERTILIZER_BENEFICIARY') {
            showResult({
                valid: false,
                message: 'Invalid QR Code - Not a DA Beneficiary QR'
            });
            return;
        }
        
        // Verify with API
        verifyBeneficiary(qrData.farmer_id, qrData.rsbsa_id, qrData.token);
    } catch (e) {
        showResult({
            valid: false,
            message: 'Invalid QR Code format'
        });
    }
}

// Verify beneficiary via API
async function verifyBeneficiary(farmerId, rsbsaId, token) {
    updateStatus('Verifying...', 'scanning');
    
    try {
        const response = await fetch('api/verify.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                farmer_id: farmerId,
                rsbsa_id: rsbsaId,
                token: token
            })
        });
        
        const result = await response.json();
        showResult(result);
        addToRecentVerifications(result);
        
    } catch (err) {
        console.error('Verification error:', err);
        showResult({
            valid: false,
            message: 'Verification failed - Network error'
        });
    }
}

// Verify manual token
async function verifyManualToken() {
    const token = document.getElementById('manualToken').value.trim();
    
    if (!token) {
        alert('Please enter a verification token');
        return;
    }
    
    updateStatus('Verifying token...', 'scanning');
    
    try {
        const response = await fetch('api/verify.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                token: token
            })
        });
        
        const result = await response.json();
        showResult(result);
        addToRecentVerifications(result);
        
    } catch (err) {
        console.error('Verification error:', err);
        showResult({
            valid: false,
            message: 'Verification failed - Network error'
        });
    }
}

// Show verification result
function showResult(result) {
    const container = document.getElementById('resultContent');
    
    if (result.valid && result.farmer) {
        const farmer = result.farmer;
        const statusClass = farmer.status === 'Approved' ? 'verification-success' : 'verification-pending';
        const statusIcon = farmer.status === 'Approved' ? 'fa-check-circle text-green-600' : 'fa-clock text-yellow-600';
        const formattedDate = new Date(farmer.date_approved).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        
        // Store farmer data for printing
        window.lastVerifiedFarmer = {
            ...farmer,
            formattedDate: formattedDate
        };
        
        container.innerHTML = `
            <div id="printableResult">
                <!-- Print Header (hidden on screen, visible on print) -->
                <div class="hidden print:block text-center mb-6" style="display: none;" id="printHeader">
                    <h1 style="font-size: 24px; font-weight: bold; color: #006400; margin-bottom: 5px;">Department of Agriculture</h1>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Fertilizer Beneficiary Verification Result</p>
                    <hr style="border: 1px solid #228B22; margin: 15px 0;">
                </div>
                
                <div class="${statusClass} rounded-lg p-6 mb-4">
                    <div class="flex items-center justify-center mb-4">
                        <i class="fas ${statusIcon} text-5xl print:hidden"></i>
                        <span class="hidden" id="printStatusIcon">✓</span>
                    </div>
                    <h3 class="text-xl font-bold text-center mb-2">${farmer.status === 'Approved' ? 'VERIFIED BENEFICIARY' : 'PENDING STATUS'}</h3>
                    <p class="text-center text-sm">${result.message}</p>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-id-card text-da-green text-xl mr-3 print:hidden"></i>
                        <div>
                            <p class="text-xs text-gray-500">RSBSA ID</p>
                            <p class="font-bold font-mono">${farmer.rsbsa_id}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-user text-da-green text-xl mr-3 print:hidden"></i>
                        <div>
                            <p class="text-xs text-gray-500">Full Name</p>
                            <p class="font-bold">${farmer.full_name}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-map-marker-alt text-da-green text-xl mr-3 print:hidden"></i>
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="font-semibold text-sm">${farmer.full_address}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-yellow-50 rounded-lg text-center">
                            <p class="text-xs text-gray-500">Fertilizer Type</p>
                            <p class="font-bold text-sm">${farmer.fertilizer_type}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg text-center">
                            <p class="text-xs text-gray-500">Quantity</p>
                            <p class="font-bold text-lg">${farmer.fertilizer_quantity} kg</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                        <i class="fas fa-calendar text-blue-500 text-xl mr-3 print:hidden"></i>
                        <div>
                            <p class="text-xs text-gray-500">Date Approved</p>
                            <p class="font-bold">${formattedDate}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Print Footer (hidden on screen) -->
                <div class="hidden" id="printFooter">
                    <hr style="border: 1px solid #ddd; margin: 20px 0;">
                    <p style="font-size: 11px; color: #999; text-align: center;">Verified on ${new Date().toLocaleString()}</p>
                </div>
            </div>
            
            <div class="mt-4 flex gap-2 no-print">
                <a href="view.php?id=${farmer.id}" class="flex-1 text-center px-4 py-2 bg-da-green text-white rounded-lg hover:bg-da-dark-green transition">
                    <i class="fas fa-eye mr-2"></i>View Full Details
                </a>
                <button onclick="printVerification()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition" title="Print Verification">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="verification-failed rounded-lg p-6 text-center">
                <i class="fas fa-times-circle text-red-600 text-5xl mb-4"></i>
                <h3 class="text-xl font-bold text-red-700 mb-2">VERIFICATION FAILED</h3>
                <p class="text-red-600">${result.message}</p>
            </div>
            
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    If you believe this is an error, please contact the DA office with the beneficiary's RSBSA ID.
                </p>
            </div>
        `;
    }
}

// Add to recent verifications
function addToRecentVerifications(result) {
    const item = {
        time: new Date().toLocaleTimeString(),
        valid: result.valid,
        name: result.farmer ? result.farmer.full_name : 'Unknown',
        rsbsaId: result.farmer ? result.farmer.rsbsa_id : 'N/A',
        status: result.farmer ? result.farmer.status : 'Failed'
    };
    
    recentVerifications.unshift(item);
    if (recentVerifications.length > 10) recentVerifications.pop();
    
    updateRecentVerifications();
}

// Update recent verifications list
function updateRecentVerifications() {
    const container = document.getElementById('recentVerifications');
    
    if (recentVerifications.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-400 py-4">No recent verifications</p>';
        return;
    }
    
    container.innerHTML = recentVerifications.map(item => `
        <div class="flex items-center justify-between p-3 ${item.valid ? 'bg-green-50' : 'bg-red-50'} rounded-lg mb-2">
            <div class="flex items-center">
                <i class="fas ${item.valid ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500'} mr-3"></i>
                <div>
                    <p class="font-semibold text-sm">${item.name}</p>
                    <p class="text-xs text-gray-500">${item.rsbsaId}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-2 py-1 ${item.valid ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} text-xs rounded-full">${item.status}</span>
                <p class="text-xs text-gray-400 mt-1">${item.time}</p>
            </div>
        </div>
    `).join('');
}

// Update status message
function updateStatus(message, type) {
    const status = document.getElementById('scanStatus');
    let icon, bgClass;
    
    switch (type) {
        case 'success':
            icon = 'fa-check-circle';
            bgClass = 'bg-green-500';
            break;
        case 'error':
            icon = 'fa-exclamation-circle';
            bgClass = 'bg-red-500';
            break;
        case 'scanning':
            icon = 'fa-spinner fa-spin';
            bgClass = 'bg-blue-500';
            break;
        default:
            icon = 'fa-info-circle';
            bgClass = 'bg-black bg-opacity-50';
    }
    
    status.innerHTML = `<span class="${bgClass} text-white px-4 py-2 rounded-full text-sm"><i class="fas ${icon} mr-2"></i>${message}</span>`;
}

// Print verification
function printVerification() {
    if (!window.lastVerifiedFarmer) {
        alert('No verification result to print');
        return;
    }
    
    const farmer = window.lastVerifiedFarmer;
    const statusText = farmer.status === 'Approved' ? 'VERIFIED BENEFICIARY' : 'PENDING STATUS';
    const statusColor = farmer.status === 'Approved' ? '#28a745' : '#ffc107';
    const statusBg = farmer.status === 'Approved' ? '#d4edda' : '#fff3cd';
    
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Verification Result - ${farmer.rsbsa_id}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 40px;
                    max-width: 600px;
                    margin: 0 auto;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 3px solid #228B22;
                }
                .header h1 {
                    color: #006400;
                    font-size: 24px;
                    margin-bottom: 5px;
                }
                .header p {
                    color: #666;
                    font-size: 14px;
                }
                .status-box {
                    background: ${statusBg};
                    border: 2px solid ${statusColor};
                    border-radius: 10px;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 25px;
                }
                .status-box .icon {
                    font-size: 48px;
                    color: ${statusColor};
                    margin-bottom: 10px;
                }
                .status-box h2 {
                    color: ${statusColor};
                    font-size: 20px;
                    margin-bottom: 5px;
                }
                .status-box p {
                    color: #666;
                    font-size: 14px;
                }
                .info-section {
                    margin-bottom: 20px;
                }
                .info-row {
                    display: flex;
                    border-bottom: 1px solid #eee;
                    padding: 12px 0;
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .info-label {
                    width: 140px;
                    font-weight: bold;
                    color: #333;
                    font-size: 13px;
                }
                .info-value {
                    flex: 1;
                    color: #555;
                    font-size: 14px;
                }
                .info-value.mono {
                    font-family: monospace;
                }
                .two-col {
                    display: flex;
                    gap: 20px;
                    margin: 20px 0;
                }
                .col-box {
                    flex: 1;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                }
                .col-box.yellow { background: #fff9e6; border: 1px solid #ffc107; }
                .col-box.green { background: #e8f5e9; border: 1px solid #28a745; }
                .col-box .label {
                    font-size: 11px;
                    color: #666;
                    margin-bottom: 5px;
                }
                .col-box .value {
                    font-size: 16px;
                    font-weight: bold;
                    color: #333;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    text-align: center;
                    color: #999;
                    font-size: 11px;
                }
                @media print {
                    body { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Department of Agriculture</h1>
                <p>Fertilizer Beneficiary Verification Result</p>
            </div>
            
            <div class="status-box">
                <div class="icon">${farmer.status === 'Approved' ? '✓' : '⏳'}</div>
                <h2>${statusText}</h2>
                <p>Beneficiary verified successfully</p>
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">RSBSA ID</div>
                    <div class="info-value mono">${farmer.rsbsa_id}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">${farmer.full_name}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Address</div>
                    <div class="info-value">${farmer.full_address}</div>
                </div>
            </div>
            
            <div class="two-col">
                <div class="col-box yellow">
                    <div class="label">Fertilizer Type</div>
                    <div class="value">${farmer.fertilizer_type}</div>
                </div>
                <div class="col-box green">
                    <div class="label">Quantity</div>
                    <div class="value">${farmer.fertilizer_quantity} kg</div>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">Date Approved</div>
                    <div class="info-value">${farmer.formattedDate}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value" style="color: ${statusColor}; font-weight: bold;">${farmer.status}</div>
                </div>
            </div>
            
            <div class="footer">
                <p>Verified on ${new Date().toLocaleString()}</p>
                <p style="margin-top: 5px;">This document serves as proof of beneficiary verification.</p>
            </div>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initCameras);

// Handle camera change
document.getElementById('cameraSelect').addEventListener('change', function() {
    if (scanning) {
        stopScanning();
        startScanning();
    }
});
</script>

<?php renderFooter(); ?>
