<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteef552a62a76762763ba4efd6fa54d13
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteef552a62a76762763ba4efd6fa54d13::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteef552a62a76762763ba4efd6fa54d13::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
