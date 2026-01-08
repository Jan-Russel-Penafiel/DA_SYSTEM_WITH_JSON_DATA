<?php

require_once __DIR__ . '/FarmerRepository.php';
require_once __DIR__ . '/Region.php';

class Statistics {
    private FarmerRepository $repository;

    public function __construct(FarmerRepository $repository) {
        $this->repository = $repository;
    }

    public function getTotalBeneficiaries(): int {
        return $this->repository->getTotalFarmers();
    }

    public function getTotalFertilizerDistributed(): float {
        return $this->repository->getTotalFertilizerDistributed();
    }

    public function getTotalFarmArea(): float {
        $total = 0;
        foreach ($this->repository->getAllFarmers() as $farmer) {
            $total += $farmer->getFarmArea();
        }
        return $total;
    }

    public function getAverageFertilizerPerFarmer(): float {
        $total = $this->getTotalBeneficiaries();
        if ($total === 0) return 0;
        return $this->getTotalFertilizerDistributed() / $total;
    }

    public function getAverageFarmArea(): float {
        $total = $this->getTotalBeneficiaries();
        if ($total === 0) return 0;
        return $this->getTotalFarmArea() / $total;
    }

    public function getBeneficiariesByRegion(): array {
        return $this->repository->getTotalByRegion();
    }

    public function getBeneficiariesByFertilizerType(): array {
        $totals = [];
        foreach ($this->repository->getAllFarmers() as $farmer) {
            $type = $farmer->getFertilizerType();
            if (!isset($totals[$type])) {
                $totals[$type] = ['count' => 0, 'quantity' => 0];
            }
            $totals[$type]['count']++;
            $totals[$type]['quantity'] += $farmer->getFertilizerQuantity();
        }
        return $totals;
    }

    public function getBeneficiariesByMonth(): array {
        $totals = [];
        foreach ($this->repository->getAllFarmers() as $farmer) {
            $month = date('Y-m', strtotime($farmer->getDateApproved()));
            if (!isset($totals[$month])) {
                $totals[$month] = 0;
            }
            $totals[$month]++;
        }
        ksort($totals);
        return $totals;
    }

    public function getTopProvinces(int $limit = 5): array {
        $totals = [];
        foreach ($this->repository->getAllFarmers() as $farmer) {
            $province = $farmer->getProvince();
            if (!isset($totals[$province])) {
                $totals[$province] = 0;
            }
            $totals[$province]++;
        }
        arsort($totals);
        return array_slice($totals, 0, $limit, true);
    }

    public function getRecentApprovals(int $limit = 10): array {
        $farmers = $this->repository->getAllFarmers();
        usort($farmers, function($a, $b) {
            return strtotime($b->getDateApproved()) - strtotime($a->getDateApproved());
        });
        return array_slice($farmers, 0, $limit);
    }

    public function getEstimatedTotalValue(): float {
        $total = 0;
        $avgPricePerKg = 30; // Average price per kg
        foreach ($this->repository->getAllFarmers() as $farmer) {
            $total += $farmer->getFertilizerQuantity() * $avgPricePerKg;
        }
        return $total;
    }

    public function getRegionWithMostBeneficiaries(): array {
        $byRegion = $this->getBeneficiariesByRegion();
        if (empty($byRegion)) {
            return ['code' => 'N/A', 'count' => 0];
        }
        $maxCode = array_keys($byRegion, max($byRegion))[0];
        return ['code' => $maxCode, 'count' => $byRegion[$maxCode]];
    }
}
