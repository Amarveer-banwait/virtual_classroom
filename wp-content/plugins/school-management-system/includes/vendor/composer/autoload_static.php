<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5d2f1813b77a82e71a057414811f6ebc
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5d2f1813b77a82e71a057414811f6ebc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5d2f1813b77a82e71a057414811f6ebc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5d2f1813b77a82e71a057414811f6ebc::$classMap;

        }, null, ClassLoader::class);
    }
}
