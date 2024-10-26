<?php

namespace Utoai\Monk\View\Composers\Concerns;

use BadMethodCallException;
use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * 缓存过期
     *
     * 如果未指定过期时间，则值将永久缓存。
     *
     * @var int|float
     */
    protected $cache_expiration;

    /**
     * Cache key
     *
     * 如果未指定 key，则 key 将默认为类名和帖子 ID
     *
     * @var string
     */
    protected $cache_key;

    /**
     * 缓存标签
     *
     * 如果未指定标签，则标签将为类名、文章 ID 和文章类型
     *
     * @var string[]
     */
    protected $cache_tags;

    /**
     * 缓存助手
     *
     * @param  dynamic  key|key,value|key-values|null
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function cache()
    {

        $archive = \Typecho\Widget::widget('\Widget\Archive');
        static $cache;

        $arguments = func_get_args();
        $tags = $this->cache_tags ?? [static::class, 'post-' . $archive->cid() . $archive->type()];

        if (! $cache) {
            try {
                $cache = Cache::tags($tags);
            } catch (BadMethodCallException $error) {
                $cache = cache();
            }
        }

        if (empty($arguments)) {
            return $cache;
        }

        if (! is_string($values = $arguments[0])) {
            $data = [];

            foreach ($values as $key => $value) {
                $data[$key] = $this->cache($key, $value);
            }

            return $data;
        }

        if (! isset($arguments[1])) {
            return $cache->get($arguments[0]);
        }

        $key = $arguments[0];
        $value = $arguments[1];

        if (! is_callable($value)) {
            throw new BadMethodCallException('Cache value should be callable');
        }

        if (! $expires = $this->cache_expiration) {
            return $cache->rememberForever($key, $value);
        }

        return $cache->remember($key, $expires, $value);
    }

    /**
     * 忘记缓存数据
     *
     * @param  string  $key
     * @return void
     */
    protected function forget($key = null)
    {
        return $this->cache()->forget($key ?? static::class . \Typecho\Widget::widget('\Widget\Archive')->cid());
    }

    /**
     * 刷新所有缓存数据
     *
     * 如果支持标签，则只会刷新标签
     *
     * @return void
     */
    protected function flush()
    {
        return $this->cache()->flush();
    }

    /**
     * 在渲染之前要合并并传递给视图的数据。
     *
     * @return array
     */
    protected function merge()
    {
        $key = $this->cache_key ?? hash('crc32b', static::class . serialize(
            collect($_SERVER)->only('HTTP_HOST', 'REQUEST_URI', 'QUERY_STRING', 'WP_HOME')->toArray()
        ));

        $with = $this->cache($key, function () {
            return $this->with();
        });

        return array_merge(
            $with,
            $this->view->getData(),
            $this->override()
        );
    }
}
