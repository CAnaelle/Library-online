<?php

class InfosController
{
    public function displayInfos()
    {
        $titre="Informations pratiques";
        $template = "others/infos";
        //appel au layout
        require "views/layout.phtml";
    }
    
}

?>