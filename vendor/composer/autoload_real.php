<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitec402472f4b8ea33d2c27b94bf38e199
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

        spl_autoload_register(array('ComposerAutoloaderInitec402472f4b8ea33d2c27b94bf38e199', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitec402472f4b8ea33d2c27b94bf38e199', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitec402472f4b8ea33d2c27b94bf38e199::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
