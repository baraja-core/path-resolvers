<?php

declare(strict_types=1);

namespace Baraja\PathResolvers\Resolvers;


final class LogDirResolver
{
	private RootDirResolver $rootDirResolver;

	private ?string $logDir;

	private string $logDirName;


	public function __construct(RootDirResolver $rootDirResolver, ?string $logDir = null, string $logDirName = 'log')
	{
		$this->rootDirResolver = $rootDirResolver;
		$this->logDir = $logDir;
		$this->logDirName = $logDirName;
	}


	public function get(?string $customPath = null): string
	{
		return str_replace(
			['/', '\\'],
			DIRECTORY_SEPARATOR,
			($this->logDir ?? ($this->rootDirResolver->get($this->logDirName)))
			. ($customPath !== null ? '/' . $customPath : ''),
		);
	}
}
