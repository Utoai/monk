<?php

namespace Utoai\Monk\FounBox\Concerns;

trait FiltersTemplates
{
    /**
     * 返回模板时使用已编译的 Blade 视图。
     *
     * 筛选: {type}_template_hierarchy
     *
     * @param  array  $files
     * @return string[] List of possible views
     */
    public function filterTemplateHierarchy($files)
    {
        echo '返回模板时使用已编译的 Blade 视图。';
        $templates = $this->sageFinder->locate($files);

        // 根据 Typecho 的逻辑处理
        return array_merge($templates, $files);
    }

    /**
     * 包括已编译的 Blade 视图并附加了数据。
     *
     * 筛选: template_include
     *
     * @param  string
     * @return string
     */
    public function filterTemplateInclude($file)
    {
        echo '包括已编译的 Blade 视图并附加了数据。 ';
        $view = $this->fileFinder->getPossibleViewNameFromPath(realpath($file));
        $view = trim($view, '\\/.');

        // 收集传递给视图的数据
        $data = [];
        // foreach (get_body_class() as $class) {
        //     $data = apply_filters("founbox/template/{$class}/data", $data, $view, $file);
        // }

        // 将视图和数据存储到 Typecho 的上下文中
        $this->app->instance('sage.view', $view);
        $this->app->instance('sage.data', $data);

        // 调试输出
        echo "视图: $view\n";
        echo "数据: " . print_r($data, true) . "\n";

        // return __TYPECHO_THEME_DIR__ . '/FounBox/index.php'; // Typecho 主题目录

        return 'D:\pwa\www\q.w\usr\themes\FounBox\index.php'; // Typecho 主题目录
    }

    /**
     * 为主题模板添加兼容性。
     *
     * 筛选: theme_templates
     *
     * @param array $templates
     * @param mixed $theme
     * @param mixed $post
     * @param string $postType
     * @return array 主题模板列表
     */
    public function filterThemeTemplates($templates, $theme, $post, $postType)
    {
        echo '为主题模板添加兼容性。';
        return array_unique(array_merge($templates, $this->getTemplates($postType)));
    }

    /**
     * 检测模板文件。
     *
     * @param string $postType
     * @return array
     */
    protected function getTemplates($postType = '')
    {
        echo '检测模板文件。';
        $templates = [];

        // 在 Typecho 的路径中查找模板文件
        $paths = $this->fileFinder->getPaths();

        foreach ($paths as $path) {
            foreach (glob("{$path}/*.php") as $file) {
                if (preg_match('|Template Name:(.*)$|mi', file_get_contents($file), $header)) {
                    $templates[$postType][] = [
                        'file' => $file,
                        'name' => trim($header[1])
                    ];
                }
            }
        }

        return $templates[$postType] ?? [];
    }
}
