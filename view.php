<?php
ob_start();
session_start();

require_once __DIR__ . '/classes/Region.php';
require_once __DIR__ . '/classes/Farmer.php';
require_once __DIR__ . '/classes/FarmerRepository.php';
require_once __DIR__ . '/classes/QRCodeGenerator.php';
require_once __DIR__ . '/includes/layout.php';

// Initialize
$repository = new FarmerRepository();
$regions = Region::getAllRegions();

// Get farmer ID
$farmerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$farmer = $repository->getFarmerById($farmerId);

if (!$farmer) {
    header('Location: beneficiaries.php');
    exit;
}

// Find region name
$regionName = '';
foreach ($regions as $region) {
    if ($region->getCode() === $farmer->getRegionCode()) {
        $regionName = $region->getName();
        break;
    }
}

// Generate QR code for this farmer
$verificationToken = $farmer->getVerificationToken();

// If farmer doesn't have token, generate one
if (empty($verificationToken)) {
    $verificationToken = QRCodeGenerator::generateVerificationToken($farmer->getId(), $farmer->getRsbsaId());
}

$qrCodeDataUri = QRCodeGenerator::generateFarmerQRCode(
    $farmer->getId(),
    $farmer->getRsbsaId(),
    $verificationToken,
    180
);

renderHeader('beneficiaries');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="index.php" class="hover:text-da-green"><i class="fas fa-home"></i></a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="beneficiaries.php" class="hover:text-da-green">Beneficiaries</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-da-dark-green font-semibold"><?= htmlspecialchars($farmer->getRsbsaId()) ?></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-da-dark-green to-da-green p-6 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white rounded-full p-4">
                            <i class="fas fa-user text-da-green text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold"><?= htmlspecialchars($farmer->getFullName()) ?></h1>
                            <p class="text-da-light-green font-mono"><?= htmlspecialchars($farmer->getRsbsaId()) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="p-6">
                    <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Personal Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">First Name</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($farmer->getFirstName()) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Middle Name</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($farmer->getMiddleName()) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Last Name</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($farmer->getLastName()) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Contact Number</label>
                            <p class="text-gray-800 font-semibold">
                                <i class="fas fa-phone text-da-green mr-2"></i>
                                <?= htmlspecialchars($farmer->getContactNumber()) ?>
                            </p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2"></i>Address Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Barangay</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($farmer->getBarangay()) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Municipality/City</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($farmer->getMunicipality()) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Province</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($farmer->getProvince()) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Region</label>
                            <p class="text-gray-800 font-semibold">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full mr-2">
                                    <?= htmlspecialchars($farmer->getRegionCode()) ?>
                                </span>
                                <?= htmlspecialchars($regionName) ?>
                            </p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                        <i class="fas fa-tractor mr-2"></i>Farm Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Farm Area</label>
                            <p class="text-gray-800 font-semibold text-xl">
                                <?= number_format($farmer->getFarmArea(), 2) ?> <span class="text-sm text-gray-500">hectares</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Fertilizer Allocation</label>
                            <p class="text-gray-800 font-semibold text-xl">
                                <?= number_format($farmer->getFertilizerQuantity()) ?> <span class="text-sm text-gray-500">kilograms</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- QR Code Card -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-qrcode mr-2"></i>Verification QR
                </h2>
                <div class="bg-gray-100 rounded-lg p-4 text-center" id="qrCodeSection">
                    <div class="inline-block p-4 bg-white rounded-lg shadow">
                        <img src="<?= htmlspecialchars($qrCodeDataUri) ?>" 
                             alt="Verification QR Code for <?= htmlspecialchars($farmer->getRsbsaId()) ?>"
                             class="w-40 h-40">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Scan to verify beneficiary</p>
                    <p class="text-xs text-gray-400 mt-1 font-mono"><?= htmlspecialchars($verificationToken) ?></p>
                    <div class="flex gap-2 justify-center mt-3">
                        <button onclick="printQRCode()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition">
                            <i class="fas fa-print mr-2"></i>Print QR
                        </button>
                        <a href="verify.php" 
                           class="inline-flex items-center px-4 py-2 bg-da-green text-white text-sm rounded-lg hover:bg-da-dark-green transition">
                            <i class="fas fa-camera mr-2"></i>Verify
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500"></i>Status
                </h2>
                <div class="text-center">
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-full text-lg font-bold">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= htmlspecialchars($farmer->getStatus()) ?>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">
                        Approved on <?= date('F d, Y', strtotime($farmer->getDateApproved())) ?>
                    </p>
                </div>
            </div>

            <!-- Fertilizer Card -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-box mr-2 text-yellow-500"></i>Fertilizer Details
                </h2>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4">
                    <div class="text-center">
                        <i class="fas fa-cubes text-yellow-500 text-4xl mb-3"></i>
                        <h3 class="font-bold text-gray-800"><?= htmlspecialchars($farmer->getFertilizerType()) ?></h3>
                        <p class="text-3xl font-bold text-da-dark-green mt-2">
                            <?= number_format($farmer->getFertilizerQuantity()) ?> kg
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-cog mr-2"></i>Actions
                </h2>
                <div class="space-y-3">
                    <a href="beneficiaries.php?region=<?= urlencode($farmer->getRegionCode()) ?>" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                        <i class="fas fa-map-marker-alt mr-2"></i>View Same Region
                    </a>
                    <button onclick="window.print()" 
                            class="w-full flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-print mr-2"></i>Print Details
                    </button>
                    <a href="beneficiaries.php" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-da-green text-white rounded-lg hover:bg-da-dark-green transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    header, footer, nav, .no-print, button {
        display: none !important;
    }
    .container {
        max-width: 100% !important;
        padding: 0 !important;
    }
    .shadow-md {
        box-shadow: none !important;
    }
}

