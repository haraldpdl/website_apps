<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\PDO;
  use osCommerce\OM\Core\Registry;

  class Controller implements \osCommerce\OM\Core\SiteInterface {
    protected static $_default_application = 'Index';

    public static function initialize() {
      Registry::set('Cache', new Cache());
      Registry::set('PDO', PDO::initialize());

      $application = __NAMESPACE__ . '\\Application\\' . OSCOM::getSiteApplication() . '\\Controller';
      Registry::set('Application', new $application());
    }

    public static function getDefaultApplication() {
      return static::$_default_application;
    }

    public static function hasAccess($application) {
      return true;
    }
  }
?>
