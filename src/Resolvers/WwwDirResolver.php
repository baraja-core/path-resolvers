<?php

declare(strict_types=1);

namespace Baraja\PathResolvers\Resolvers;


final class WwwDirResolver
{
	private RootDirResolver $rootDirResolver;

	private ?string $wwwDir;


	public function __construct(RootDirResolver $rootDirResolver, ?string $wwwDir = null)
	{
		$this->rootDirResolver = $rootDirResolver;
		$this->wwwDir = $wwwDir;
	}


	public function get(): string
	{
		if ($this->wwwDir !== null) {
			return $this->wwwDir;
		}
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$last = end($trace);

		if ($last === false || isset($last['file']) === false) {
			throw new \RuntimeException('WwwDir can not be detected. Did you use DI?');
		}
		if (str_ends_with($last['file'], DIRECTORY_SEPARATOR . 'index.php') === false) {
			return $this->rootDirResolver->get('www');
		}

		return dirname($last['file']);
	}
}
