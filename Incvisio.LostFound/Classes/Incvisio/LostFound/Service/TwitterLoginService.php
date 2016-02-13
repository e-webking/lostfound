<?php
namespace Incvisio\LostFound\Service;
/*                                                                        *
 * This script belongs to the FLOW3 package.            *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;
use Incvisio\LostFound\Service\AbstractConnectorService;

/**
 * Twitter Connector
 *
 */

class TwitterLoginService extends AbstractConnectorService{

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Session\SessionInterface
     */
    protected $session;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Twitter.app_id")
     */
    protected $app_key;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Twitter.app_secret")
     */
    protected $app_secret;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Twitter.redirect_uri")
     */
    protected $redirect_uri;


    /**
     * @return string
     */
    public function getAuthorizationUri(){
        $connection = new \Abraham\TwitterOAuth\TwitterOAuth($this->app_key, $this->app_secret);
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $this->redirect_uri));
        $this->session->putData('oauth_token',$request_token['oauth_token']);
        $this->session->putData('oauth_token_secret',$request_token['oauth_token_secret']);
        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        return $url;
    }

    /**
     * @return string
     */
    public function getAppKey(){
        return $this->app_key;
    }

    /**
     * @return string
     */
    public function getRedirectUri(){
        return $this->redirect_uri;
    }

    /**
     * @param string $oauth_verifier
     * @return array
     */
    public function getToken($oauth_verifier){
        $connection = new \Abraham\TwitterOAuth\TwitterOAuth($this->app_key, $this->app_secret,$this->session->getData('oauth_token'),$this->session->getData('oauth_token_secret'));
        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $oauth_verifier));
        $connection = new \Abraham\TwitterOAuth\TwitterOAuth($this->app_key, $this->app_secret,$access_token['oauth_token'],$access_token['oauth_token_secret']);
        $credentials = $connection->get('/account/verify_credentials',['include_entities' => true, 'skip_status' => true, 'include_email' => true]);
        $access_token['since_id'] = $credentials;
        return $access_token;
    }
}