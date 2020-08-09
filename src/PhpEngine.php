<?php

declare(strict_types=1);

namespace Chiron\Views;

//https://github.com/yiisoft/view/blob/master/src/PhpTemplateRenderer.php
//https://github.com/yiisoft/yii-twig/blob/master/src/ViewRenderer.php

class PhpEngine
{
    public function render(string $sourceFile, array $variables = []): string
    {
        // It's to prevent common problems with paths associated with symlinks
        $sourceFile = realpath($sourceFile);

        if (! is_file($sourceFile)) {
            throw new \InvalidArgumentException("Unable to render template : `$sourceFile` because this file does not exist");
        }
        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            ob_start();
            call_user_func(function () {
                extract(func_get_arg(0), EXTR_OVERWRITE); // EXTR_SKIP
                include func_get_arg(1);
            }, $variables, $sourceFile);
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return $content;
    }
}
