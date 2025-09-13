<?php
require_once APP_PATH . '/Repositories/DinoModels.php';

class DinosaurioController {
    private $tipos = [
        "Azul"    => 'imgs/minis/azul.png',
        "Cyan"    => 'imgs/minis/cyan.png',
        "Naranja" => 'imgs/minis/naranja.png',
        "Rojo"    => 'imgs/minis/rojo.png',
        "Rosado"  => 'imgs/minis/rosa.png',
        "Verde"   => 'imgs/minis/verde.png',
    ];

    public function asignacion() {
        $bandeja = [];
        $id = 1;

        foreach ($this->tipos as $tipo => $rutaRelativa) {
            $imagen = asset($rutaRelativa); 
            for ($i = 0; $i < 8; $i++) {
                $bandeja[] = new Dinosaurio($id, $tipo, $imagen);
                $id++;
            }
        }

        return $bandeja;
    }
}