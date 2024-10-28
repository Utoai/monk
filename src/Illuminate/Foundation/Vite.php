<?php

namespace Illuminate\Foundation;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class Vite implements Htmlable
{
    use Macroable;

    /**
     * 内容安全策略 nonce 应用于所有生成的标记。
     *
     * @var string|null
     */
    protected $nonce;

    /**
     * 用于检查清单中完整性哈希的键。
     *
     * @var string|false
     */
    protected $integrityKey = 'integrity';

    /**
     * 配置的入口点。
     *
     * @var array
     */
    protected $entryPoints = [];

    /**
     * “hot” 文件的路径。
     *
     * @var string|null
     */
    protected $hotFile;

    /**
     * 生成目录的路径。
     *
     * @var string
     */
    protected $buildDirectory = 'build';

    /**
     * 清单文件的名称。
     *
     * @var string
     */
    protected $manifestFilename = 'manifest.json';

    /**
     * 自定义资产路径解析程序。
     *
     * @var callable|null
     */
    protected $assetPathResolver = null;

    /**
     * 脚本标记属性解析程序。
     *
     * @var array
     */
    protected $scriptTagAttributesResolvers = [];

    /**
     * 样式标记属性解析程序。
     *
     * @var array
     */
    protected $styleTagAttributesResolvers = [];

    /**
     * preload 标签属性 resolvers。
     *
     * @var array
     */
    protected $preloadTagAttributesResolvers = [];

    /**
     * 预加载的资产。
     *
     * @var array
     */
    protected $preloadedAssets = [];

    /**
     * 缓存的清单文件。
     *
     * @var array
     */
    protected static $manifests = [];

    /**
     * 要使用的预取策略。
     *
     * @var null|'waterfall'|'aggressive'
     */
    protected $prefetchStrategy = null;

    /**
     * 使用 “waterfall” 策略时要同时加载的资产数量。
     *
     * @var int
     */
    protected $prefetchConcurrently = 3;

    /**
     * 应触发预取的事件的名称。该事件必须在 'window' 上调度。     
     * 
     * @var string
     */
    protected $prefetchEvent = 'load';

    /**
     * 获取预加载的资产。
     *
     * @return array
     */
    public function preloadedAssets()
    {
        echo "<br />-------------------------preloadedAssets-------------------------<br />";
        var_dump($this->preloadedAssets);
        echo "<br />-------------------------preloadedAssets-------------------------<br />";
        return $this->preloadedAssets;
    }

    /**
     * 获取应用于所有生成的标记的内容安全策略 nonce。
     *
     * @return string|null
     */
    public function cspNonce()
    {
        echo "<br />-------------------------cspNonce-------------------------<br />";
        var_dump($this->nonce);
        echo "<br />-------------------------cspNonce-------------------------<br />";
        return $this->nonce;
    }

    /**
     * 生成或设置内容安全策略 nonce 以应用于所有生成的标记。
     *
     * @param  string|null  $nonce
     * @return string
     */
    public function useCspNonce($nonce = null)
    {
        return $this->nonce = $nonce ?? Str::random(40);
    }

    /**
     * 使用给定的键检测清单中的完整性哈希。
     *
     * @param  string|false  $key
     * @return $this
     */
    public function useIntegrityKey($key)
    {
        $this->integrityKey = $key;

        return $this;
    }

    /**
     * Set the Vite entry points.
     *
     * @param  array  $entryPoints
     * @return $this
     */
    public function withEntryPoints($entryPoints)
    {
        $this->entryPoints = $entryPoints;

        return $this;
    }

    /**
     * Merge additional Vite entry points with the current set.
     *
     * @param  array  $entryPoints
     * @return $this
     */
    public function mergeEntryPoints($entryPoints)
    {
        return $this->withEntryPoints(array_unique([
            ...$this->entryPoints,
            ...$entryPoints,
        ]));
    }

    /**
     * Set the filename for the manifest file.
     *
     * @param  string  $filename
     * @return $this
     */
    public function useManifestFilename($filename)
    {
        $this->manifestFilename = $filename;

        return $this;
    }

