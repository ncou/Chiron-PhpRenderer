<?php

namespace Chiron\Views;

class PhpRenderer implements \ArrayAccess
{
    /** @var string */
    private $templatePath;
    /** @var string */
    private $file;
    /** @var array */
    private $attributes = [];

    public function __construct($templatePath = '')
    {
        $this->setTemplatePath($templatePath);
    }

    /**
     * Get the template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Set the template path
     *
     * @param string $templatePath
     */
    public function setTemplatePath($templatePath)
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

    public function addAttribute($key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute($key)
    {
        return $this->attributes[$key];
    }

    public function removeAttribute($key): self
    {
        unset($this->attributes[$key]);
        return $this;
    }

    public function hasAttribute($key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /********************************************************************************
     * Render template
     *******************************************************************************/

    public function render(string $templateFile, array $__vars = [], string $templatePath = null) : string
    {
        if (!isset($templatePath)) {
            $templatePath = $this->templatePath;
        }
        // assigns to a class variable and not a local variable, to prevent naming collisions
        $this->file = $templatePath . $templateFile;
        // assigns to a double-underscored variable, to prevent naming collisions
        $__vars = array_merge($this->attributes, $__vars);
        
        // extract all assigned vars, but not 'this'.
        if (array_key_exists('this', $__vars)) {
            unset($__vars['this']);
        }
        extract($__vars, EXTR_OVERWRITE); // EXTR_SKIP
        unset($__vars); // remove $__vars from local scope
        
        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            ob_start();
            $includeReturn = include $this->file;
            $content = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        if ($includeReturn === false || empty($content)) {
            throw new \RuntimeException(sprintf(
                '%s: Unable to render template "%s"; file include failed',
                __METHOD__,
                $this->file
            ));
        }

        return $content;
    }

    /********************************************************************************
     * Security Helpers
     *******************************************************************************/

    /**
     * Escape HTML entities in a string.
     *
     * @param  string $value
     * @return void (echo the result)
     */
    private function e(string $value)
    {
        // We take advantage of ENT_SUBSTITUTE flag to correctly deal with invalid UTF-8 sequences.
        echo is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value;
    }

    /**
    *   Remove HTML tags (except those enumerated) and non-printable
    *   characters to mitigate XSS/code injection attacks
    *   @return void (echo the result)
    *   @param $val string
    *   @param $tags string - tags allowed, separated by a commma, semi-colon or pipe character. EX : "b|i" or "<b>;<i>" or "b,<i>". use "*" to keep all the html tags
    **/
    private function scrub(string $val, string $tags = null)
    {

        // if tags = *, we don't remove the html tags in the string
        if ($tags!='*') {
            //Split comma-, semi-colon, or pipe-separated string tags
            $tags = array_map('trim', preg_split('/[,;|]/', $tags, 0, PREG_SPLIT_NO_EMPTY));
            $val=trim(strip_tags($val, '<'.implode('><', $tags).'>'));
        }

        // remove non displayable chars -> remove control characters (ASCII characters 0 to 31) except for tabs and newlines
        echo trim(preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', '', $val));
    }

    /********************************************************************************
     * ArrayAccess interface
     *******************************************************************************/

    /**
     * Does this collection have a given key?
     *
     * @param  string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->hasAttribute($key);
    }
    /**
     * Get collection item for key
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
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->addAttribute($key, $value);
    }
    /**
     * Remove item from collection
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
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->addAttribute($key, $value);
    }
    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->hasAttribute($key);
    }
    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __unset($key)
    {
        $this->removeAttribute($key);
    }
}