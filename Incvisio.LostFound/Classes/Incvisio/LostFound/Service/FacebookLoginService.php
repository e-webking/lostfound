<?php
namespace Incvisio\LostFound\Service;
/*                                                                        *
 * This script belongs to the FLOW3 package "Incvisio.LostFound".         *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;
use Incvisio\LostFound\Service\AbstractConnectorService;


class FacebookLoginService extends AbstractConnectorService{

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Facebook.app_id")
     */
    protected $app_id;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Facebook.app_secret")
     */
    protected $app_secret;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Facebook.redirect_uri")
     */
    protected $redirect_uri;

    /**
     * @return string
     */
    public function getAuthorizationUri(){
        return "https://www.facebook.com/v2.2/dialog/oauth?response_type=code&client_id=". $this->app_id ."&redirect_uri=".$this->redirect_uri."&scope=read_insights%2C+manage_pages";
    }

    /**
     * @return string
     */
    public function getAppId(){
        return $this->app_id;
    }

    /**
     * @return string
     */
    public function getRedirectUri(){
        return $this->redirect_uri;
    }

    /**
     * @param string $code
     * @return array
     */
    public function getToken($code){
        $uri =  'https://graph.facebook.com/oauth/access_token?'.
            'client_id='. $this->app_id .
            '&redirect_uri=' . $this->redirect_uri .
            '&client_secret=' . $this->app_secret .
            '&code=' . $code;
        $token = $this->processUri($uri)->getContent();
        $error = json_decode($token);
        // @TODO better error checking
        if ($error !== NULL){
            $response['error'] = $error->error->message;
        }else{
            parse_str($token,$response);
        }
        return $response;
    }

    /**
     * @param string $token
     * @return array
     */
    public function getAccounts($token){
        $uri =  'https://graph.facebook.com/me/?fields=first_name,last_name,email'.
            '&access_token=' . $token;
        $response = file_get_contents($uri);
        $accounts = json_decode($response,true);

        if ((isset($accounts['error']))){
            return NULL;
        }else{
            $accounts = $accounts;
        }
        return $accounts;
    }
}