    /**
     * Resolve asset paths using the provided resolver.
     *
     * @param  callable|null  $resolver
     * @return $this
     */
    public function createAssetPathsUsing($resolver)
    {
        $this->assetPathResolver = $resolver;

        return $this;
    }

    /**
     * 获取 Vite "hot" 文件路径。
     *
     * @return string
     */
    public function hotFile()
    {
        return $this->hotFile ?? public_path('/hot');
    }

    /**
     * 设置 Vite "hot" 文件路径。
     *
     * @param  string  $path
     * @return $this
     */
    public function useHotFile($path)
    {
        $this->hotFile = $path;

        return $this;
    }

    /**
     * 设置 Vite 构建目录。
     *
     * @param  string  $path
     * @return $this
     */
    public function useBuildDirectory($path)
    {
        $this->buildDirectory = $path;

        return $this;
    }

    /**
     * 使用给定的回调来解析脚本标签的属性。
     *
     * @param  (callable(string, string, ?array, ?array): array)|array  $attributes
     * @return $this
     */
    public function useScriptTagAttributes($attributes)
    {
        if (! is_callable($attributes)) {
            $attributes = fn() => $attributes;
        }

        $this->scriptTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * 使用给定的回调来解析样式标签的属性。
     *
     * @param  (callable(string, string, ?array, ?array): array)|array  $attributes
     * @return $this
     */
    public function useStyleTagAttributes($attributes)
    {
        if (! is_callable($attributes)) {
            $attributes = fn() => $attributes;
        }

        $this->styleTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * 使用给定的回调来解析预加载标签的属性。
     *
     * @param  (callable(string, string, ?array, ?array): (array|false))|array|false  $attributes
     * @return $this
     */
    public function usePreloadTagAttributes($attributes)
    {
        if (! is_callable($attributes)) {
            $attributes = fn() => $attributes;
        }

        $this->preloadTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * 预先获取资源。
     *
     * @param  int|null  $concurrency
     * @param  string  $event
     * @return $this
     */
    public function prefetch($concurrency = null, $event = 'load')
    {
        $this->prefetchEvent = $event;

        return $concurrency === null
            ? $this->usePrefetchStrategy('aggressive')
            : $this->usePrefetchStrategy('waterfall', ['concurrency' => $concurrency]);
    }

    /**
     * 使用 "waterfall" 预先获取策略。
     *
     * @return $this
     */
    public function useWaterfallPrefetching(?int $concurrency = null)
    {
        return $this->usePrefetchStrategy('waterfall', [
            'concurrency' => $concurrency ?? $this->prefetchConcurrently,
        ]);
    }

    /**
     * 使用 "aggressive" 预先获取策略。
     *
     * @return $this
     */
    public function useAggressivePrefetching()
    {
        return $this->usePrefetchStrategy('aggressive');
    }

    /**
     * 设置预先获取策略。
     *
     * @param  'waterfall'|'aggressive'|null  $strategy
     * @param  array  $config
     * @return $this
     */
    public function usePrefetchStrategy($strategy, $config = [])
    {
        $this->prefetchStrategy = $strategy;

        if ($strategy === 'waterfall') {
            $this->prefetchConcurrently = $config['concurrency'] ?? $this->prefetchConcurrently;
        }

        return $this;
    }

    /**
     * 为入口点生成 Vite 标签。
     *
     * @param  string|string[]  $entrypoints
     * @param  string|null  $buildDirectory
     * @return \Illuminate\Support\HtmlString
     *
     * @throws \Exception
     */
    public function __invoke($entrypoints, $buildDirectory = null)
    {
        $entrypoints = collect($entrypoints);
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return new HtmlString(
                $entrypoints
                    ->prepend('@vite/client')
                    ->map(fn($entrypoint) => $this->makeTagForChunk($entrypoint, $this->hotAsset($entrypoint), null, null))
                    ->join('')
            );
        }

        $manifest = $this->manifest($buildDirectory);

        $tags = collect();
        $preloads = collect();

        foreach ($entrypoints as $entrypoint) {
            $chunk = $this->chunk($manifest, $entrypoint);

            $preloads->push([
                $chunk['src'],
                $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                $chunk,
                $manifest,
            ]);

            foreach ($chunk['imports'] ?? [] as $import) {
                $preloads->push([
                    $import,
                    $this->assetPath("{$buildDirectory}/{$manifest[$import]['file']}"),
                    $manifest[$import],
                    $manifest,
                ]);

                foreach ($manifest[$import]['css'] ?? [] as $css) {
                    $partialManifest = Collection::make($manifest)->where('file', $css);

                    $preloads->push([
                        $partialManifest->keys()->first(),
                        $this->assetPath("{$buildDirectory}/{$css}"),
                        $partialManifest->first(),
                        $manifest,
                    ]);

                    $tags->push($this->makeTagForChunk(
                        $partialManifest->keys()->first(),
                        $this->assetPath("{$buildDirectory}/{$css}"),
                        $partialManifest->first(),
                        $manifest
                    ));
                }
            }

            $tags->push($this->makeTagForChunk(
                $entrypoint,
                $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                $chunk,
                $manifest
            ));

            foreach ($chunk['css'] ?? [] as $css) {
                $partialManifest = Collection::make($manifest)->where('file', $css);

                $preloads->push([
                    $partialManifest->keys()->first(),
                    $this->assetPath("{$buildDirectory}/{$css}"),
                    $partialManifest->first(),
                    $manifest,
                ]);

                $tags->push($this->makeTagForChunk(
                    $partialManifest->keys()->first(),
                    $this->assetPath("{$buildDirectory}/{$css}"),
                    $partialManifest->first(),
                    $manifest
                ));
            }
        }

        [$stylesheets, $scripts] = $tags->unique()->partition(fn($tag) => str_starts_with($tag, '<link'));

        $preloads = $preloads->unique()
            ->sortByDesc(fn($args) => $this->isCssPath($args[1]))
            ->map(fn($args) => $this->makePreloadTagForChunk(...$args));

        $base = $preloads->join('') . $stylesheets->join('') . $scripts->join('');

        if ($this->prefetchStrategy === null || $this->isRunningHot()) {
            return new HtmlString($base);
        }

        $discoveredImports = [];

        return collect($entrypoints)
            ->flatMap(fn($entrypoint) => collect($manifest[$entrypoint]['dynamicImports'] ?? [])
                ->map(fn($import) => $manifest[$import])
                ->filter(fn($chunk) => str_ends_with($chunk['file'], '.js') || str_ends_with($chunk['file'], '.css'))
                ->flatMap($f = function ($chunk) use (&$f, $manifest, &$discoveredImports) {
                    return collect([...$chunk['imports'] ?? [], ...$chunk['dynamicImports'] ?? []])
                        ->reject(function ($import) use (&$discoveredImports) {
                            if (isset($discoveredImports[$import])) {
                                return true;
                            }

                            return ! $discoveredImports[$import] = true;
                        })
                        ->reduce(
                            fn($chunks, $import) => $chunks->merge(
                                $f($manifest[$import])
                            ),
                            collect([$chunk])
                        )
                        ->merge(collect($chunk['css'] ?? [])->map(
                            fn($css) => collect($manifest)->first(fn($chunk) => $chunk['file'] === $css) ?? [
                                'file' => $css,
                            ],
                        ));
                })
                ->map(function ($chunk) use ($buildDirectory, $manifest) {
                    return collect([
                        ...$this->resolvePreloadTagAttributes(
                            $chunk['src'] ?? null,
                            $url = $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                            $chunk,
                            $manifest,
                        ),
                        'rel' => 'prefetch',
                        'fetchpriority' => 'low',
                        'href' => $url,
                    ])->reject(
                        fn($value) => in_array($value, [null, false], true)
                    )->mapWithKeys(fn($value, $key) => [
                        $key = (is_int($key) ? $value : $key) => $value === true ? $key : $value,
                    ])->all();
                })
                ->reject(fn($attributes) => isset($this->preloadedAssets[$attributes['href']])))
            ->unique('href')
            ->values()
            ->pipe(fn($assets) => with(Js::from($assets), fn($assets) => match ($this->prefetchStrategy) {
                'waterfall' => new HtmlString($base . <<<HTML

                    <script{$this->nonceAttribute()}>
                         window.addEventListener('{$this->prefetchEvent}', () => window.setTimeout(() => {
                            const makeLink = (asset) => {
                                const link = document.createElement('link')

                                Object.keys(asset).forEach((attribute) => {
                                    link.setAttribute(attribute, asset[attribute])
                                })

                                return link
                            }

                            const loadNext = (assets, count) => window.setTimeout(() => {
                                if (count > assets.length) {
                                    count = assets.length

                                    if (count === 0) {
                                        return
                                    }
                                }

                                const fragment = new DocumentFragment

                                while (count > 0) {
                                    const link = makeLink(assets.shift())
                                    fragment.append(link)
                                    count--

                                    if (assets.length) {
                                        link.onload = () => loadNext(assets, 1)
                                        link.error = () => loadNext(assets, 1)
                                    }
                                }

                                document.head.append(fragment)
                            })

                            loadNext({$assets}, {$this->prefetchConcurrently})
                        }))
                    </script>
                    HTML),
                'aggressive' => new HtmlString($base . <<<HTML

                    <script{$this->nonceAttribute()}>
                         window.addEventListener('{$this->prefetchEvent}', () => window.setTimeout(() => {
                            const makeLink = (asset) => {
                                const link = document.createElement('link')

                                Object.keys(asset).forEach((attribute) => {
                                    link.setAttribute(attribute, asset[attribute])
                                })

                                return link
                            }

                            const fragment = new DocumentFragment
                            {$assets}.forEach((asset) => fragment.append(makeLink(asset)))
                            document.head.append(fragment)
                         }))
                    </script>
                    HTML),
            }));
    }

    /**
     * 为给定的 chunk 制作一个标签。
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array|null  $chunk
     * @param  array|null  $manifest
     * @return string
     */
    protected function makeTagForChunk($src, $url, $chunk, $manifest)
    {
        if (
            $this->nonce === null
            && $this->integrityKey !== false
            && ! array_key_exists($this->integrityKey, $chunk ?? [])
            && $this->scriptTagAttributesResolvers === []
            && $this->styleTagAttributesResolvers === []
        ) {
            return $this->makeTag($url);
        }

        if ($this->isCssPath($url)) {
            return $this->makeStylesheetTagWithAttributes(
                $url,
                $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
            );
        }

        return $this->makeScriptTagWithAttributes(
            $url,
            $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)
        );
    }

    /**
     * 为给定的 chunk 制作一个 preload 标签。
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array  $chunk
     * @param  array  $manifest
     * @return string
     */
    protected function makePreloadTagForChunk($src, $url, $chunk, $manifest)
    {
        // 添加调试信息
        var_dump('Source:', $src);
        var_dump('URL:', $url);
        var_dump('Chunk:', $chunk);
        var_dump('Manifest:', $manifest);

        // 检查 `$chunk` 和 `$manifest` 是否包含非数组或对象数据类型
        foreach ($chunk as $key => $value) {
            if (is_object($value)) {
                var_dump('Chunk 键为', $key, '的数据是对象类型', get_class($value));
            } else {
                var_dump('Chunk 键为', $key, '的数据类型是', gettype($value));
            }
        }

        foreach ($manifest as $key => $value) {
            if (is_object($value)) {
                var_dump('Manifest 键为', $key, '的数据是对象类型', get_class($value));
            } else {
                var_dump('Manifest 键为', $key, '的数据类型是', gettype($value));
            }
        }

        // 处理标签生成
        $attributes = $this->resolvePreloadTagAttributes($src, $url, $chunk, $manifest);

        if ($attributes === false) {
            return '';
        }

        $this->preloadedAssets[$url] = $this->parseAttributes(
            Collection::make($attributes)->forget('href')->all()
        );

        return '<link ' . implode(' ', $this->parseAttributes($attributes)) . ' />';
    }


    /**
     * 解析生成的脚本标签的属性。
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array|null  $chunk
     * @param  array|null  $manifest
     * @return array
     */
    protected function resolveScriptTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->integrityKey !== false
            ? ['integrity' => $chunk[$this->integrityKey] ?? false]
            : [];

        foreach ($this->scriptTagAttributesResolvers as $resolver) {
            $attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
        }

        return $attributes;
    }

