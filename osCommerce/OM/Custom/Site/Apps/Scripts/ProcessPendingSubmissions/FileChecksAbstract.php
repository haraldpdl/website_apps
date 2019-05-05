<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions;

abstract class FileChecksAbstract
{
    public static $priority;

    public $public_fail_error = 'Failed';

    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    abstract public function execute(): bool;
}
