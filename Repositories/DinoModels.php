<?php
class Dinosaurio {
    public $id;
    public $imagen;
    public $tipo;

    public function __construct($id, $tipo, $imagen) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->imagen = $imagen;
    }
}