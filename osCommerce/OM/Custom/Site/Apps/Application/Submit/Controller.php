<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Submit;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\Apps;

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_Session = Registry::get('Session');
        $OSCOM_Template = Registry::get('Template');

        if (!$OSCOM_Session->hasStarted()) {
            $OSCOM_Session->start();
        }

        $this->setPageParameters();

        $params = $OSCOM_Template->getValue('url_params');

        if (!isset($_SESSION['Website']['Account'])) {
            $_SESSION['login_redirect'] = [
                'url' => OSCOM::getLink(null, 'Submit', implode('&', $params)),
                'info_text' => OSCOM::getDef('login_text_info'),
                'cancel_url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)),
                'cancel_text' => OSCOM::getDef('redirect_cancel_return_to_site')
            ];

            OSCOM::redirect(OSCOM::getLink('Website', 'Account', 'Login', 'SSL'));
        }

        $OSCOM_Template->setValue('aTitleLength', Apps::TITLE_LENGTH);
        $OSCOM_Template->setValue('aTitleMinLength', Apps::TITLE_MIN_LENGTH);
        $OSCOM_Template->setValue('aShortDescriptionLength', Apps::SHORT_DESCRIPTION_LENGTH);
        $OSCOM_Template->setValue('aShortDescriptionMinLength', Apps::SHORT_DESCRIPTION_MIN_LENGTH);
        $OSCOM_Template->setValue('aDescriptionLength', Apps::DESCRIPTION_LENGTH);
        $OSCOM_Template->setValue('aDescriptionMinLength', Apps::DESCRIPTION_MIN_LENGTH);
        $OSCOM_Template->setValue('aCoverImageWidth', Apps::COVER_IMAGE_WIDTH);
        $OSCOM_Template->setValue('aCoverImageHeight', Apps::COVER_IMAGE_HEIGHT);
        $OSCOM_Template->setValue('aScreenshotImageWidth', Apps::SCREENSHOT_IMAGE_WIDTH);
        $OSCOM_Template->setValue('aScreenshotImageHeight', Apps::SCREENSHOT_IMAGE_HEIGHT);
        $OSCOM_Template->setValue('aUploadSizeMb', Apps::UPLOAD_SIZE_MB);

        $OSCOM_Template->addHtmlElement('header', '<link rel="stylesheet" href="' . OSCOM::getPublicSiteLink('external/dropzone/5.0.0/dropzone.min.css') . '">');
        $OSCOM_Template->addHtmlElement('header', '<script src="' . OSCOM::getPublicSiteLink('external/dropzone/5.0.0/dropzone.min.js') . '"></script>');

        $js = <<<EOD
$(function() {
  $('#nbSubmit').addClass('active');
});
EOD;

        $OSCOM_Template->addJavascriptBlock($js);

        $current_app = $OSCOM_Template->getValue('current_app');

        if (!empty($current_app)) {
            $addon = Apps::getAddOnInfo($current_app);
            $addon_authors = Apps::getAddOnAuthors($current_app, false);

            $OSCOM_Template->setValue('addon', $addon);

            $is_owner = (($addon['userprofile_id'] > 0) && isset($_SESSION['Website']['Account']) && ($addon['userprofile_id'] == $_SESSION['Website']['Account']['id'])) ? true : false;
            $can_upload_update = (($is_owner === true) || ($addon['open_flag'] == '1') || (isset($_SESSION['Website']['Account']) && in_array($_SESSION['Website']['Account']['id'], $addon_authors))) ? true : false;

            $OSCOM_Template->setValue('is_owner', $is_owner);
            $OSCOM_Template->setValue('can_upload_update', $can_upload_update);

            if ($can_upload_update !== true) {
                $OSCOM_MessageStack->add('Index', OSCOM::getDef('ms_error_submit_update_no_access'), 'error');

                OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
            }

            if (Apps::isInQueue($current_app)) {
                $OSCOM_MessageStack->add('Index', OSCOM::getDef('ms_error_submit_update_in_queue'), 'error');

                OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
            }

            $this->_page_contents = 'update.html';
            $this->_page_title = OSCOM::getDef('html_page_title_update');
        } else {
            $this->_page_contents = 'new.html';
            $this->_page_title = OSCOM::getDef('html_page_title');
        }
    }
}
