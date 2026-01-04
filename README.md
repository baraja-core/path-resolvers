# 🗂️ Path Resolvers

A group of intelligent DI services that provide disk paths to important locations such as `tempDir`, `wwwDir`, `vendorDir`, `logDir` and other system constants for your applications. Dependency management can be easily solved from one place.

## 🎯 Key Features

- **Automatic path detection** - Uses Composer's ClassLoader reflection to automatically detect the vendor directory
- **Zero configuration** - Works out of the box with Nette Framework via DIC extension
- **CLI and Web support** - Properly detects paths in both CLI and web server environments
- **PHAR archive support** - Special handling for applications bundled in PHAR archives
- **Cross-platform compatibility** - Normalizes directory separators for Windows and Unix systems
- **Performance optimized** - Static caching prevents redundant path resolution
- **Customizable** - Override any path via constructor injection or DI configuration
- **Type-safe** - Fully typed with PHP 8.0+ support and PHPStan level 8 compatibility

## 🏗️ Architecture

The package follows a hierarchical resolver pattern where each resolver can depend on others:

```
┌─────────────────────────────────────────────────────────────┐
│                    PathResolverExtension                    │
│                  (Nette DI CompilerExtension)               │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      VendorResolver                         │
│            (Core resolver - detects vendor dir)             │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     RootDirResolver                         │
│          (Derives root from vendor - parent dir)            │
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
┌───────────────────┐ ┌─────────────┐ ┌─────────────┐
│   WwwDirResolver  │ │TempDirResolver│ │LogDirResolver │
│   (www/ or auto)  │ │  (temp/)    │ │   (log/)    │
└───────────────────┘ └─────────────┘ └─────────────┘
```

## 🧩 Components

### VendorResolver

The core resolver that detects the Composer vendor directory. It uses multiple detection strategies:

1. **Primary method**: Reflects on `Composer\Autoload\ClassLoader` to find its file location
2. **CLI fallback**: For PHAR or system-installed PHP, uses debug backtrace to locate vendor
3. **Error handling**: Throws `RuntimeException` if vendor cannot be detected

```php
$vendorResolver = new VendorResolver();
$vendorPath = $vendorResolver->get(); // e.g., "/var/www/project/vendor"
```

### RootDirResolver

Resolves the project root directory by getting the parent directory of vendor. Supports custom path appending.

```php
$rootResolver = new RootDirResolver($vendorResolver);
$rootPath = $rootResolver->get();           // e.g., "/var/www/project"
$configPath = $rootResolver->get('config'); // e.g., "/var/www/project/config"
```

### WwwDirResolver

Resolves the public web directory. Uses intelligent detection:

1. If custom `wwwDir` is provided via constructor, uses that
2. If called from `index.php`, returns its directory
3. Falls back to `{root}/www`

```php
$wwwResolver = new WwwDirResolver($rootResolver);
$wwwPath = $wwwResolver->get(); // e.g., "/var/www/project/www"

// Or with custom path
$wwwResolver = new WwwDirResolver($rootResolver, '/custom/public');
```

### TempDirResolver

Resolves the temporary directory. Supports custom paths and directory names.

```php
$tempResolver = new TempDirResolver($rootResolver);
$tempPath = $tempResolver->get();          // e.g., "/var/www/project/temp"
$cachePath = $tempResolver->get('cache');  // e.g., "/var/www/project/temp/cache"

// Custom temp directory name
$tempResolver = new TempDirResolver($rootResolver, null, 'tmp');
$tempPath = $tempResolver->get(); // e.g., "/var/www/project/tmp"
```

### LogDirResolver

Resolves the log directory. Supports custom paths and directory names.

```php
$logResolver = new LogDirResolver($rootResolver);
$logPath = $logResolver->get();           // e.g., "/var/www/project/log"
$errorLog = $logResolver->get('error');   // e.g., "/var/www/project/log/error"

// Custom log directory name
$logResolver = new LogDirResolver($rootResolver, null, 'logs');
$logPath = $logResolver->get(); // e.g., "/var/www/project/logs"
```

## 📦 Installation

