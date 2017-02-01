<?php
/**
 * test suite bootstrap.
 *
 * Tries to include Composer vendor/autoload.php; dies if it does not exist.
 *
 * @category  Location
 * @author    Carsten Witt <tomkyle@posteo.de>
 */

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!is_readable( $autoloader )) {
    die("\nMissing Composer's vendor/autoload.php; run 'composer update' first.\n\n");
}
require $autoloader;
