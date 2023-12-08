<?php

namespace App\Controller\Trait;

trait ControllerToolsTrait {

    protected function checkEntityExistence(mixed $var, int $id){
        if (!$var){
            throw $this->createNotFoundException(
                'No entity found for id '.$id
            );
        }
    }

}