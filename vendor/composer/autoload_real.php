<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitaeebd4b381ebeb0baeea8959a4bd7dbf
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitaeebd4b381ebeb0baeea8959a4bd7dbf', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitaeebd4b381ebeb0baeea8959a4bd7dbf', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitaeebd4b381ebeb0baeea8959a4bd7dbf::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}