<?php

namespace Chiron\Views;

use Throwable;

class PhpRenderer implements \ArrayAccess
{
    /** @var string */
    private $templatePath;

    /** @var array */
    private $attributes = [];

    public function __construct(string $templatePath = '')
    {
        $this->setTemplatePath($templatePath);
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Set the template path.
     *
     * @param string $templatePath
     */
    public function setTemplatePath(string $templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';

        return $this;
    }

    public function setAttributes(array $attributes = [])
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    // mixed $value
    public function addAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key];
    }

    public function removeAttribute(string $key): self
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /********************************************************************************
     * Render template
     *******************************************************************************/

    public function render(string $templateFile, array $variables = [], string $templatePath = null): string
    {
        if (! isset($templatePath)) {
            $templatePath = $this->templatePath;
        }

        $template = $templatePath . $templateFile;

        if (! is_file($template)) {
            throw new \RuntimeException("Unable to render template : `$template` because this file does not exist");
        }

        $variables = array_merge($this->attributes, $variables);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            ob_start();
            call_user_func(function () {
                extract(func_get_arg(1), EXTR_OVERWRITE); // EXTR_SKIP
                include func_get_arg(0);
            }, $template, $variables);
            $content = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return $content;
    }

    /********************************************************************************
     * Security Helpers
     *******************************************************************************/

    /**
     * Escape HTML entities in a string.
     *
     * @param string $raw
     */
    private function e(string $raw): void
    {
        // change horizontal tab with 4 spaces
        $raw = str_replace(chr(9), '    ', $raw);
        // We take advantage of ENT_SUBSTITUTE flag to correctly deal with invalid UTF-8 sequences.
        echo htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     *   Remove HTML tags (except those enumerated) and non-printable
     *   characters to mitigate XSS/code injection attacks.
     *
     *   @param string $raw
     *   @param string $tags - tags allowed, separated by a commma, semi-colon or pipe character. EX : "b|i" or "<b>;<i>" or "b,<i>". use "*" to keep all the html tags
     **/
    private function scrub(string $raw, string $tags = null): void
    {
        // if tags = *, we don't remove the html tags in the string
        if ($tags != '*') {
            //Split comma-, semi-colon, or pipe-separated string tags
            $tags = array_map('trim', preg_split('/[,;|]/', $tags, 0, PREG_SPLIT_NO_EMPTY));
            $raw = trim(strip_tags($raw, '<' . implode('><', $tags) . '>'));
        }

        // remove non displayable chars -> remove control characters (ASCII characters 0 to 31) except for tabs and newlines
        echo trim(preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', '', $raw));
    }

    /********************************************************************************
     * ArrayAccess interface
     *******************************************************************************/

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Get collection item for key.
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     */
    public function offsetGet($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set collection item.
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->addAttribute($key, $value);
    }

    /**
     * Remove item from collection.
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        $this->removeAttribute($key);
    }

    /********************************************************************************
     * Magic methods
     *******************************************************************************/

    /**
     * Get a piece of data from the view.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->addAttribute($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __unset($key)
    {
        $this->removeAttribute($key);
    }
}
