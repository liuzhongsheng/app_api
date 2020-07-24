<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc6a5fd2d7f53c16cfcaa25c7be9ade39
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\' => 10,
        ),
        'M' => 
        array (
            'Medoo\\' => 6,
        ),
        'G' => 
        array (
            'GatewayWorker\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
        'Medoo\\' => 
        array (
            0 => __DIR__ . '/..' . '/catfan/medoo/src',
        ),
        'GatewayWorker\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/gateway-worker/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc6a5fd2d7f53c16cfcaa25c7be9ade39::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc6a5fd2d7f53c16cfcaa25c7be9ade39::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
