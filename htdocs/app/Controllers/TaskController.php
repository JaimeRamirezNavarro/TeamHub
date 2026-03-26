<?php

class TaskController {

    private $tasks;

    public function __construct() {
        $this->tasks = new TaskModel();
    }

    public function obtenerTodas() {
        return $this->tasks->obtenerTodas();
    }
}
