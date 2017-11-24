<?php

namespace Kreait\Firebase\Database\Query\Sorter;

use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

final class OrderByChild implements Sorter
{
    use ModifierTrait;

    private $childKey;

    public function __construct(string $childKey)
    {
        $this->childKey = $childKey;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'orderBy', sprintf('"%s"', $this->childKey));
    }

    public function modifyValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        
        $childKey = $this->childKey;
        
        uasort($value, function ($a, $b) use ($childKey) {
            return ($this->get($a, $childKey) ?? null) <=> $this->get($b, $childKey) ?? null;
        });
        
        return $value;
    }

    /**
     * Function taken from Mohamed Meabed repo:
     * https://github.com/tajawal/lodash-php/blob/master/src/collections/get.php
     * 
     * Get item of an array by index , aceepting nested index
     *
     ** __::get(['foo' => ['bar' => 'ter']], 'foo/bar');
     ** // → 'ter'
     *
     * @param array  $collection array of values
     * @param string $key        key or index
     * @param null   $default    default value to return if index not exist
     *
     * @return array|mixed|null
     *
     */
    function get($collection = [], $key = '', $default = null)
    {
        if (is_null($key)) {
            return $collection;
        }

        if (!is_object($collection) && isset($collection[$key])) {
            return $collection[$key];
        }

        foreach (explode('/', $key) as $segment) {
            if (is_object($collection)) {
                if (!isset($collection->{$segment})) {
                    return $default instanceof \Closure ? $default() : $default;
                } else {
                    $collection = $collection->{$segment};
                }
            } else {
                if (!isset($collection[$segment])) {
                    return $default instanceof \Closure ? $default() : $default;
                } else {
                    $collection = $collection[$segment];
                }
            }
        }

        return $collection;
    }
}
