<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

use osCommerce\OM\Core\Site\Apps\Apps;

class GetSearchListing
{
    public static function execute(array $params)
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $result = [];

        $query = 'select SQL_CALC_FOUND_ROWS left(p.title, :title_length) as title, left(p.short_description, :short_description_length) as short_description, p.public_id, p.cover_image, if(p.public_flag = 1, 1, null) as open_flag, date_format(max(f.date_added), "%Y%m%d %H%i%s") as last_update_date, if(ma.id > 0, 1, null) as certified';

        if (!isset($params['sort'])) {
            $query .= ', match (p.title, p.description) against (:keywords) as pscore';

            if ($params['deep_search'] === true) {
                $query .= ', match (f.title, f.description) against (:keywords) as fscore';
            }
        }

        $query .= ' from contrib_files f, contrib_packages p left join modules_addons ma on (p.id = ma.addons_package_id), contrib_categories c, contrib_versions v where f.status = 1 and f.contrib_packages_id = p.id and p.status = 1 and p.contrib_categories_id = c.id and c.status = 1 and p.contrib_versions_id = v.id and v.status = 1 ';

        if (isset($params['version'])) {
            $query .= 'and v.code = :version_code ';
        }

        $query .= 'and (match (p.title, p.description) against (:keywords)';

        if ($params['deep_search'] === true) {
            $query .= ' or match (f.title, f.description) against (:keywords)';
         }

         $query .= ') group by f.contrib_packages_id order by certified desc, ';

        if (!isset($params['sort'])) {
            $query .= '(pscore';

            if ($params['deep_search'] === true) {
                $query .= ' + fscore';
            }

            $query .= ') desc ';
        } else {
            $query .= 'max(f.date_added) desc ';
        }

        $query .= 'limit :batch_pageset, :batch_max_results; select found_rows();';

        $Qlisting = $OSCOM_PDO_OLD->prepare($query);
        $Qlisting->bindInt(':title_length', Apps::TITLE_LENGTH);
        $Qlisting->bindInt(':short_description_length', Apps::SHORT_DESCRIPTION_LENGTH);

        if (isset($params['version'])) {
            $Qlisting->bindValue(':version_code', $params['version']);
        }

        $Qlisting->bindValue(':keywords', $params['keywords']);
        $Qlisting->bindInt(':batch_pageset', $OSCOM_PDO_OLD->getBatchFrom($params['pageset'], 24));
        $Qlisting->bindInt(':batch_max_results', 24);
        $Qlisting->execute();

        $result['entries'] = $Qlisting->fetchAll();

        $Qlisting->nextRowset();

        $result['total'] = $Qlisting->fetchColumn();

        return $result;
    }
}
