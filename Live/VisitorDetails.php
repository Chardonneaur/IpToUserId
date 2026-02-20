<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IpToUserId\Live;

use Piwik\Plugins\IpToUserId\Model;
use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    /**
     * @var array<string, string|null>
     */
    private static $mappingCache = [];

    /**
     * @var Model|null
     */
    private $model;

    public function extendVisitorDetails(&$visitor)
    {
        if (!empty($visitor['userId']) || empty($visitor['visitIp'])) {
            return;
        }

        $userId = $this->getUserIdForIp($visitor['visitIp']);
        if ($userId === null) {
            return;
        }

        $visitor['userId'] = $userId;
    }

    private function getUserIdForIp($ip)
    {
        if (!array_key_exists($ip, self::$mappingCache)) {
            self::$mappingCache[$ip] = $this->getModel()->getUserIdForIp($ip);
        }

        return self::$mappingCache[$ip];
    }

    private function getModel()
    {
        if ($this->model === null) {
            $this->model = new Model();
        }

        return $this->model;
    }
}
