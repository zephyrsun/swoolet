<?php

/**
 * Basic View
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Swoolet\View;

use Swoolet\App;
use Swoolet\Result;

class Basic extends Result
{
    public $options = [
        'source_dir' => '',
        'source_ext' => 'php',
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options + App::getConfig('view') + $this->options;

        if (!$this->options['source_dir'])
            $this->options['source_dir'] = \BASE_DIR . App::$server->namespace . DIRECTORY_SEPARATOR . 'View';
    }

    /**
     * @param $name
     * @param string $ext
     */
    public function render($name, $ext = '')
    {
        $name = $this->getSourceFile($name, $ext);

        \extract($this->get(), EXTR_SKIP);

        include $name;
    }

    /**
     * @param $name
     * @param null $ext
     * @return string
     */
    public function fetch($name, $ext = null)
    {
        \ob_start();
        $this->render($name, $ext);
        return \ob_get_clean();
    }

    /**
     * @param $key
     * @param null $val
     * @return $this
     */
    public function assign($key, $val = null)
    {
        parent::set($key, $val);

        return $this;
    }

    /**
     * @param $name
     * @param $ext
     * @return string
     * @throws \Exception
     */
    public function getSourceFile($name, $ext)
    {
        if (!$ext)
            $ext = $this->options['source_ext'];

        $name = $this->options['source_dir'] . \DIRECTORY_SEPARATOR . $name . '.' . $ext;

        if (\is_file($name))
            return $name;

        throw new \Exception("View file '$name' not found");
    }
}