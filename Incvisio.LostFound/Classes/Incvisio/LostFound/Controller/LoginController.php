<?php
namespace Incvisio\LostFound\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\SwiftMailer\Message as SwiftMessage;
class LoginController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/*
    * @var string
    */
	protected $providerName = 'DefaultProvider';
    /**
     * @var \TYPO3\Flow\Utility\Now
     * @Flow\Inject
     */

    protected $time;

    /**
     * @var \TYPO3\Flow\Security\Cryptography\HashService
     * @Flow\Inject
     */
    protected $hashService;
    /**
     * @var \TYPO3\Flow\Utility\Algorithms
     * @Flow\Inject
     */
    protected $algorithms;
	/**
	 * @var $settings
	 * @Flow\Inject
	 */
	protected $settings;
	/**
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 * @Flow\Inject
	 */
	protected $authenticationManager;

	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 * @var \TYPO3\Flow\Security\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var \Incvisio\LostFound\Factory\UserFactory
	 * @Flow\Inject
	 */
	protected $userFactory;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

    /**
     * A standalone template view
     *
     * @Flow\Inject
     * @var \TYPO3\Fluid\View\StandaloneView
     */
    protected $standaloneView;
	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Service\FacebookLoginService
	 */
	protected $facebookService;

	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Service\GooglePlusLoginService
	 */
	protected $googlePlusService;

	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Service\TwitterLoginService
	 */
	protected $twitterService;

	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Service\VkLoginService
	 */
	protected $vkService;

	/**
	 * @var \TYPO3\Flow\I18n\Translator
	 * @Flow\Inject
	 */
	public $translator;

	/**
	 * @var \TYPO3\Flow\I18n\Service
	 * @Flow\Inject
	 */
	public $translatorService;

	/**
	 * index action, does only display the form
	 */
	public function indexAction() {
		$account = $this->securityContext->getAccount();
		$facebook_appid = $this->facebookService->getAppId();
		$facebook_redirecturi = $this->facebookService->getRedirectUri();
		$vkLoginUrl = $this->vkService->getAuthorizationUri();
		$this->view->assignMultiple(array(
			'vk_url' => $vkLoginUrl,
			'twitter_url' => $this->twitterService->getAuthorizationUri(),
			'google_url' => $this->googlePlusService->getAuthorizationUri(),
			'facebook_appid' => $facebook_appid,
			'facebook_redirecturi' => $facebook_redirecturi
		));
	}
	/**
	 * @param string $social
	 * @return void
	 */
	public function socialAction($social = NULL) {
		$args = $this->request->getArguments();
		switch ($social){
			case "facebook":
				if ($args['connectorjson'] !== NULL){
					$response = json_decode($args['connectorjson'],true);
					$token = $response['accessToken'];
					$facebookAccounts = $this->facebookService->getAccounts($token);
					$facebookImage = "https:////graph.facebook.com/".$facebookAccounts['id']."/picture?type=large";
					$this->createSocialAccount(
						'facebook-'.$facebookAccounts['id'],
						$facebookAccounts['id'],
						$facebookAccounts['id'],
						$facebookAccounts['first_name'],
						$facebookAccounts['last_name'],
						$facebookImage,
						$facebookAccounts['email'],
						$network="facebook"
					);
				}else{
					$this->addFlashMessage("access_denied");
					$this->redirect('index', 'Standard');
				}
				break;
			case "vk":
				if (isset($args['code']) && $args['code'] !== NULL){
					$token = $this->vkService->getToken($args['code']);

					if(isset($token['error']) && $token['error']!=NULL){
						$this->addFlashMessage($token['error']);
						$this->redirect('index', 'Standard');
					}
					$data = json_decode($token);
					$vkAccounts = $this->vkService->getAccounts($data->access_token, $data->user_id);
					$this->createSocialAccount(
						'vk-'.$vkAccounts['response'][0]['uid'],
						$vkAccounts['response'][0]['uid'],
						$vkAccounts['response'][0]['uid'],
						$vkAccounts['response'][0]['first_name'],
						$vkAccounts['response'][0]['last_name'],
						$vkAccounts['response'][0]['photo_big'],
						$data->email,
						$network="vk"
					);
				}
				else{
					$this->addFlashMessage("access_denied");
					$this->redirect('index', 'Standard');
				}
				break;
			case "googleplus":
				if (isset($args['code']) && $args['code'] !== NULL){
					$token = $this->googlePlusService->getToken($args['code']);
					if(!isset($token->access_token)) {
						$this->addFlashMessage($token->error_description);
						$this->redirect('index', 'Standard');
					}
					$googleAccounts = $this->googlePlusService->getAccounts($token->access_token);
					$this->createSocialAccount(
						'googlePlus-'.$googleAccounts['id'],
						$googleAccounts['id'],
						$googleAccounts['id'],
						$googleAccounts['name']['familyName'],
						$googleAccounts['name']['givenName'],
						$googleAccounts['image']['url'],
						$googleAccounts['emails'][0]['value'],
						$network="googlePlus"
					);

				}else{
					$this->addFlashMessage("access_denied");
					$this->redirect('index', 'Standard');
				}
				break;
			case "twitter":
				if (isset($args['oauth_verifier']) && $args['oauth_verifier'] !== NULL){
					$response = $this->twitterService->getToken($args['oauth_verifier']);
					if($response !== NULL){
						$user_id = $response['user_id'];
						$fullName = explode(" ", $response['since_id']->name);
						$this->createSocialAccount(
							'twitter-'.$user_id,
							$user_id,
							$user_id,
							$fullName[0],
							$fullName[1],
							$response['since_id']->profile_image_url,
							'',
							$network="twitter"
						);
					}
				}else{
					$this->addFlashMessage('Something get wrong. Please, try again.',"Error",'Error');
					$this->redirect('index', 'Standard');
				}
				break;

		}

	}
	/**
	 * save the registration
	 * @param string $name
	 * @param string $pass
	 * @param string $pass2
	 * @param string $firstName
	 * @param string $lastName
	 * @param string $photo
	 * @param string $socialEmail
	 * @param string $socialNetwork
	 */
	public function createSocialAccount($name, $pass, $pass2,$firstName,$lastName,$photo=NULL,$socialEmail=NULL,$socialNetwork=NULL){
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($name,$this->providerName);
		if ($account instanceof \TYPO3\Flow\Security\Account) {
			if($account->getParty()->getSocialNetwork()==$socialNetwork){
				$tokens = $this->securityContext->getAuthenticationTokens();
				foreach ($tokens as $token) {
					$token->setAccount($account);
					$token->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
				}
				$this->flashMessageContainer->addMessage(
					new Message(
						$this->translator->translateById('login.login.success',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
					)
				);
				$this->redirect('index', 'Standard');
			}else {
				$this->flashMessageContainer->addMessage(
					new Message(
						$this->translator->translateById('login.login.usernameExist',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
					)
				);
				$this->redirect('index', 'Standard');
			}
		}else{
			$defaultRole = array('Incvisio.LostFound:User');

			if($name == '' || strlen($name) < 3) {
				$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Username too short or empty'));
				$this->redirect('register', 'Login');
			} else if($pass == '' || $pass != $pass2) {
				$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Password too short or does not match'));
				$this->redirect('register', 'Login');
			} else {

				// create a account with password an add it to the accountRepository
				$user = $this->userFactory->create($name,$pass,$firstName,$lastName,$defaultRole);
				$user->setSocialNetwork($socialNetwork);
				$user->setLikes(0);
				$user->setDislike(0);
				if($socialEmail!=NULL){
					$user->setSocialEmail($socialEmail);
				}
				if($photo!=NULL){
					$user->setSocialPhoto($photo);
				}
				$this->userRepository->add($user);
				$accounts = $user->getAccounts();
				foreach ($accounts as $account) {
					$this->accountRepository->add($account);
				}
				$this->persistenceManager->persistAll();

				$this->socialAuthenticateAction($name,$socialNetwork);

			}
			$this->flashMessageContainer->addMessage(
				new Message(
					$this->translator->translateById('login.registration.success.header',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
				)
			);
			// redirect to the login form
			$this->redirect('index', 'Standard');
		}
	}

	public function socialAuthenticateAction($name,$socialNetwork){
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($name,$this->providerName);
		if ($account instanceof \TYPO3\Flow\Security\Account) {
			if($account->getParty()->getSocialNetwork()==$socialNetwork){
				$tokens = $this->securityContext->getAuthenticationTokens();
				foreach ($tokens as $token) {
					$token->setAccount($account);
					$token->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
				}
				$this->flashMessageContainer->addMessage(
					new Message(
						$this->translator->translateById('login.login.success',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
					)
				);
				if ($this->request->getArgument('return_url') != '' && $this->request->hasArgument('return_url')) {
					$this->redirectToUri($this->request->getArgument('return_url'));
				} else {
					$this->redirect('index', 'Standard');
				}
				
			}else {
				$this->flashMessageContainer->addMessage(
					new Message(
						$this->translator->translateById('login.login.usernameExist',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
					)
				);
				$this->redirect('index', 'Standard');
			}
		}
	}
	/**
	 * @throws \TYPO3\Flow\Security\Exception\AuthenticationRequiredException
	 * @return void
	 */
	public function authenticateAction() {
		
		try {
			$this->authenticationManager->authenticate();
			$this->flashMessageContainer->addMessage(
				new Message(
					$this->translator->translateById('login.login.success',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
				)
			);
			if ($this->request->getArgument('return_url') != '' && $this->request->hasArgument('return_url')) {
				$this->redirectToUri($this->request->getArgument('return_url'));
			} else {
				$this->redirect('index', 'Standard');
			}
		} catch (\TYPO3\Flow\Security\Exception\AuthenticationRequiredException $exception) {
			$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error($this->translator->translateById('login.login.wrongPassword',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
			$this->redirect('index', 'Standard');
		}
	}

	/**
	 * @return void
	 */
	public function registerAction() {
		// do nothing more than display the register form
	}

	/**
	 * save the registration
	 * @param string $lemail
	 * @param string $lpass
	 * @param string $lpass2
     * @param string $firstName
     * @param string $lastName
	 */
	public function createAction($lemail, $lpass, $lpass2,$firstName,$lastName=NULL) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($lemail,$this->providerName);
		if ($account instanceof \TYPO3\Flow\Security\Account) {
			$this->flashMessageContainer->addMessage(
				new Message(
					$this->translator->translateById('login.login.usernameExist',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
				)
			);
			$this->redirect('index', 'Standard');
		}else{
		$defaultRole = array('Incvisio.LostFound:User');

        if($lemail == '' || strlen($lemail) < 3) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Username too short or empty'));
			$this->redirect('index', 'Standard');
        } else if($lpass == '' || $lpass != $lpass2) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Password too short or does not match'));
			$this->redirect('index', 'Standard');
        } else {
			if($lastName == NULL){
				$lastName = " ";
			}
            // create a account with password an add it to the accountRepository
			$user = $this->userFactory->create($lemail,$lpass,$firstName,$lastName,$defaultRole);
			$user->setLikes(0);
			$user->setDislike(0);
			$this->userRepository->add($user);
			$accounts = $user->getAccounts();
			foreach ($accounts as $account) {
				$account->setExpirationDate(new \TYPO3\Flow\Utility\Now());
				$this->accountRepository->add($account);
			}
			$this->persistenceManager->persistAll();
			$date = $this->time->getTimestamp();
			$random = rand();
			$json = json_encode(array(
				'username' => $lemail,
				'date' => $date,
				'random' => $random
			));
			$cryptKey = md5($this->providerName);
			$cryptJson = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $cryptKey, $json, MCRYPT_MODE_CBC, md5($cryptKey))));
			$this->sendActivationEmail($lemail, $cryptJson, $firstName);
            // add a message and redirect to the login form
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error($this->translator->translateById('main.messages.accountcreated',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
			$this->redirect('index', 'Standard');
        }

        // redirect to the login form
			$this->redirect('index', 'Standard');
		}
    }

	/**
	 * Sends for subscribe
	 *
	 * @param string $email
	 * @param string $cryptJson
	 * @param string $name
	 * @return bool
	 */
	public function sendActivationEmail($email,$cryptJson,$name) {

		$baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
		$requestLink = $baseUrl.'activateaccount?code='.$cryptJson;
		$templatepath =  'resource://Incvisio.LostFound/Private/Templates/Emails/ActivateAccount.html';
		$this->standaloneView->setFormat('html');
		$this->standaloneView->setTemplatePathAndFilename($templatepath);
		$this->standaloneView->assign('requestLink',$requestLink);
		$this->standaloneView->assign('username',$name);

		$emailBody = $this->standaloneView->render();

		$mail =  new \TYPO3\SwiftMailer\Message();
		$mail->setTo($email,$email)
			->setFrom('info@lostfound.com.ua')
			->setSubject('Welcome to LostFound')
			->setBody($emailBody, 'text/html')
			->send();

	}

	/*
     * @return string
     *
     */
	public function activateAccountAction(){
		$args = $this->request->getArguments();

		$activatelink = $args['code'];

		if ($activatelink != NULL) {
			$cryptJson = $activatelink;

			$cryptKey = md5($this->providerName);
			$uncryptJson = base64_decode($cryptJson);
			$uncryptJson = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$cryptKey, $uncryptJson, MCRYPT_MODE_CBC, md5( $cryptKey ));
			$uncryptJson = rtrim($uncryptJson, "\0");

			$json = json_decode($uncryptJson);
		}else{
			$json = NULL;
		}
		if ($json != NULL) {
			if ($this->time->getTimestamp() - $json->date > 86400) {
				$this->flashMessageContainer->addMessage(
					new Message(
						$this->translator->translateById('login.messages.registration.not_valid',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
					)
				);

			} else {

				$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($json->username, $this->providerName);

				$account->setExpirationDate(NULL);
				$this->accountRepository->update($account);
				$this->persistenceManager->persistAll();

			}

			$this->flashMessageContainer->addMessage(
				new Message(
					$this->translator->translateById('login.registration.success.header',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
				)
			);
			$this->redirect('index', 'Standard');
		}else{
			$this->flashMessageContainer->addMessage(
				new Message(
					$this->translator->translateById('login.messages.registration.not_valid',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
				)
			);
			$this->redirect('index', 'Standard');
		}


	}
    public function logoutAction() {
        $this->authenticationManager->logout();
		$this->flashMessageContainer->addMessage(
			new Message(
				$this->translator->translateById('login.messages.logout.success',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
			)
		);
        $this->redirect('index', 'Standard');
    }


    /**
     *
     * @param string $username
     * @return string|void
     */
    public function requestPasswordAction($username = NULL) {

        if ($username !== NULL){
            $account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username,$this->providerName);
            if ($account === NULL) {

				$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error($this->translator->translateById('login.request.passwordError',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
				$this->redirect('index', 'Standard');
			} else {
                $date = $this->time->getTimestamp();
                $user = $account->getParty();
                $random = rand();
                $json = json_encode(array(
                    'username' => $username,
                    'date' => $date,
                    'random' =>$random
                ));
                $cryptKey  = md5($this->providerName);
                $cryptJson = urlencode( base64_encode(mcrypt_encrypt( MCRYPT_RIJNDAEL_256,$cryptKey, $json, MCRYPT_MODE_CBC, md5($cryptKey))) );
                $this->sendPasswordResetLink($username,$cryptJson);


				$this->flashMessageContainer->addMessage(
					new Message(
						$this->translator->translateById('login.request.password',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')
					)
				);
				$this->redirect('index', 'Standard');

            }
        }else{

        }
    }

    public function sendPasswordResetLink($email,$cryptJson){

        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
        $requestLink = $baseUrl.'changepassword?code='.$cryptJson;
        $templatepath =  'resource://Incvisio.LostFound/Private/Templates/Emails/ForgotPassword.html';
        $this->standaloneView->setFormat('html');
        $this->standaloneView->setTemplatePathAndFilename($templatepath);
        $this->standaloneView->assign('requestLink',$requestLink);

        $emailBody = $this->standaloneView->render();

        $mail =  new \TYPO3\SwiftMailer\Message();
        $mail->setTo($email,$email)
            ->setFrom('info@lostfound.com.ua')
            ->setSubject('Reset link')
            ->setBody($emailBody, 'text/html')
            ->send();
    }

    /**
     * @param string $password,
     * @param string $passwordconfirm
     * @param string $code
     * @return string|void
     */
    public function changePasswordAction($password = NULL,$passwordconfirm = NULL,$code = NULL) {
        if ($code !== NULL) {
            $cryptJson = $code;
            $cryptKey = md5($this->providerName);
            $uncryptJson = base64_decode($cryptJson);
            $uncryptJson = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$cryptKey, $uncryptJson, MCRYPT_MODE_CBC, md5( $cryptKey ));
            $uncryptJson = rtrim($uncryptJson, "\0");
            $json = json_decode($uncryptJson);
        }else{
            $json = NULL;
        }

        $this->view->assign('code',$code);
        // @TODO Check if User has random number
        if ($json != NULL) {
            if ($this->time->getTimestamp() - $json->date > 86400) {
                $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error($this->translator->translateById('login.messages.registration.not_valid',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
                $this->redirect('index', 'Standard', NULL, array());
            } else {
                $account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($json->username, $this->providerName);

                if (($password == $passwordconfirm) && ($password !== NULL)) {
					$account->setExpirationDate(NULL);
                    $account->setCredentialsSource($this->hashService->hashPassword($password, 'default'));
                    $this->accountRepository->update($account);
                    $this->flashMessageContainer->addMessage(new Message($this->translator->translateById('login.login.update',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
                    $this->redirect('index', 'Standard', NULL, array());
                }else{
                    if ($password !== NULL){
                        $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error("Sorry"));
                    }
                }
            }
        }else{
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error($this->translator->translateById('login.messages.registration.not_valid',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
            $this->redirect('index', 'Standard', NULL, array());
        }

    }
	/**
	 * @param \TYPO3\Flow\Security\Exception\AuthenticationRequiredException $exception The exception thrown while the authentication process
	 * @return void
	 */
	protected function onAuthenticationFailure(\TYPO3\Flow\Security\Exception\AuthenticationRequiredException $exception = NULL) {
		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Authentication failed!', ($exception === NULL ? 1347016771 : $exception->getCode())));

	}


}