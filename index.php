<?php
ob_start();
session_start();

require_once __DIR__ . '/classes/Region.php';
require_once __DIR__ . '/classes/Farmer.php';
require_once __DIR__ . '/classes/FarmerRepository.php';
require_once __DIR__ . '/classes/Statistics.php';
require_once __DIR__ . '/includes/layout.php';

// Initialize
$repository = new FarmerRepository();
$statistics = new Statistics($repository);
$regions = Region::getAllRegions();

// Get recent approvals
$recentApprovals = $statistics->getRecentApprovals(5);
$topRegion = $statistics->getRegionWithMostBeneficiaries();

renderHeader('home');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-da-dark-green to-da-green rounded-2xl shadow-xl p-8 mb-8 text-white">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-4xl font-bold mb-4">
                    <i class="fas fa-leaf mr-3"></i>
                    Welcome to DA-FBMS
                </h1>
                <p class="text-da-light-green text-lg mb-6">
                    Fertilizer Beneficiary Management System for tracking approved farmer beneficiaries 
                    across all regions of the Philippines.
                </p>
            </div>
            <div class="hidden lg:block text-center">
                <div class="inline-block bg-white/10 rounded-full p-12">
                    <i class="fas fa-tractor text-8xl text-da-gold"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Quick Search -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-da-dark-green mb-4 flex items-center">
                <i class="fas fa-search mr-2"></i>Quick Search
            </h2>
            <form action="beneficiaries.php" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Term</label>
                        <div class="relative">
                            <input type="text" name="search" placeholder="Name, RSBSA ID, Location..." 
                                class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                        <select name="region" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-da-green focus:border-da-green bg-white">
                            <option value="">All Regions</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= htmlspecialchars($region->getCode()) ?>">
                                    Region <?= htmlspecialchars($region->getCode()) ?> - <?= htmlspecialchars($region->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full bg-da-green hover:bg-da-dark-green text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-search mr-2"></i>Search Beneficiaries
                </button>
            </form>
        </div>

        <!-- Top Region -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-da-dark-green mb-4 flex items-center">
                <i class="fas fa-trophy mr-2 text-da-gold"></i>Top Region
            </h2>
            <div class="text-center py-6">
                <div class="inline-block bg-gradient-to-r from-da-gold to-yellow-400 rounded-full p-6 mb-4">
                    <i class="fas fa-award text-white text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-da-dark-green">Region <?= htmlspecialchars($topRegion['code']) ?></h3>
                <p class="text-gray-600"><?= number_format($topRegion['count']) ?> Beneficiaries</p>
                <a href="beneficiaries.php?region=<?= urlencode($topRegion['code']) ?>" 
                   class="mt-4 inline-block text-da-green hover:text-da-dark-green font-semibold">
                    View Details <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Approvals -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-da-dark-green px-6 py-4">
                <h2 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-clock mr-2"></i>Recent Approvals
                </h2>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    <?php foreach ($recentApprovals as $farmer): ?>
                        <a href="view.php?id=<?= $farmer->getId() ?>" class="block p-3 bg-gray-50 rounded-lg hover:bg-green-50 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-da-light-green rounded-full p-2">
                                        <i class="fas fa-user text-da-dark-green"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($farmer->getFullName()) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($farmer->getRsbsaId()) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                        <?= htmlspecialchars($farmer->getRegionCode()) ?>
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1"><?= date('M d, Y', strtotime($farmer->getDateApproved())) ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a href="beneficiaries.php?sort=date_approved&order=desc" class="block text-center mt-4 text-da-green hover:text-da-dark-green font-semibold">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Region Overview -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-da-dark-green px-6 py-4">
                <h2 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-map mr-2"></i>Beneficiaries by Region
                </h2>
            </div>
            <div class="p-4 max-h-96 overflow-y-auto">
                <?php 
                $byRegion = $statistics->getBeneficiariesByRegion();
                $maxCount = max($byRegion);
                foreach ($regions as $region): 
                    $count = $byRegion[$region->getCode()] ?? 0;
                    $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                ?>
                    <a href="beneficiaries.php?region=<?= urlencode($region->getCode()) ?>" 
                       class="block mb-3 p-3 bg-gray-50 rounded-lg hover:bg-green-50 transition">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                <?= htmlspecialchars($region->getCode()) ?> - <?= htmlspecialchars($region->getName()) ?>
                            </span>
                            <span class="text-sm font-bold text-da-dark-green"><?= $count ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-da-green h-2 rounded-full transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
