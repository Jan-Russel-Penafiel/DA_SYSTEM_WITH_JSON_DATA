<?php

class Region {
    private int $id;
    private string $code;
    private string $name;
    private string $psgcCode;

    public function __construct(int $id, string $code, string $name, string $psgcCode = '') {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->psgcCode = $psgcCode;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPsgcCode(): string {
        return $this->psgcCode;
    }

    public static function getAllRegions(): array {
        return [
            new Region(1, 'NCR', 'National Capital Region', '130000000'),
            new Region(2, 'CAR', 'Cordillera Administrative Region', '140000000'),
            new Region(3, 'I', 'Ilocos Region', '010000000'),
            new Region(4, 'II', 'Cagayan Valley', '020000000'),
            new Region(5, 'III', 'Central Luzon', '030000000'),
            new Region(6, 'IV-A', 'CALABARZON', '040000000'),
            new Region(7, 'IV-B', 'MIMAROPA', '170000000'),
            new Region(8, 'V', 'Bicol Region', '050000000'),
            new Region(9, 'VI', 'Western Visayas', '060000000'),
            new Region(10, 'VII', 'Central Visayas', '070000000'),
            new Region(11, 'VIII', 'Eastern Visayas', '080000000'),
            new Region(12, 'IX', 'Zamboanga Peninsula', '090000000'),
            new Region(13, 'X', 'Northern Mindanao', '100000000'),
            new Region(14, 'XI', 'Davao Region', '110000000'),
            new Region(15, 'XII', 'SOCCSKSARGEN', '120000000'),
            new Region(16, 'XIII', 'Caraga', '160000000'),
            new Region(17, 'BARMM', 'Bangsamoro Autonomous Region in Muslim Mindanao', '190000000'),
        ];
    }
}
