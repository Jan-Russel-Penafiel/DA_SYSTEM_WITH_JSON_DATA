<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QRCodeGenerator {
    private const TOKEN_SECRET = 'DA_FERTILIZER_BENEFICIARY_2025';
    private const TOKEN_PREFIX = 'DA-BEN-';
    
    /**
     * Generate a unique verification token for a farmer
     */
    public static function generateVerificationToken(int $farmerId, string $rsbsaId): string {
        $data = $farmerId . '|' . $rsbsaId . '|' . self::TOKEN_SECRET;
        $hash = hash('sha256', $data);
        return self::TOKEN_PREFIX . strtoupper(substr($hash, 0, 16));
    }
    
    /**
     * Generate QR code data (JSON with verification info)
     */
    public static function generateQRData(int $farmerId, string $rsbsaId, string $verificationToken): string {
        $data = [
            'system' => 'DA_FERTILIZER_BENEFICIARY',
            'version' => '1.0',
            'farmer_id' => $farmerId,
            'rsbsa_id' => $rsbsaId,
            'token' => $verificationToken,
            'generated' => date('Y-m-d H:i:s')
        ];
        return json_encode($data);
    }
    
    /**
     * Generate QR code as SVG string
     */
    public static function generateQRCodeSVG(string $data, int $size = 200): string {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        return $writer->writeString($data);
    }
    
    /**
     * Generate QR code as base64 encoded data URI
     */
    public static function generateQRCodeDataURI(string $data, int $size = 200): string {
        $svg = self::generateQRCodeSVG($data, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Generate complete QR code for a farmer (returns data URI)
     */
    public static function generateFarmerQRCode(int $farmerId, string $rsbsaId, string $verificationToken, int $size = 200): string {
        $qrData = self::generateQRData($farmerId, $rsbsaId, $verificationToken);
        return self::generateQRCodeDataURI($qrData, $size);
    }
    
    /**
     * Verify a token for a farmer
     */
    public static function verifyToken(int $farmerId, string $rsbsaId, string $token): bool {
        $expectedToken = self::generateVerificationToken($farmerId, $rsbsaId);
        return hash_equals($expectedToken, $token);
    }
    
    /**
     * Parse QR data and extract information
     */
    public static function parseQRData(string $qrData): ?array {
        $data = json_decode($qrData, true);
        
        if (!$data || !isset($data['system']) || $data['system'] !== 'DA_FERTILIZER_BENEFICIARY') {
            return null;
        }
        
        if (!isset($data['farmer_id']) || !isset($data['rsbsa_id']) || !isset($data['token'])) {
            return null;
        }
        
        return $data;
    }
}
