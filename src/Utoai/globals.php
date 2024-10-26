<?php

if (! function_exists('asset')) {
    function asset(string $asset)
    {
        return Utoai\asset($asset);
    }
}

if (! function_exists('view')) {
    function view()
    {
        return Utoai\view(...func_get_args());
    }
}

if (! function_exists('get_theme_file_path')) {
    function get_theme_file_path($file = '')
    {
        // 获取当前主题的目录（Typecho 中使用 Helper::options()->themeUrl）
        $themeDirectory = __TYPECHO_ROOT_DIR__ . '/usr/themes/FounBox/' . $file;

        // 构建文件路径
        return $themeDirectory;
    }
}






//----------------------------------
