<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IpToUserId;

use Piwik\Common;
use Piwik\Db;
use Piwik\Date;
use Piwik\Network\IP;

class Model
{
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable('ip_to_userid');
    }

    /**
     * Get all IP to User ID mappings
     */
    public function getAllMappings()
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY date_added DESC";
        return Db::fetchAll($sql);
    }

    /**
     * Add a new IP to User ID mapping
     */
    public function addMapping($ipAddress, $userIdentifier)
    {
        $ipAddress = trim($ipAddress);
        $userIdentifier = trim($userIdentifier);

        if (empty($ipAddress) || empty($userIdentifier)) {
            throw new \Exception('IP address and user identifier are required');
        }

        // Check if it's a CIDR range
        $isRange = strpos($ipAddress, '/') !== false;
        $rangeStart = null;
        $rangeEnd = null;

        if ($isRange) {
            $range = $this->parseCidrRange($ipAddress);
            $rangeStart = $range['start'];
            $rangeEnd = $range['end'];
        }

        // Check for duplicate
        $existing = Db::fetchOne(
            "SELECT id FROM `{$this->table}` WHERE ip_address = ?",
            [$ipAddress]
        );

        if ($existing) {
            throw new \Exception("IP address '$ipAddress' already exists");
        }

        Db::query(
            "INSERT INTO `{$this->table}` (ip_address, is_range, range_start, range_end, user_identifier, date_added)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $ipAddress,
                $isRange ? 1 : 0,
                $rangeStart,
                $rangeEnd,
                $userIdentifier,
                Date::now()->getDatetime()
            ]
        );

        return Db::getReader()->lastInsertId();
    }

    /**
     * Add multiple mappings from bulk input
     * Format: IP,UserID per line
     */
    public function addBulkMappings($bulkInput)
    {
        $lines = explode("\n", $bulkInput);
        $added = 0;
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = str_getcsv($line);
            if (count($parts) < 2) {
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid format (expected IP,UserID)";
                continue;
            }

            $ip = trim($parts[0]);
            $userId = trim($parts[1]);

            try {
                $this->addMapping($ip, $userId);
                $added++;
            } catch (\Exception $e) {
                $errors[] = "Line " . ($lineNum + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'added' => $added,
            'errors' => $errors
        ];
    }

    /**
     * Delete a mapping by ID
     */
    public function deleteMapping($id)
    {
        Db::query("DELETE FROM `{$this->table}` WHERE id = ?", [(int)$id]);
    }

    /**
     * Update a mapping
     */
    public function updateMapping($id, $ipAddress, $userIdentifier)
    {
        $ipAddress = trim($ipAddress);
        $userIdentifier = trim($userIdentifier);

        $isRange = strpos($ipAddress, '/') !== false;
        $rangeStart = null;
        $rangeEnd = null;

        if ($isRange) {
            $range = $this->parseCidrRange($ipAddress);
            $rangeStart = $range['start'];
            $rangeEnd = $range['end'];
        }

        Db::query(
            "UPDATE `{$this->table}`
             SET ip_address = ?, is_range = ?, range_start = ?, range_end = ?, user_identifier = ?
             WHERE id = ?",
            [$ipAddress, $isRange ? 1 : 0, $rangeStart, $rangeEnd, $userIdentifier, (int)$id]
        );
    }

    /**
     * Get User ID for a given IP address
     */
    public function getUserIdForIp($ip)
    {
        // First try exact match
        $userId = Db::fetchOne(
            "SELECT user_identifier FROM `{$this->table}` WHERE ip_address = ? AND is_range = 0",
            [$ip]
        );

        if ($userId) {
            return $userId;
        }

        // Try range match
        $ipBinary = @inet_pton($ip);
        if ($ipBinary === false) {
            return null;
        }

        $userId = Db::fetchOne(
            "SELECT user_identifier FROM `{$this->table}`
             WHERE is_range = 1 AND range_start <= ? AND range_end >= ?
             LIMIT 1",
            [$ipBinary, $ipBinary]
        );

        return $userId ?: null;
    }

    /**
     * Parse CIDR notation and return start/end IP in binary format
     */
    private function parseCidrRange($cidr)
    {
        $parts = explode('/', $cidr);
        $ip = $parts[0];
        $prefix = isset($parts[1]) ? (int)$parts[1] : (strpos($ip, ':') !== false ? 128 : 32);

        $ipBinary = inet_pton($ip);
        if ($ipBinary === false) {
            throw new \Exception("Invalid IP address in CIDR: $cidr");
        }

        $isIpv6 = strlen($ipBinary) === 16;
        $maxPrefix = $isIpv6 ? 128 : 32;

        if ($prefix < 0 || $prefix > $maxPrefix) {
            throw new \Exception("Invalid prefix length in CIDR: $cidr");
        }

        // Calculate network mask
        $maskBits = str_repeat('1', $prefix) . str_repeat('0', $maxPrefix - $prefix);
        $mask = '';
        for ($i = 0; $i < $maxPrefix; $i += 8) {
            $mask .= chr(bindec(substr($maskBits, $i, 8)));
        }

        // Calculate start (network address)
        $start = $ipBinary & $mask;

        // Calculate end (broadcast address)
        $invertedMask = ~$mask;
        $end = $ipBinary | $invertedMask;

        return [
            'start' => $start,
            'end' => $end
        ];
    }
}
