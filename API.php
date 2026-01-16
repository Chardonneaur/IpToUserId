<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IpToUserId;

use Piwik\Piwik;

/**
 * API for managing IP to User ID mappings.
 *
 * @method static API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get all IP to User ID mappings.
     *
     * @return array
     */
    public function getAllMappings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $model = new Model();
        return $model->getAllMappings();
    }

    /**
     * Add a new IP to User ID mapping.
     *
     * @param string $ipAddress IP address or CIDR range
     * @param string $userIdentifier User identifier to assign
     * @return int The ID of the new mapping
     */
    public function addMapping($ipAddress, $userIdentifier)
    {
        Piwik::checkUserHasSuperUserAccess();

        $model = new Model();
        return $model->addMapping($ipAddress, $userIdentifier);
    }

    /**
     * Add multiple mappings from bulk input.
     *
     * @param string $bulkInput One mapping per line in format: IP,UserID
     * @return array Results with 'added' count and 'errors' array
     */
    public function addBulkMappings($bulkInput)
    {
        Piwik::checkUserHasSuperUserAccess();

        $model = new Model();
        return $model->addBulkMappings($bulkInput);
    }

    /**
     * Delete a mapping by ID.
     *
     * @param int $id Mapping ID
     */
    public function deleteMapping($id)
    {
        Piwik::checkUserHasSuperUserAccess();

        $model = new Model();
        $model->deleteMapping($id);
    }

    /**
     * Update an existing mapping.
     *
     * @param int $id Mapping ID
     * @param string $ipAddress IP address or CIDR range
     * @param string $userIdentifier User identifier to assign
     */
    public function updateMapping($id, $ipAddress, $userIdentifier)
    {
        Piwik::checkUserHasSuperUserAccess();

        $model = new Model();
        $model->updateMapping($id, $ipAddress, $userIdentifier);
    }

    /**
     * Get the User ID for a given IP address.
     *
     * @param string $ip IP address to check
     * @return string|null User ID or null if not found
     */
    public function getUserIdForIp($ip)
    {
        Piwik::checkUserHasSuperUserAccess();

        $model = new Model();
        return $model->getUserIdForIp($ip);
    }
}
