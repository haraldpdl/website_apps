<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

  use osCommerce\OM\Core\Registry;

  class GetInfo {
    public static function execute($data) {
      $OSCOM_PDO = Registry::get('PDO');

      $result = [ ];

      $Qapp = $OSCOM_PDO->prepare('select a.id, ap.code as provider_code, ap.title as provider_title, a.code, ad.title, ad.description, a.partner_id, a.legacy_addon_id from :table_website_apps_providers ap, :table_website_apps a, :table_website_apps_description ad where ap.code = :provider_code and ap.id = a.provider_id and a.code = :app_code and a.id = ad.app_id and ad.language_id = :language_id');
      $Qapp->bindValue(':provider_code', $data['provider']);
      $Qapp->bindValue(':app_code', $data['app']);
      $Qapp->bindInt(':language_id', $data['language_id']);
      $Qapp->execute();

      $app = $Qapp->fetch();

      if ( !empty($app) ) {
        $Qrel = $OSCOM_PDO->prepare('select af.id, af.app_version, af.core_dep, unix_timestamp(af.date_added) as date_added, ac.changelog from :table_website_apps_files af, :table_website_apps_files_changelog ac where af.app_id = :app_id and af.id = ac.file_id and ac.language_id = :language_id order by af.core_dep, af.app_version');
        $Qrel->bindInt(':app_id', $app['id']);
        $Qrel->bindInt(':language_id', $data['language_id']);
        $Qrel->execute();

        while ( $Qrel->fetch() ) {
          $app['releases'][$Qrel->value('core_dep')][] = [ 'id' => $Qrel->valueInt('id'),
                                                           'version' => $Qrel->value('app_version'),
                                                           'date_added' => date('j M Y', $Qrel->value('date_added')),
                                                           'changelog' => $Qrel->value('changelog')
                                                         ];
        }

        if ( isset($app['releases']) ) {
          $result = $app;
          unset($app);
        }
      }

      return $result;
    }
  }
?>
