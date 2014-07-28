<?php
namespace ScnSocialAuth\HybridAuth\Provider;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

/**
 * This is simply to trigger autoloading as a hack for poor design in HybridAuth.
 */

class Live extends \Hybrid_Providers_Live
{
    //Override the default login behavior of the hybridauth which passes the redirect_uri as querystring  and
    //creates possible mismatch with the registerd redirect_url in the providers api settings
    // E.g. Windows Live doesnot allow querystring ?hauth.done=Live is invalid for Windows Live, therefore the redirect_uri is changed to /hauth/done/live
    public function loginBegin()
    {
        # get hybridauth base url
        if (empty(\Hybrid_Auth::$config["base_url"])) {
            // the base url wasn't provide, so we must use the current
            // url (which makes sense actually)
            $url  = empty($_SERVER['HTTPS']) ? 'http' : 'https';
            $url .= '://' . $_SERVER['HTTP_HOST'];
            $url .= $_SERVER['REQUEST_URI'];
            $HYBRID_AUTH_URL_BASE = $url;
        } else {
            $HYBRID_AUTH_URL_BASE = \Hybrid_Auth::$config["base_url"];
        }

        $params["scope"]         = $this->scope;

        $this->api->redirect_uri = $HYBRID_AUTH_URL_BASE . ( strpos( $HYBRID_AUTH_URL_BASE, '?' ) ? '&' : '/' ) . "done/live" ;

        \Hybrid_Auth::storage()->set( "hauth_session.live.hauth_endpoint" , $this->api->redirect_uri  );

        $provider_params = \Hybrid_Auth::storage()->get( "hauth_session.live.id_provider_params");
        $provider_params['login_done'] = $this->api->redirect_uri ;

        \Hybrid_Auth::storage()->set( "hauth_session.live.id_provider_params" , $provider_params );

        $this->getEventManager()->trigger('beforeLiveLogin', $this, array('api' => $this->api,'params' => &$params, 'provider_params' => $provider_params));

        // redirect the user to the provider authentication url
        \Hybrid_Auth::redirect( $this->api->authorizeUrl($params ) );
    }

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
}
