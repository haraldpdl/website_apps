<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Edit\RPC;

use osCommerce\OM\Core\Site\Website\Invision;

class FindMembers
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

                foreach (Invision::findMembers($query) as $m) {
                    $result['suggestions'][] = [
                        'value' => $m['name'],
                        'id' => $m['id']
                    ];
                }
            }
        }

        echo json_encode($result);
    }
}
