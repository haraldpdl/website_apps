<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\Application\Index;

  use osCommerce\OM\Core\OSCOM;

  class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract {
    protected function initialize() {
      if ( !OSCOM::isRPC() ) {
        header('X-Robots-Tag: none');

        OSCOM::redirect('http://www.oscommerce.com');
      }
    }
  }
?>
