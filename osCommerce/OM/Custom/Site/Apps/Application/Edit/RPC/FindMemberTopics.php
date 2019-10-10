<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Edit\RPC;

use osCommerce\OM\Core\OSCOM;

use osCommerce\OM\Core\Site\Website\Invision;

class FindMemberTopics
{
    public static function execute()
    {
        header('Content-Type: application/json');

        $result = [
            'suggestions' => []
        ];

        $error = null;

        if (!isset($_SESSION['Website']['Account'])) {
            $error = true;
        }

        if (!isset($error)) {
            $public_token = isset($_POST['public_token']) ? trim(str_replace(["\r\n", "\n", "\r"], '', $_POST['public_token'])) : '';

            if ($public_token !== md5($_SESSION['Website']['public_token'])) {
                $error = true;
            }
        }

        if (!isset($error)) {
            if (isset($_POST['query']) && !empty($_POST['query'])) {
                $query = str_replace(["\r\n", "\n", "\r"], '', $_POST['query']);

                foreach (Invision::findMemberTopics($_SESSION['Website']['Account']['id'], $query, Invision::FORUM_ADDONS_CATEGORY_IDS) as $t) {
                    $result['suggestions'][] = [
                        'value' => $t['title'] . ' (' . $t['forum_title'] . ')',
                        'id' => $t['id'],
                        'link' => 'https://forums.oscommerce.com/topic/' . $t['id'] . '-' . $t['title_seo'],
                        'title_seo' => $t['title_seo']
                    ];
                }

                if (empty($result['suggestions'])) {
                    $result['suggestions'][] = [
                        'value' => OSCOM::getDef('error_topic_not_found'),
                        'id' => '0'
                    ];
                }
            }
        }

        echo json_encode($result);
    }
}
