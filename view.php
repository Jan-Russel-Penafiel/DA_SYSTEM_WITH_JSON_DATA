<?php
ob_start();
session_start();

require_once __DIR__ . '/classes/Region.php';
require_once __DIR__ . '/classes/Farmer.php';
require_once __DIR__ . '/classes/FarmerRepository.php';
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

            <!-- QR Code Placeholder -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                    <i class="fas fa-qrcode mr-2"></i>Verification QR
                </h2>
                <div class="bg-gray-100 rounded-lg p-4 text-center">
                    <div class="inline-block p-4 bg-white rounded-lg shadow">
                        <div class="w-32 h-32 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-qrcode text-gray-400 text-5xl"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Scan to verify beneficiary</p>
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
</style>

<?php renderFooter(); ?>
