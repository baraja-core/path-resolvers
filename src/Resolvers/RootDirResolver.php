<?php

declare(strict_types=1);

namespace Baraja\PathResolvers\Resolvers;


final class RootDirResolver
{
	private VendorResolver $vendorResolver;


	public function __construct(VendorResolver $vendorResolver)
	{
		$this->vendorResolver = $vendorResolver;
	}


	public function get(?string $customPath = null): string
	{
		return str_replace(
			['/', '\\'],
			DIRECTORY_SEPARATOR,
			dirname($this->vendorResolver->get()) . ($customPath !== null ? '/' . $customPath : ''),
		);
	}
}
