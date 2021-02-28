<?php

declare(strict_types=1);

namespace Baraja\PathResolvers\Resolvers;


use Composer\Autoload\ClassLoader;

final class VendorResolver
{
	public function get(): string
	{
		static $cache;

		return $cache ?? $cache = $this->detect();
	}


	private function detect(): string
	{
		try {
			if (class_exists(ClassLoader::class)) {
				$loaderRc = new \ReflectionClass(ClassLoader::class);
				$vendorDir = dirname((string) $loaderRc->getFileName(), 2) ?: null;
			} else {
				$vendorDir = null;
			}
		} catch (\ReflectionException) {
			$vendorDir = null;
		}
		if (
			$vendorDir !== null
			&& PHP_SAPI === 'cli'
			&& (
				str_starts_with($vendorDir, 'phar://')
				|| str_starts_with($vendorDir, '/usr/share')
			)
		) {
			$vendorDirFile = str_replace(DIRECTORY_SEPARATOR, '/', debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0]['file']);
			$vendorDir = (string) preg_replace('/^(.+?\/vendor)(.*)$/', '$1', (string) $vendorDirFile);
		}
		if ($vendorDir === null) {
			throw new \RuntimeException('Can not resolve "vendorDir". Did you generate Composer autoloader by "composer install" or "composer dump" command?');
		}

		return $vendorDir;
	}
}
