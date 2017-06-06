<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\OSCOM;

class MessageStack extends \osCommerce\OM\Core\MessageStack
{
    public function get(string $group = null) : string
    {
        if (!isset($group) || empty($group)) {
            $group = OSCOM::getSiteApplication();
        }

        $result = '';

        if ($this->exists($group)) {
            $result .= '<div id="msgStk_' . $group . '">';

            $messages = [];

            foreach ($this->_data[$group] as $message) {
                if ($message['type'] == 'error') {
                    $message['type'] = 'danger';
                }

                $messages[$message['type']][] = $message['text'];
            }

            foreach (array_keys($messages) as $type) {
                $result .= '<div class="alert alert-' . $type . '" role="alert">';

                $ait = new \ArrayIterator($messages[$type]);
                $cit = new \CachingIterator($ait);

                foreach ($cit as $m) {
                    $result .= '<p' . (!$cit->hasNext() ? ' class="mb-0"' : '') . '>' . $m . '</p>';
                }

                $result .= '</div>';
            }

            $result .= '</div>';

            unset($this->_data[$group]);
        }

        return $result;
    }
}
