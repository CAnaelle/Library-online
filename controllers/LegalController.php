<?php

class LegalController
{
    public function displayMentions()
    {
        $titre="Mentions légales";
        $template = "others/mentions";
        require "views/layout.phtml";
    }
    
    public function displayPrivacyPolicies()
    {
        $titre="Politiques de confidentialités";
        $template = "others/privacyPolicies";
        require "views/layout.phtml";
    }
}

?>