<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\PDO;
  use osCommerce\OM\Core\Registry;

  class Controller implements \osCommerce\OM\Core\SiteInterface {
    protected static $_default_application = 'Index';

    public static function initialize() {
      if ( !OSCOM::isRPC() ) {
        OSCOM::redirect('http://www.oscommerce.com');
      }

      Registry::set('PDO', PDO::initialize());
    }

    public static function getDefaultApplication() {
      return self::$_default_application;
    }

    public static function hasAccess($application) {
      return true;
    }
  }
?>
