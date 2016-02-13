<?php
namespace Incvisio\LostFound\Service;
/*                                                                        *
 * This script belongs to the FLOW3 package "Incvisio.LostFound".            *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;
use Incvisio\LostFound\Service\AbstractConnectorService;


class VkLoginService extends AbstractConnectorService{

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Vk.app_id")
     */
    protected $app_id;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Vk.app_secret")
     */
    protected $app_secret;

    /**
     * @var string
     * @Flow\Inject(setting="strategies.Vk.redirect_uri")
     */
    protected $redirect_uri;

    /**
     * @return string
     */
    public function getAuthorizationUri(){
        return "http://oauth.vk.com/authorize?response_type=code&client_id=". $this->app_id ."&redirect_uri=".$this->redirect_uri."&scope=email ";
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
        $uri =  'https://oauth.vk.com/access_token?'.
            'client_id='. $this->app_id .
            '&redirect_uri=' . $this->redirect_uri .
            '&client_secret=' . $this->app_secret .
            '&code=' . $code;
        $token = $this->processUri($uri)->getContent();
        $error = json_decode($token);

        // @TODO better error checking
        if (!isset($error->access_token)){
            $response['error'] = $error->error_description;
        }else{
            $response=$token;
        }
        return $response;
    }

    /**
     * @param string $token
     * @param string $user_id
     * @return array
     */
    public function getAccounts($token,$user_id){
        $uri =  'https://api.vk.com/method/users.get?user_id='.$user_id.'&fields=uid,first_name,last_name,screen_name,sex,bdate,photo_big'.
            '&access_token=' . $token;
        $response = file_get_contents($uri);
        $accounts = json_decode($response,true);

        if (isset($accounts['error'])){
            return NULL;
        }else{
            $accounts = $accounts;
        }
        return $accounts;
    }
}