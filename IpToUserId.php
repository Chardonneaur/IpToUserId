<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IpToUserId;

use Piwik\Plugin;
use Piwik\Db;
use Piwik\Common;
use Piwik\Plugins\IpToUserId\Live\VisitorDetails as IpToUserIdVisitorDetails;

class IpToUserId extends Plugin
{
    public function registerEvents()
    {
        return [
            'Tracker.newVisitorInformation' => 'enrichVisitorWithUserId',
            'Live.addVisitorDetails' => 'addVisitorDetails',
            'Live.filterVisitorDetails' => 'moveVisitorDetailsToEnd',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function install()
    {
        $table = Common::prefixTable('ip_to_userid');

        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `ip_address` VARCHAR(45) NOT NULL,
            `is_range` TINYINT(1) NOT NULL DEFAULT 0,
            `range_start` VARBINARY(16) NULL,
            `range_end` VARBINARY(16) NULL,
            `user_identifier` VARCHAR(200) NOT NULL,
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ip_address` (`ip_address`),
            INDEX `idx_range` (`range_start`, `range_end`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        Db::exec($sql);
    }

    public function uninstall()
    {
        $table = Common::prefixTable('ip_to_userid');
        Db::exec("DROP TABLE IF EXISTS `$table`");
    }

    public function enrichVisitorWithUserId(&$visitorInfo, \Piwik\Tracker\Request $request)
    {
        // Only set user ID if not already set
        if (!empty($visitorInfo['user_id'])) {
            return;
        }

        $ip = $request->getIp();
        if (empty($ip)) {
            return;
        }

        $model = new Model();
        $userId = $model->getUserIdForIp($ip);

        if ($userId !== null) {
            $visitorInfo['user_id'] = $userId;
        }
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'IpToUserId_DeleteConfirm';
        $translationKeys[] = 'IpToUserId_MappingAdded';
        $translationKeys[] = 'IpToUserId_MappingDeleted';
    }

    public function addVisitorDetails(&$visitorDetails)
    {
        $visitorDetails[] = new IpToUserIdVisitorDetails();
    }

    public function moveVisitorDetailsToEnd(&$visitorDetails)
    {
        $ipToUserDetails = [];

        foreach ($visitorDetails as $index => $detailsInstance) {
            if ($detailsInstance instanceof IpToUserIdVisitorDetails) {
                $ipToUserDetails[] = $detailsInstance;
                unset($visitorDetails[$index]);
            }
        }

        foreach ($ipToUserDetails as $detailsInstance) {
            $visitorDetails[] = $detailsInstance;
        }
    }
}
