<?php

class Region {
    private int $id;
    private string $code;
    private string $name;

    public function __construct(int $id, string $code, string $name) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
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

    public static function getAllRegions(): array {
        return [
            new Region(1, 'NCR', 'National Capital Region'),
            new Region(2, 'CAR', 'Cordillera Administrative Region'),
            new Region(3, 'I', 'Ilocos Region'),
            new Region(4, 'II', 'Cagayan Valley'),
            new Region(5, 'III', 'Central Luzon'),
            new Region(6, 'IV-A', 'CALABARZON'),
            new Region(7, 'IV-B', 'MIMAROPA'),
            new Region(8, 'V', 'Bicol Region'),
            new Region(9, 'VI', 'Western Visayas'),
            new Region(10, 'VII', 'Central Visayas'),
            new Region(11, 'VIII', 'Eastern Visayas'),
            new Region(12, 'IX', 'Zamboanga Peninsula'),
            new Region(13, 'X', 'Northern Mindanao'),
            new Region(14, 'XI', 'Davao Region'),
            new Region(15, 'XII', 'SOCCSKSARGEN'),
            new Region(16, 'XIII', 'Caraga'),
            new Region(17, 'BARMM', 'Bangsamoro Autonomous Region in Muslim Mindanao'),
        ];
    }
}
