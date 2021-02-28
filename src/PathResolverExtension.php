<?php

declare(strict_types=1);

namespace Baraja\PathResolver;


use Baraja\PathResolvers\Resolvers\LogDirResolver;
use Baraja\PathResolvers\Resolvers\RootDirResolver;
use Baraja\PathResolvers\Resolvers\TempDirResolver;
use Baraja\PathResolvers\Resolvers\VendorResolver;
use Baraja\PathResolvers\Resolvers\WwwDirResolver;
use Nette\DI\CompilerExtension;

final class PathResolverExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('vendorResolver'))
			->setFactory(VendorResolver::class);

		$builder->addDefinition($this->prefix('rootResolver'))
			->setFactory(RootDirResolver::class);

		$builder->addDefinition($this->prefix('wwwResolver'))
			->setFactory(WwwDirResolver::class);

		$builder->addDefinition($this->prefix('tempResolver'))
			->setFactory(TempDirResolver::class);

		$builder->addDefinition($this->prefix('logResolver'))
			->setFactory(LogDirResolver::class);
	}
}
