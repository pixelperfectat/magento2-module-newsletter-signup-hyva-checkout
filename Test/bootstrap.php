<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Magewire ships with the commercial Hyvä Checkout, which is only suggested: use a stand-in unless it is installed.
if (!class_exists(\Magewirephp\Magewire\Component::class)) {
    require __DIR__ . '/Stubs/Component.php';
}
