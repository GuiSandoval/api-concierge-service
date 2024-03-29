<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9c499a6d662a6ed9e1736e35c175f4d5
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9c499a6d662a6ed9e1736e35c175f4d5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9c499a6d662a6ed9e1736e35c175f4d5::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
