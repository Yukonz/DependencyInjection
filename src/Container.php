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

    private function create_object(string $class_name) : object
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
            $item_name = $argument->getType()->getName();

            if (interface_exists($item_name)) {
                $available_classes = $this->list_classes_implementing_interface($item_name);

                //TODO: implement class switching logic
                $arguments[] = $this->get($available_classes[0]);
            } else {
                $arguments[] = $this->get($item_name);
            }
        }

        return $this->objects[$class_name] = new $class_name(...$arguments);
    }

    private function list_classes_implementing_interface(string $interface_name) : array
    {
        $classes_list = [];

        foreach (get_declared_classes() as $class_name) {
            if (in_array($interface_name, class_implements($class_name))) {
                $classes_list[] = $class_name;
            }
        }

        return $classes_list;
    }
}