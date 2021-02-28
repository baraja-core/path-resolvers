<?php

declare(strict_types=1);

namespace Baraja\PathResolvers\Resolvers;


final class TempDirResolver
{
	private RootDirResolver $rootDirResolver;

	private ?string $tempDir;

	private string $tempDirName;


	public function __construct(RootDirResolver $rootDirResolver, ?string $tempDir = null, string $tempDirName = 'temp')
	{
		$this->rootDirResolver = $rootDirResolver;
		$this->tempDir = $tempDir;
		$this->tempDirName = $tempDirName;
	}


	public function get(?string $customPath = null): string
	{
		return str_replace(
			['/', '\\'],
			DIRECTORY_SEPARATOR,
			($this->tempDir ?? ($this->rootDirResolver->get($this->tempDirName)))
			. ($customPath !== null ? '/' . $customPath : '')
		);
	}
}
