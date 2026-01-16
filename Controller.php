<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IpToUserId;

use Piwik\Piwik;
use Piwik\View;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Nonce;

class Controller extends ControllerAdmin
{
    public const NONCE_NAME = 'IpToUserId.manage';

    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View('@IpToUserId/index');

        $model = new Model();
        $view->mappings = $model->getAllMappings();
        $view->nonce = Nonce::getNonce(self::NONCE_NAME);

        $this->setGeneralVariablesView($view);

        return $view->render();
    }
}
