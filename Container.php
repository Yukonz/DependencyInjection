<?php

namespace Blog;

class Container implements Psr\Container\ContainerInterface
{
    private array $objects = [];

    public function __construct()
    {
        $this->objects[Db::class] = new \Blog\Db();
        $this->objects[PostsStorage::class] = new \Blog\PostsStorage($this->get('db'));
        $this->objects[PostsController::class] = new \Blog\PostsController($this->get('posts_repository'));
    }

    public function has(string $class_name) : bool
    {
        return isset($this->objects[$class_name]) || class_exists($class_name);
    }

    public function get(string $class_name) : mixed
    {
        return isset($this->objects[$class_name]) ? $this->objects[$class_name]() : $this->create_object($class_name);
    }

    private function create_object(string $class_name): object
    {
        $reflector_class = new \ReflectionClass($class_name);

        $reflector_constructor = $reflector_class->getConstructor();
        if (empty($reflector_constructor)) {
            return new $class_name;
        }

        $constructor_args = $reflector_constructor->getParameters();
        if (empty($constructor_args)) {
            return new $class_name;
        }

        $arguments = [];
        foreach ($constructor_args as $argument) {
            $argumentType = $argument->getType()->getName();
            $arguments[$argument->getName()] = $this->get($argumentType);
        }

        return new $class_name(...$arguments);
    }
}