It's best to use [Composer](https://getcomposer.org) for installation, and you can also find the package on
[Packagist](https://packagist.org/packages/baraja-core/path-resolvers) and
[GitHub](https://github.com/baraja-core/path-resolvers).

To install, simply use the command:

```shell
$ composer require baraja-core/path-resolvers
```

You can use the package manually by creating an instance of the internal classes, or register a DIC extension to link the services directly to the Nette Framework.

### Requirements

- PHP 8.0 or higher
- Nette DI 3.0+ (for DIC integration)
- Composer autoloader

## 🚀 Basic Usage

### With Nette Framework (Recommended)

Register the extension in your `config.neon`:

```neon
extensions:
    pathResolver: Baraja\PathResolver\PathResolverExtension
```

All resolvers are automatically registered as services and can be injected:

```php
use Baraja\PathResolvers\Resolvers\TempDirResolver;
use Baraja\PathResolvers\Resolvers\LogDirResolver;

final class MyService
{
    public function __construct(
        private TempDirResolver $tempResolver,
        private LogDirResolver $logResolver,
    ) {
    }

    public function getCacheDir(): string
    {
        return $this->tempResolver->get('cache');
    }

    public function getErrorLogPath(): string
    {
        return $this->logResolver->get('error.log');
    }
}
```

### Standalone Usage (Without Nette)

You can use the resolvers without the DI extension:

```php
use Baraja\PathResolvers\Resolvers\VendorResolver;
use Baraja\PathResolvers\Resolvers\RootDirResolver;
use Baraja\PathResolvers\Resolvers\TempDirResolver;
use Baraja\PathResolvers\Resolvers\LogDirResolver;
use Baraja\PathResolvers\Resolvers\WwwDirResolver;

// Create resolvers manually
$vendorResolver = new VendorResolver();
$rootResolver = new RootDirResolver($vendorResolver);
$tempResolver = new TempDirResolver($rootResolver);
$logResolver = new LogDirResolver($rootResolver);
$wwwResolver = new WwwDirResolver($rootResolver);

// Use them
echo $rootResolver->get();        // /var/www/project
echo $tempResolver->get('cache'); // /var/www/project/temp/cache
echo $logResolver->get();         // /var/www/project/log
echo $wwwResolver->get();         // /var/www/project/www
```

## ⚙️ Configuration

### Custom Paths via DI

Override default paths in your Nette configuration:

```neon
services:
    pathResolver.tempResolver:
        factory: Baraja\PathResolvers\Resolvers\TempDirResolver
        arguments:
            tempDir: %tempDir%
            tempDirName: 'tmp'

    pathResolver.logResolver:
        factory: Baraja\PathResolvers\Resolvers\LogDirResolver
        arguments:
            logDir: %logDir%
            logDirName: 'logs'

    pathResolver.wwwResolver:
        factory: Baraja\PathResolvers\Resolvers\WwwDirResolver
        arguments:
            wwwDir: %wwwDir%
```

### Constructor Parameters

| Resolver | Parameter | Type | Default | Description |
|----------|-----------|------|---------|-------------|
| `TempDirResolver` | `tempDir` | `?string` | `null` | Custom absolute path to temp directory |
| `TempDirResolver` | `tempDirName` | `string` | `'temp'` | Directory name relative to root |
| `LogDirResolver` | `logDir` | `?string` | `null` | Custom absolute path to log directory |
| `LogDirResolver` | `logDirName` | `string` | `'log'` | Directory name relative to root |
| `WwwDirResolver` | `wwwDir` | `?string` | `null` | Custom absolute path to www directory |

## 💡 Use Cases

### Storing Cache Files

```php
public function __construct(private TempDirResolver $tempResolver) {}

public function getCacheFile(string $key): string
{
    return $this->tempResolver->get('cache/' . md5($key) . '.cache');
}
```

### Writing Log Files

```php
public function __construct(private LogDirResolver $logResolver) {}

public function writeLog(string $message): void
{
    $logFile = $this->logResolver->get('app.log');
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
}
```

### Serving Static Files

```php
public function __construct(private WwwDirResolver $wwwResolver) {}

public function getAssetPath(string $asset): string
{
    return $this->wwwResolver->get() . '/assets/' . $asset;
}
```

### Project Configuration

```php
public function __construct(private RootDirResolver $rootResolver) {}

public function getConfigPath(): string
{
    return $this->rootResolver->get('config/app.neon');
}
```

## 🔧 Technical Details

### Path Normalization

All resolvers normalize directory separators using `DIRECTORY_SEPARATOR` for cross-platform compatibility:

- Windows: `C:\project\temp\cache`
- Unix/Linux: `/var/www/project/temp/cache`

### Caching

The `VendorResolver` uses static caching to prevent redundant filesystem operations:

```php
public function get(): string
{
    static $cache;
    return $cache ?? $cache = $this->detect();
}
```

### CLI Detection

For CLI environments running from PHAR archives or system-installed PHP, the vendor resolver uses debug backtrace as a fallback detection method.

## 👤 Author

**Jan Barášek**
[https://baraja.cz](https://baraja.cz)

## 📄 License

`baraja-core/path-resolvers` is licensed under the MIT license. See the [LICENSE](https://github.com/baraja-core/path-resolvers/blob/master/LICENSE) file for more details.
