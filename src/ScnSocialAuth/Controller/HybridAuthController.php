<?php
namespace ScnSocialAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class HybridAuthController extends AbstractActionController
{
    public function indexAction()
    {
        \Hybrid_Endpoint::process();
    }

    // Used for providers which donot allow querystring as redirect_uri (e.g windows live)
    public function doneAction()
    {
        $provider =  $this->params('provider');

   		 // If querystring is appended in the url by the provider
        if(strrpos( $_SERVER["QUERY_STRING"], '?' ) )
            parse_str( $_SERVER["QUERY_STRING"], $_REQUEST );

        // Check if the provider is set and
        if(isset($provider))
            $_REQUEST["hauth_done"] = $provider;

        \Hybrid_Endpoint::process($_REQUEST);
    }
}
