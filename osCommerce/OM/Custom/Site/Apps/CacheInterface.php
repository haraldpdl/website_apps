<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

interface CacheInterface extends \Psr\SimpleCache\CacheInterface
{
    public function cleanup($key);
    public function canUse(): bool;
}
