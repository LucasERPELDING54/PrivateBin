<?php
/**
 * Configuration file for PHP Coding Standards Fixer (php-cs-fixer).
 *
 * On GitHub: https://github.com/FriendsOfPhp/php-cs-fixer
 * More information: http://cs.sensiolabs.org/
 */

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
    ])
    ->setFinder($finder)
;
