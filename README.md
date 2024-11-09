
# 基于`roots/acorn`修改
- Acorn 是一个用于将 Laravel 生态集成到 WordPress 中的框架

## `roots/acorn`只适配Word Press，所以，我决定自己修改一个适配`Typecho`的版本，并且会持续更新

## !! 忽然发现继续微改,可以支持集成到大部分传统php环境中的

## Monk 利用 Laravel 生态系统
 Typecho 主题的 Laravel Blade模板，Laravel 组件

## 使用方法
- 主题/插件
```
composer i
```
- 启动程序
```
Application::configure($basePath = null)
    ->withPaths(
        // 自定义路径
        '..\root\app',           // 'app' 路径
        '..\root\config',         // 'config' 路径
    )
    ->withProviders([
        // 自定义服务
        App\Providers\ThemeServiceProvider::class,
    ])
    ->withRouting(
        // 添加自定义路由，无需使用typecho添加。
        // web: 'D:\pwa\www\w.w\wp-content\themes\root\routes\web.php',
    )
    ->boot();
```
