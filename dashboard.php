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

// Get statistics data
$byRegion = $statistics->getBeneficiariesByRegion();
$byFertilizer = $statistics->getBeneficiariesByFertilizerType();
$byMonth = $statistics->getBeneficiariesByMonth();
$topProvinces = $statistics->getTopProvinces(10);

renderHeader('dashboard');
renderFlashMessage();
?>

<div class="container mx-auto px-4">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-da-dark-green flex items-center">
            <i class="fas fa-chart-line mr-3"></i>Analytics Dashboard
        </h1>
        <p class="text-gray-600 mt-1">Comprehensive overview of fertilizer distribution to approved beneficiaries</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-md p-5 border-t-4 border-da-green">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Farmers</p>
                    <p class="text-2xl font-bold text-da-dark-green"><?= number_format($statistics->getTotalBeneficiaries()) ?></p>
                </div>
                <i class="fas fa-users text-da-green text-2xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Fertilizer</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($statistics->getTotalFertilizerDistributed()) ?> kg</p>
                </div>
                <i class="fas fa-box text-blue-500 text-2xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5 border-t-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Avg per Farmer</p>
                    <p class="text-2xl font-bold text-yellow-600"><?= number_format($statistics->getAverageFertilizerPerFarmer(), 1) ?> kg</p>
                </div>
                <i class="fas fa-calculator text-yellow-500 text-2xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5 border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Farm Area</p>
                    <p class="text-2xl font-bold text-purple-600"><?= number_format($statistics->getTotalFarmArea(), 1) ?> ha</p>
                </div>
                <i class="fas fa-mountain text-purple-500 text-2xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5 border-t-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Est. Value</p>
                    <p class="text-2xl font-bold text-red-600">₱<?= number_format($statistics->getEstimatedTotalValue()) ?></p>
                </div>
                <i class="fas fa-peso-sign text-red-500 text-2xl opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Chart: Beneficiaries by Region -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                <i class="fas fa-chart-bar mr-2"></i>Beneficiaries by Region
            </h2>
            <div class="h-80">
                <canvas id="regionChart"></canvas>
            </div>
        </div>

        <!-- Chart: Fertilizer Distribution -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                <i class="fas fa-chart-pie mr-2"></i>Fertilizer Type Distribution
            </h2>
            <div class="h-80">
                <canvas id="fertilizerChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Monthly Approvals Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                <i class="fas fa-chart-line mr-2"></i>Monthly Approvals Trend
            </h2>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Top Provinces -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-da-dark-green mb-4 flex items-center">
                <i class="fas fa-trophy mr-2 text-da-gold"></i>Top Provinces
            </h2>
            <div class="space-y-3">
                <?php 
                $rank = 1;
                foreach ($topProvinces as $province => $count): 
                    $badgeColor = $rank === 1 ? 'bg-yellow-400' : ($rank === 2 ? 'bg-gray-400' : ($rank === 3 ? 'bg-amber-600' : 'bg-gray-300'));
                ?>
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="<?= $badgeColor ?> text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                                <?= $rank ?>
                            </span>
                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($province) ?></span>
                        </div>
                        <span class="text-sm font-bold text-da-dark-green"><?= $count ?></span>
                    </div>
                <?php 
                    $rank++;
                endforeach; 
                ?>
            </div>
        </div>
    </div>

    <!-- Detailed Region Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
        <div class="bg-da-dark-green px-6 py-4">
            <h2 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-table mr-2"></i>Detailed Regional Statistics
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Region</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Beneficiaries</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">% of Total</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php 
                    $totalBeneficiaries = $statistics->getTotalBeneficiaries();
                    foreach ($regions as $region): 
                        $count = $byRegion[$region->getCode()] ?? 0;
                        $percentage = $totalBeneficiaries > 0 ? ($count / $totalBeneficiaries) * 100 : 0;
                    ?>
                        <tr class="hover:bg-green-50 transition">
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                                    <?= htmlspecialchars($region->getCode()) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($region->getName()) ?></td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-da-dark-green"><?= number_format($count) ?></td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-da-green h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600"><?= number_format($percentage, 1) ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="beneficiaries.php?region=<?= urlencode($region->getCode()) ?>" 
                                   class="text-da-green hover:text-da-dark-green font-semibold text-sm">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fertilizer Type Details -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
        <div class="bg-da-dark-green px-6 py-4">
            <h2 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-cubes mr-2"></i>Fertilizer Distribution Details
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fertilizer Type</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Beneficiaries</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Quantity (kg)</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Avg per Farmer (kg)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($byFertilizer as $type => $data): ?>
                        <tr class="hover:bg-green-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <i class="fas fa-box text-da-green mr-3"></i>
                                    <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($type) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-da-dark-green"><?= number_format($data['count']) ?></td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700"><?= number_format($data['quantity']) ?></td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700"><?= number_format($data['quantity'] / $data['count'], 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Region Chart
const regionCtx = document.getElementById('regionChart').getContext('2d');
new Chart(regionCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($byRegion)) ?>,
        datasets: [{
            label: 'Beneficiaries',
            data: <?= json_encode(array_values($byRegion)) ?>,
            backgroundColor: 'rgba(34, 139, 34, 0.7)',
            borderColor: 'rgba(0, 100, 0, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Fertilizer Chart
const fertilizerCtx = document.getElementById('fertilizerChart').getContext('2d');
new Chart(fertilizerCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($byFertilizer)) ?>,
        datasets: [{
            data: <?= json_encode(array_column($byFertilizer, 'count')) ?>,
            backgroundColor: [
                'rgba(34, 139, 34, 0.8)',
                'rgba(255, 215, 0, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(16, 185, 129, 0.8)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 10 } }
            }
        }
    }
});

// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($m) { return date('M Y', strtotime($m . '-01')); }, array_keys($byMonth))) ?>,
        datasets: [{
            label: 'Approvals',
            data: <?= json_encode(array_values($byMonth)) ?>,
            borderColor: 'rgba(34, 139, 34, 1)',
            backgroundColor: 'rgba(34, 139, 34, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php renderFooter(); ?>
