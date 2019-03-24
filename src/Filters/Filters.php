<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;

trait Filters
{
    protected $filters = [];
    protected static $namespaces = [__NAMESPACE__];

    /**
     * Add a namespace to look through when dynamically looking for filters.
     *
     * @param string $namespace
     */
    public static function addNamespace(string $namespace)
    {
        if (!in_array($namespace, self::$namespaces)) {
            self::$namespaces[] = $namespace;
        }
    }

    /**
     * Determine if the method called is a filter, a.k.a. starts with "with".
     *
     * @param $name
     * @return bool|With
     */
    protected function isFilter($name)
    {
        $parts = preg_split('/(?=[A-Z])/',$name);
        if ($parts[0] == 'with') {
            return $this->findFilter([$parts[1], rtrim($parts[1], 's')]);
        }

        return false;
    }

    /**
     * Iterate through all namespaces to find a matching class.
     *
     * @param array $names
     * @return bool
     */
    protected function findFilter(array $names) {
        foreach ($names as $name) {
            if (isset($this->filters[$name])) {
                return $this->filters[$name];
            }

            foreach (self::$namespaces as $namespace) {
                $class = rtrim($namespace, '\\'). "\\With" . $name;

                if (class_exists($class)) {
                    $this->filters[$name] = $filter = new $class;
                    return $filter;
                }
            }
        }

        return false;
    }
}