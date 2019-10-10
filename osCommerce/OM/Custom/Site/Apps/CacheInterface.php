<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

interface CacheInterface extends \Psr\SimpleCache\CacheInterface
{
    public function cleanup($key);
    public function canUse(): bool;
}
