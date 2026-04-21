<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
use Releva\Retargeting\Base\RelevanzApi;
use Releva\Retargeting\Base\Credentials;
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Modified\Configuration as ShopConfiguration;
use Releva\Retargeting\Modified\ShopInfo;
use Releva\Retargeting\Modified\Helper as RelevanzHelper;

class RelevanzAdminModule {

    protected $credentials = null;

    final public function __construct() {
        RelevanzHelper::loadLanguage();
        $this->credentials = ShopConfiguration::getCredentials();

        $this->route();
    }

    protected function route() {
        $actionName = (isset($_GET['tab']) && !empty($_GET['tab']))
            ? 'action'.ucfirst($_GET['tab'])
            : 'actionStats';

        if (!$this->credentials->isComplete() && ($actionName !== 'actionConf')) {
            return xtc_redirect(xtc_href_link('relevanz.php', 'tab=conf'));
        }
        if (!method_exists($this, $actionName)) {
            return xtc_redirect(xtc_href_link('relevanz.php'));
        }
        return call_user_func(array($this, $actionName));
    }

    protected function outputPage($view, $data) {
        global $relevanz_view, $relevanz_data;
        $relevanz_data = array_merge_recursive([
            'credentials' => $this->credentials,
            'messages' => [],
        ], $data);
        $relevanz_view = $view;
    }

    protected function actionConf() {
        $messages = [];
        if (isset($_POST['conf']['apikey'])) {
            try {
                $credentials = RelevanzApi::verifyApiKey($_POST['conf']['apikey'], [
                    'callback-url' => ShopInfo::getUrlCallback(),
                ]);
                ShopConfiguration::updateCredentials($credentials);

                $messages[] = [
                    'type' => 'success',
                    'code' => 1554076968,
                ];

                $this->credentials = $credentials;

            } catch (RelevanzException $re) {
                $messages[] = [
                    'type' => 'danger',
                    'code' => $re->getCode(),
                ];
            }
        }

        if (isset($_POST['conf']['additional_html'])) {
            ShopConfiguration::updateAdditionalHtml($_POST['conf']['additional_html']);
        }

        $exportUrl = str_replace(':auth', $this->credentials->getAuthHash(), ShopInfo::getUrlProductExport());

        $this->outputPage('configuration', [
            'messages' => $messages,
            'action' => xtc_href_link('relevanz.php', 'tab=conf'),
            'urlExport' => $exportUrl,
            'additionalHtml' => ShopConfiguration::getAdditionalHtml(),
        ]);
    }

    protected function actionStats() {
        $this->outputPage('statistics', [
            'stats-url' => RelevanzApi::RELEVANZ_STATS_FRAME.$this->credentials->getApiKey(),
        ]);
    }
}
