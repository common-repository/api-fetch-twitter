<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd5dc8141928f42d758cb07c605423d97 {

	public static $prefixLengthsPsr4 = array(
		'C' =>
		array(
			'Composer\\CaBundle\\' => 18,
		),
		'A' =>
		array(
			'Abraham\\TwitterOAuth\\' => 21,
		),
	);

	public static $prefixDirsPsr4 = array(
		'Composer\\CaBundle\\'    =>
		array(
			0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
		),
		'Abraham\\TwitterOAuth\\' =>
		array(
			0 => __DIR__ . '/..' . '/abraham/twitteroauth/src',
		),
	);

	public static $classMap = array(
		'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
	);

	public static function getInitializer( ClassLoader $loader ) {
		return \Closure::bind(
			function () use ( $loader ) {
				$loader->prefixLengthsPsr4 = ComposerStaticInitd5dc8141928f42d758cb07c605423d97::$prefixLengthsPsr4;
				$loader->prefixDirsPsr4    = ComposerStaticInitd5dc8141928f42d758cb07c605423d97::$prefixDirsPsr4;
				$loader->classMap          = ComposerStaticInitd5dc8141928f42d758cb07c605423d97::$classMap;

			},
			null,
			ClassLoader::class
		);
	}
}
