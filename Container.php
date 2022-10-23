<?php

namespace Blog;

class Container
{
    private array $objects = [];

    public function has(string $class_name) : bool
    {
        return isset($this->objects[$class_name]) || class_exists($class_name);
    }

    public function get(string $class_name) : object
    {
        return isset($this->objects[$class_name]) ?
                     $this->objects[$class_name]() :
                     $this->create_object($class_name);
    }

    private function create_object(string $class_name): object
    {
        $reflector_class = new \ReflectionClass($class_name);

        $reflector_constructor = $reflector_class->getConstructor();
        if (empty($reflector_constructor)) {
            return $this->objects[$class_name] = new $class_name;
        }

        $constructor_args = $reflector_constructor->getParameters();
        if (empty($constructor_args)) {
            return $this->objects[$class_name] = new $class_name;
        }

        $arguments = [];
        foreach ($constructor_args as $argument) {
            $arguments[] = $this->get($argument->getType()->getName());
        }

        return $this->objects[$class_name] = new $class_name(...$arguments);
    }
}