<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - UUID ====================
 * ================================
 */

namespace celionatti\Bolt\Uuid;

class UUID
{
    /**
     * Generate a more advanced UUID with additional metadata
     */
    public static function generate(array $metadata = [])
    {
        // Base UUID generation
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        // Timestamp with microsecond precision
        $timestamp = microtime(true) * 10000;
        $timestampBinary = substr(pack('J', $timestamp), 2);
        $data = substr_replace($data, $timestampBinary, 0, 8);

        // Add machine/process identifiers
        $machineId = substr(sha1(gethostname() . php_uname('n')), 0, 4);
        $processId = substr(pack('n', getmypid()), 0, 2);
        $data .= hex2bin($machineId . bin2hex($processId));

        // Incorporate optional metadata
        if (!empty($metadata)) {
            $metadataHash = substr(sha1(json_encode($metadata)), 0, 8);
            $data .= hex2bin($metadataHash);
        }

        // Additional randomness
        $data .= random_bytes(8);

        // Format UUID
        $uuid = vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));

        return "bv_{$uuid}";
    }

    /**
     * Validate UUID integrity
     */
    public static function validate($uuid)
    {
        // Remove prefix
        $hexUuid = substr($uuid, 3);

        // Check length and format
        if (strlen($hexUuid) !== 64) {
            return false;
        }

        // Check version and variant bits
        $versionByte = hexdec(substr($hexUuid, 12, 2));
        $variantByte = hexdec(substr($hexUuid, 16, 2));

        return (($versionByte & 0xF0) === 0x40) && (($variantByte & 0xC0) === 0x80);
    }

    /**
     * Extract comprehensive UUID information
     */
    public static function parseUUID($uuid)
    {
        $hexUuid = substr($uuid, 3);
        $binaryUuid = hex2bin($hexUuid);

        return [
            'timestamp' => unpack('J', "\0\0" . substr($binaryUuid, 0, 8))[1] / 10000,
            'machineId' => bin2hex(substr($binaryUuid, 8, 4)),
            'processId' => unpack('n', substr($binaryUuid, 12, 2))[1]
        ];
    }
}