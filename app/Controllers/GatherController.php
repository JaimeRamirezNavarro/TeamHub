<?php

class GatherController {

    private $gather;

    public function __construct() {
        $this->gather = new GatherModel();
    }

    public function vincular($team_id, $space_id, $space_url) {
        return $this->gather->vincular($team_id, $space_id, $space_url);
    }

    public function obtener($team_id) {
        return $this->gather->obtener($team_id);
    }

    public function activar($team_id) {
        return $this->gather->activar($team_id);
    }

    public function desactivar($team_id) {
        return $this->gather->desactivar($team_id);
    }
}
