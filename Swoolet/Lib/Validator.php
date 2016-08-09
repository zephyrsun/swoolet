<?php

/**
 * Validator
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Swoolet\Lib;


/**
 * Class Validator
 *
 * $result = (new Validator($_POST))
 *            ->length('username', 6, 20)
 *            ->email('abc@examle.com')
 *            ->getResult();
 *
 * @package Swoolet\Lib
 */
class Validator
{
    public $raw = [];

    public $result = [];

    private $err = [];

    public function __construct($raw)
    {
        $this->raw = $raw;
    }

    public function &get($key)
    {
        return $this->raw[$key];
    }

    /**
     * @param $name
     * @param $args
     * @return $this
     */
    public function __call($name, $args)
    {
        $this->err[] = "rule error: $name";

        return $this;
    }

    /**
     * not null
     *
     * @param $key
     * @param bool $required
     * @return Validator
     */
    public function required($key, $required = true)
    {
        $val = $this->get($key);
        $result = $val !== null;

        return $this->checkResult($key, $val, $result, $required);
    }

    public function mobileNumberCN($key, $required = true)
    {
        $val = $this->get($key);
        $result = strlen($val) == 11 && $val{0} == 1;

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * email address
     *
     * @param string $key
     * @param bool $required
     * @return $this
     */
    public function email($key, $required = true)
    {
        $val = $this->get($key);
        $result = filter_var($val, FILTER_VALIDATE_EMAIL);

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * IPv4 or Ipv6 address
     *
     * @param string $key
     * @param bool $required
     * @return $this
     */
    public function ip($key, $required = true)
    {
        $val = $this->get($key);
        $result = filter_var($val, FILTER_VALIDATE_IP);

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * url
     *
     * @param $key
     * @param bool $required
     * @return $this
     */
    public function url($key, $required = true)
    {
        $val = $this->get($key);
        $result = filter_var($val, FILTER_VALIDATE_URL);

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * not empty
     *
     * @param $key
     * @param bool $required
     * @return $this
     */
    public function notEmpty($key, $required = true)
    {
        $val = $this->get($key);
        $result = empty($val);

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * must match equal
     *
     * @param $key
     * @param $ref
     * @param bool $required
     * @return $this
     */
    public function equal($key, $ref, $required = true)
    {
        $val = $this->get($key);
        $result = $val === $ref;

        return $this->checkResult($key, $val, $result, $required);

    }

    /**
     * must not match equal
     *
     * @param $key
     * @param $ref
     * @param bool $required
     * @return $this
     */
    public function unequal($key, $ref, $required = true)
    {
        $val = $this->get($key);
        $result = $val !== $ref;

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * is numeric
     *
     * @param $key
     * @param bool $required
     * @return $this
     */
    public function num($key, $required = true)
    {
        $val = $this->get($key);
        $result = \is_numeric($val);

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * regex match
     *
     * @param $key
     * @param $regex
     * @param bool $required
     * @return $this
     */
    public function match($key, $regex, $required = true)
    {
        $val = $this->get($key);
        $result = \preg_match($regex, $val);

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * Less than or Equivalent with
     *
     * @param $key
     * @param $ref
     * @param bool $required
     * @param bool $length
     * @return $this
     */
    public function le($key, $ref, $required = true, $length = false)
    {
        $val = $this->get($key);

        if ($length)
            $val = \mb_strlen($val);

        $result = is_numeric($val) && $val <= $ref;

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * Greater than or Equivalent with
     *
     * @param $key
     * @param $ref
     * @param bool $required
     * @param bool $length
     * @return $this
     */
    public function ge($key, $ref, $required = true, $length = false)
    {
        $val = $this->get($key);

        if ($length)
            $val = \mb_strlen($val);

        $result = is_numeric($val) && $val >= $ref;

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * range of number
     *
     * @param $key
     * @param $min
     * @param $max
     * @param bool $required
     * @return $this
     */
    public function between($key, $min, $max, $required = true, $length = false)
    {
        $val = $this->get($key);

        if ($length)
            $val = \mb_strlen($val);

        $result = is_numeric($val) && $val >= $min && $val <= $max;

        return $this->checkResult($key, $val, $result, $required);
    }

    /**
     * Less than or Equivalent with length
     *
     * @param $key
     * @param $max
     * @param bool $required
     * @return $this
     */
    public function lengthLE($key, $max, $required = true)
    {
        return $this->le($key, $max, $required, true);
    }

    /**
     * Greater than or Equivalent with length
     *
     * @param $key
     * @param $min
     * @param bool $required
     * @return $this
     */
    public function lengthGE($key, $min, $required = true)
    {
        return $this->ge($key, $min, $required, true);
    }

    /**
     * range of length
     *
     * @param $key ;
     * @param $min
     * @param $max
     * @param bool $required
     * @return $this
     */
    public function length($key, $min, $max, $required = true)
    {
        return $this->between($key, $min, $max, $required, true);
    }

    /**
     * @param $key
     * @param $required
     * @return $this
     */
    protected function checkResult($key, $val, $result, $required)
    {
        if ($val && !$result)
            $this->err[] = $key;
        elseif ($required && $val === null)
            $this->err[] = $key;
        else
            $this->result[$key] = $val;

        return $this;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        if ($this->getError())
            return [];

        return $this->result;
    }

    /**
     * @return array
     */
    public function getError()
    {
        return $this->err;
    }

    /**
     * @return mixed
     */
    public function getFirstError()
    {
        return \current($this->err);
    }

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return \end($this->err);
    }
}