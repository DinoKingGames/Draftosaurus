<?php
require_once __DIR__ . '/../Repositories/DinoModels.php';

class DinosaurioController {

    private $tipos = [
        "Azul"    => "Client/imgs/minis/Azul.png",
        "Cyan"    => 'Client/imgs/minis/Cyan.png',
        "Naranja" => 'Client/imgs/minis/Naranja.png',
        "Rojo"    => "Client/imgs/minis/Rojo.png",
        "Rosado"  => "Client/imgs/minis/Rosa.png",
        "Verde"   => "Client/imgs/minis/Verde.png"
    ];

    public function asignacion() {
        $bandeja = [];
        $id = 1;

        foreach ($this->tipos as $tipo => $imagen) {
            for ($i = 0; $i < 8; $i++) {
                $bandeja[] = new Dinosaurio($id, $tipo, $imagen);
                $id++;
            }
        }

        return $bandeja;
    }
}