/* QR Code Print Styles */
@media print {
    body.print-qr-only * {
        visibility: hidden;
    }
    body.print-qr-only .print-qr-content,
    body.print-qr-only .print-qr-content * {
        visibility: visible;
    }
    body.print-qr-only .print-qr-content {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
}
</style>

<!-- Hidden Print Template for QR Code -->
<div id="qrPrintTemplate" class="print-qr-content hidden">
    <div style="text-align: center; padding: 20px;">
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 24px; font-weight: bold; color: #006400; margin-bottom: 5px;">Department of Agriculture</h1>
            <p style="font-size: 14px; color: #666;">Fertilizer Beneficiary Verification</p>
        </div>
        <div style="border: 3px solid #228B22; padding: 20px; display: inline-block; border-radius: 10px;">
            <img src="<?= htmlspecialchars($qrCodeDataUri) ?>" style="width: 200px; height: 200px;">
            <p style="font-size: 12px; color: #666; margin-top: 10px;">Scan to Verify Beneficiary</p>
        </div>
        <div style="margin-top: 20px; text-align: left; max-width: 300px; margin-left: auto; margin-right: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold; color: #333;">RSBSA ID:</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; font-family: monospace;"><?= htmlspecialchars($farmer->getRsbsaId()) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold; color: #333;">Name:</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= htmlspecialchars($farmer->getFullName()) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold; color: #333;">Status:</td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; color: #28a745; font-weight: bold;"><?= htmlspecialchars($farmer->getStatus()) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #333;">Token:</td>
                    <td style="padding: 8px 0; font-family: monospace; font-size: 11px;"><?= htmlspecialchars($verificationToken) ?></td>
                </tr>
            </table>
        </div>
        <p style="margin-top: 20px; font-size: 11px; color: #999;">Generated on <?= date('F d, Y') ?></p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
async function printQRCode() {
    const { jsPDF } = window.jspdf;
    
    // Convert SVG to PNG via canvas
    const svgDataUri = '<?= $qrCodeDataUri ?>';
    
    // Create an image from the SVG
    const img = new Image();
    img.src = svgDataUri;
    
    await new Promise((resolve, reject) => {
        img.onload = resolve;
        img.onerror = reject;
    });
    
    // Draw to canvas to get PNG
    const canvas = document.createElement('canvas');
    canvas.width = 200;
    canvas.height = 200;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0, 200, 200);
    const pngDataUri = canvas.toDataURL('image/png');
    
    const doc = new jsPDF();
    
    // Page dimensions
    const pageWidth = doc.internal.pageSize.getWidth();
    const centerX = pageWidth / 2;
    
    // Header
    doc.setFontSize(20);
    doc.setTextColor(0, 100, 0);
    doc.setFont('helvetica', 'bold');
    doc.text('Department of Agriculture', centerX, 30, { align: 'center' });
    
    doc.setFontSize(12);
    doc.setTextColor(102, 102, 102);
    doc.setFont('helvetica', 'normal');
    doc.text('Fertilizer Beneficiary Verification', centerX, 40, { align: 'center' });
    
    // QR Code border
    const qrSize = 60;
    const qrX = centerX - qrSize / 2 - 5;
    const qrY = 50;
    doc.setDrawColor(34, 139, 34);
    doc.setLineWidth(1);
    doc.roundedRect(qrX, qrY, qrSize + 10, qrSize + 25, 3, 3);
    
    // QR Code image (now using PNG)
    doc.addImage(pngDataUri, 'PNG', centerX - qrSize / 2, qrY + 5, qrSize, qrSize);
    
    // Scan text
    doc.setFontSize(9);
    doc.setTextColor(102, 102, 102);
    doc.text('Scan to Verify Beneficiary', centerX, qrY + qrSize + 15, { align: 'center' });
    
    // Beneficiary details
    const detailsY = 145;
    const labelX = 55;
    const valueX = 100;
    
    doc.setFontSize(11);
    doc.setTextColor(51, 51, 51);
    
    // RSBSA ID
    doc.setFont('helvetica', 'bold');
    doc.text('RSBSA ID:', labelX, detailsY);
    doc.setFont('courier', 'normal');
    doc.text('<?= $farmer->getRsbsaId() ?>', valueX, detailsY);
    
    // Line
    doc.setDrawColor(238, 238, 238);
    doc.setLineWidth(0.3);
    doc.line(labelX, detailsY + 3, 155, detailsY + 3);
    
    // Name
    doc.setFont('helvetica', 'bold');
    doc.text('Name:', labelX, detailsY + 12);
    doc.setFont('helvetica', 'normal');
    doc.text('<?= $farmer->getFullName() ?>', valueX, detailsY + 12);
    doc.line(labelX, detailsY + 15, 155, detailsY + 15);
    
    // Status
    doc.setFont('helvetica', 'bold');
    doc.text('Status:', labelX, detailsY + 24);
    doc.setTextColor(40, 167, 69);
    doc.setFont('helvetica', 'bold');
    doc.text('<?= $farmer->getStatus() ?>', valueX, detailsY + 24);
    doc.setTextColor(51, 51, 51);
    doc.line(labelX, detailsY + 27, 155, detailsY + 27);
    
    // Token
    doc.setFont('helvetica', 'bold');
    doc.text('Token:', labelX, detailsY + 36);
    doc.setFont('courier', 'normal');
    doc.setFontSize(9);
    doc.text('<?= $verificationToken ?>', valueX, detailsY + 36);
    
    // Footer
    doc.setFontSize(9);
    doc.setTextColor(153, 153, 153);
    doc.setFont('helvetica', 'normal');
    doc.text('Generated on <?= date('F d, Y') ?>', centerX, 200, { align: 'center' });
    
    // Save PDF
    doc.save('QR_<?= $farmer->getRsbsaId() ?>.pdf');
}
</script>

<?php renderFooter(); ?>