    /**
     * 解析生成的样式表标签的属性。
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array|null  $chunk
     * @param  array|null  $manifest
     * @return array
     */
    protected function resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->integrityKey !== false
            ? ['integrity' => $chunk[$this->integrityKey] ?? false]
            : [];

        foreach ($this->styleTagAttributesResolvers as $resolver) {
            $attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
        }

        return $attributes;
    }

    /**
     * 解析生成的预加载标签的属性。
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array  $chunk
     * @param  array  $manifest
     * @return array|false
     */
    protected function resolvePreloadTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->isCssPath($url) ? [
            'rel' => 'preload',
            'as' => 'style',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
            'crossorigin' => $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
        ] : [
            'rel' => 'modulepreload',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
            'crossorigin' => $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
        ];

        $attributes = $this->integrityKey !== false
            ? array_merge($attributes, ['integrity' => $chunk[$this->integrityKey] ?? false])
            : $attributes;

        foreach ($this->preloadTagAttributesResolvers as $resolver) {
            if (false === ($resolvedAttributes = $resolver($src, $url, $chunk, $manifest))) {
                return false;
            }

            $attributes = array_merge($attributes, $resolvedAttributes);
        }

        return $attributes;
    }

    /**
     * 在 HMR 模式下为给定的 URL 生成适当的标签。
     *
     * @deprecated 将在未来的 Laravel 版本中移除。
     *
     * @param  string  $url
     * @return string
     */
    protected function makeTag($url)
    {
        if ($this->isCssPath($url)) {
            return $this->makeStylesheetTag($url);
        }

        return $this->makeScriptTag($url);
    }

    /**
     * 为给定的 URL 生成脚本标签。
     *
     * @deprecated 将在未来的 Laravel 版本中移除。
     *
     * @param  string  $url
     * @return string
     */
    protected function makeScriptTag($url)
    {
        return $this->makeScriptTagWithAttributes($url, []);
    }

    /**
     * 在 HMR 模式下为给定的 URL 生成样式表标签。
     *
     * @deprecated 将在未来的 Laravel 版本中移除。
     *
     * @param  string  $url
     * @return string
     */
    protected function makeStylesheetTag($url)
    {
        return $this->makeStylesheetTagWithAttributes($url, []);
    }

    /**
     * 为给定的 URL 生成带有属性的脚本标签。
     *
     * @param  string  $url
     * @param  array  $attributes
     * @return string
     */
    protected function makeScriptTagWithAttributes($url, $attributes)
    {
        $attributes = $this->parseAttributes(array_merge([
            'type' => 'module',
            'src' => $url,
            'nonce' => $this->nonce ?? false,
        ], $attributes));

        return '<script ' . implode(' ', $attributes) . '></script>';
    }

    /**
     * 为给定的 URL 生成带有属性的链接标签。
     *
     * @param  string  $url
     * @param  array  $attributes
     * @return string
     */
    protected function makeStylesheetTagWithAttributes($url, $attributes)
    {
        $attributes = $this->parseAttributes(array_merge([
            'rel' => 'stylesheet',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
        ], $attributes));

        return '<link ' . implode(' ', $attributes) . ' />';
    }

    /**
     * 确定给定的路径是否为 CSS 文件。
     *
     * @param  string  $path
     * @return bool
     */
    protected function isCssPath($path)
    {
        return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $path) === 1;
    }

    /**
     * 将属性解析为 key="value" 字符串。
     *
     * @param  array  $attributes
     * @return array
     */
    protected function parseAttributes($attributes)
    {
        return Collection::make($attributes)
            ->reject(fn($value, $key) => in_array($value, [false, null], true))
            ->flatMap(fn($value, $key) => $value === true ? [$key] : [$key => $value])
            ->map(fn($value, $key) => is_int($key) ? $value : $key . '="' . $value . '"')
            ->values()
            ->all();
    }

    /**
     * 生成 React 刷新运行时脚本。
     *
     * @return \Illuminate\Support\HtmlString|void
     */
    public function reactRefresh()
    {
        if (! $this->isRunningHot()) {
            return;
        }

        $attributes = $this->parseAttributes([
            'nonce' => $this->cspNonce(),
        ]);

        return new HtmlString(
            sprintf(
                <<<'HTML'
                <script type="module" %s>
                    import RefreshRuntime from '%s'
                    RefreshRuntime.injectIntoGlobalHook(window)
                    window.$RefreshReg$ = () => {}
                    window.$RefreshSig$ = () => (type) => type
                    window.__vite_plugin_react_preamble_installed__ = true
                </script>
                HTML,
                implode(' ', $attributes),
                $this->hotAsset('@react-refresh')
            )
        );
    }

    /**
     * 获取在 HMR 模式下运行时给定资产的路径。
     *
     * @return string
     */
    protected function hotAsset($asset)
    {
        return rtrim(file_get_contents($this->hotFile())) . '/' . $asset;
    }

    /**
     * 获取资产的 URL。
     *
     * @param  string  $asset
     * @param  string|null  $buildDirectory
     * @return string
     */
    public function asset($asset, $buildDirectory = null)
    {
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return $this->hotAsset($asset);
        }

        $chunk = $this->chunk($this->manifest($buildDirectory), $asset);

        return $this->assetPath($buildDirectory . '/' . $chunk['file']);
    }

    /**
     * 获取给定资产的内容。
     *
     * @param  string  $asset
     * @param  string|null  $buildDirectory
     * @return string
     *
     * @throws \Illuminate\Foundation\ViteException
     */
    public function content($asset, $buildDirectory = null)
    {
        $buildDirectory ??= $this->buildDirectory;

        $chunk = $this->chunk($this->manifest($buildDirectory), $asset);

        $path = public_path($buildDirectory . '/' . $chunk['file']);

        if (! is_file($path) || ! file_exists($path)) {
            throw new ViteException("Unable to locate file from Vite manifest: {$path}.");
        }

        return file_get_contents($path);
    }

    /**
     * 为应用程序生成资产路径。
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    protected function assetPath($path, $secure = null)
    {
        return ($this->assetPathResolver ?? asset(...))($path, $secure);
    }

    /**
     * 获取给定构建目录的清单文件。
     *
     * @param  string  $buildDirectory
     * @return array
     *
     * @throws \Illuminate\Foundation\ViteManifestNotFoundException
     */
    protected function manifest($buildDirectory)
    {
        $path = $this->manifestPath($buildDirectory);

        if (! isset(static::$manifests[$path])) {
            if (! is_file($path)) {
                throw new ViteManifestNotFoundException("未在以下位置找到Vite清单： $path");
            }

            static::$manifests[$path] = json_decode(file_get_contents($path), true);
        }

        return static::$manifests[$path];
    }

    /**
     * 获取给定构建目录的清单文件路径。
     *
     * @param  string  $buildDirectory
     * @return string
     */
    protected function manifestPath($buildDirectory)
    {
        return public_path($buildDirectory . '/' . $this->manifestFilename);
    }

    /**
     * 获取当前清单的唯一哈希值，如果没有清单则返回 null。
     *
     * @param  string|null  $buildDirectory
     * @return string|null
     */
    public function manifestHash($buildDirectory = null)
    {
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return null;
        }

        if (! is_file($path = $this->manifestPath($buildDirectory))) {
            return null;
        }

        return md5_file($path) ?: null;
    }

    /**
     * 获取给定入口点 / 资产的 chunk。
     *
     * @param  array  $manifest
     * @param  string  $file
     * @return array
     *
     * @throws \Illuminate\Foundation\ViteException
     */
    protected function chunk($manifest, $file)
    {
        if (! isset($manifest[$file])) {
            throw new ViteException("Unable to locate file in Vite manifest: {$file}.");
        }

        return $manifest[$file];
    }

    /**
     * 获取预先获取脚本标签的 nonce 属性。
     *
     * @return \Illuminate\Support\HtmlString
     */
    protected function nonceAttribute()
    {
        if ($this->cspNonce() === null) {
            return new HtmlString('');
        }

        return new HtmlString(' nonce="' . $this->cspNonce() . '"');
    }

    /**
     * 确定 HMR 服务器是否正在运行。
     *
     * @return bool
     */
    public function isRunningHot()
    {
        return is_file($this->hotFile());
    }

    /**
     * 将 Vite 标签内容作为 HTML 字符串获取。
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->__invoke($this->entryPoints)->toHtml();
    }
}
