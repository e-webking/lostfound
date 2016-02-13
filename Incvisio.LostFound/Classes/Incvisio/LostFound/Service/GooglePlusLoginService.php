<?php
namespace Incvisio\LostFound\Service;
/*                                                                        *
 * This script belongs to the FLOW3 package "Incvisio.LostFound".            *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;
use Incvisio\LostFound\Service\AbstractConnectorService;


class GooglePlusLoginService extends AbstractConnectorService{

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Google.app_id")
     */
    protected $app_id;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Google.app_secret")
     */
    protected $app_secret;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Google.redirect_uri")
     */
    protected $redirect_uri;

    /**
     * @return string
     */
    public function getAuthorizationUri(){

        return "https://accounts.google.com/o/oauth2/auth?".
        "scope=profile email&".
        "redirect_uri=".$this->redirect_uri."&".
        "response_type=code&".
        "client_id=". $this->app_id ."&approval_prompt=force";

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

        $uri =  'https://www.googleapis.com/oauth2/v3/token?grant_type=authorization_code'.
            '&code='. $code .
            '&redirect_uri='. $this->redirect_uri .
            '&client_id='. $this->app_id .
            '&client_secret=' . $this->app_secret;
        $token = $this->processUriPOST($uri)->getContent();

        $tokenDecoded = json_decode($token);
        $response = $tokenDecoded;

        return $response;
    }

    /**
     * @param string $token
     * @return array
     */
    public function getAccounts($token){
        $uri =  'https://www.googleapis.com/plus/v1/people/me?alt=json&access_token='.$token;
        $response = $this->processUri($uri)->getContent();
        $accounts = json_decode($response,true);
        return $response = $accounts;

    }
}