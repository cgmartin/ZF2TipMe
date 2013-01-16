<?php
/**
 * ZF2TipMe
 *
 * @link      http://github.com/cgmartin/ZF2TipMe for the canonical source repository
 * @copyright Copyright (c) 2013 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/ZF2TipMe/master/LICENSE
 */

namespace ZF2TipMe;

use Traversable;
use Zend\Filter\AbstractFilter;

class SubstrFilter extends AbstractFilter
{
    /**
     * @var array
     */
    protected $options = array(
        'offset'   => 0,
        'length'   => null,
        'encoding' => null,
    );

    /**
     * Sets filter options
     *
     * @param  int|array|Traversable $lengthOrOptions
     */
    public function __construct($lengthOrOptions = null)
    {
        if ($lengthOrOptions !== null) {
            if (!is_array($lengthOrOptions)
                && !$lengthOrOptions  instanceof Traversable)
            {
                $this->setLength($lengthOrOptions);
            } else {
                $this->setOptions($lengthOrOptions);
            }
        }
    }

    /**
     * Sets the length option
     *
     * @param  int $length
     * @return SubstrFilter Provides a fluent interface
     */
    public function setLength($length)
    {
        $this->options['length'] = $length;
        return $this;
    }

    /**
     * Returns the length option
     *
     * @return int|null
     */
    public function getLength()
    {
        return $this->options['length'];
    }

    /**
     * Sets the offset option
     *
     * @param  int $offset
     * @return SubstrFilter Provides a fluent interface
     */
    public function setOffset($offset)
    {
        $this->options['offset'] = $offset;
        return $this;
    }

    /**
     * Returns the offset option
     *
     * @return int|null
     */
    public function getOffset()
    {
        return $this->options['offset'];
    }

    /**
     * Sets the encoding option
     *
     * @param  int $offset
     * @return SubstrFilter Provides a fluent interface
     */
    public function setEncoding($encoding)
    {
        $this->options['encoding'] = $encoding;
        return $this;
    }

    /**
     * Returns the encoding option
     *
     * @return string|null
     */
    public function getEncoding()
    {
        return $this->options['encoding'];
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns the string $value with characters truncated from the beginning and end
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        // Do not filter non-string values
        if (!is_string($value)) {
            return $value;
        }

        $length   = (null !== $this->options['length'])   ? $this->options['length']   : mb_strlen($value);
        $encoding = (null !== $this->options['encoding']) ? $this->options['encoding'] : mb_internal_encoding();

        return mb_substr($value, $this->getOffset(), $length, $encoding);
    }
}
