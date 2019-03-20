<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;

trait Filters
{
    protected $filters = [];

    /**
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

    protected function findFilter(array $names) {
        foreach ($names as $name) {
            if (isset($this->filters[$name])) {
                return $this->filters[$name];
            }

            $class = __NAMESPACE__."\\With".$name;

            if (class_exists($class)) {
                $this->filters[$name] = $filter = new $class;
                return $filter;
            }
        }

        return false;
    }
}