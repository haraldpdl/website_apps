<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecks;

use osCommerce\OM\Core\{
    FileSystem,
    RunScript
};

class PhpLint extends \osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecksAbstract
{
    public static $priority = 300;

    public $public_fail_error = 'PHP syntax error detected';

    public function execute(): bool
    {
        $pass = true;

        $ext = pathinfo($this->file, \PATHINFO_EXTENSION);

        if (strtolower($ext) !== 'zip') {
            return true;
        }

        $php = RunScript::$php_binary ?? '/usr/bin/php73';
        $tmp_directory = sys_get_temp_dir() . '/oscomAppsScriptPhpLint/';

        FileSystem::rmdir($tmp_directory);

        $zip = new \ZipArchive();
        $zip->open($this->file);
        $zip->extractTo($tmp_directory);
        $zip->close();

        foreach (FileSystem::getDirectoryContents($tmp_directory) as $f) {
            if (pathinfo($f, \PATHINFO_EXTENSION) == 'php') {
                $output = null;

                exec($php . ' -l ' . escapeshellarg($f) . ' 2>&1', $output);

                if (is_array($output) && !empty($output)) {
                    if (preg_match('/^No syntax errors detected in/', end($output)) === 1) {
                        continue;
                    }

                    foreach ($output as &$o) {
                        $o = str_replace($tmp_directory, '', $o);
                    }

                    $this->public_fail_error = implode("\n", $output);

                    $pass = false;

                    break;
                }
            }
        }

        FileSystem::rmdir($tmp_directory);

        return ($pass === true);
    }
}
