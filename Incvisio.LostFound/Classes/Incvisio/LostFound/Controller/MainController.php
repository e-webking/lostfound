<?php
namespace Incvisio\LostFound\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class MainController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Session\SessionInterface
	 */
	protected $session;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Service\FacebookLoginService
	 */
	protected $facebookService;

	/**
	 * @Flow\Inject
	 * @var \Incvisio\LostFound\Service\VkLoginService
	 */
	protected $vkService;

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
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

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
	 * Initialize view action
	 *
	 * @param \TYPO3\Flow\Mvc\View\ViewInterface $view
	 * @return void
	 */
	protected function initializeView(\TYPO3\Flow\Mvc\View\ViewInterface $view) {
		$loggedUser = $this->securityContext->getAccount();
		$view->assign('currentpage', $this->request->getHttpRequest()->getUri());

		if ($loggedUser!=NULL) {
			$view->assign('loggedInUser', $this->securityContext->getAccount()->getAccountIdentifier());
			$view->assign('currentUser', $this->securityContext->getAccount()->getParty());

		}
		$facebook_appid = $this->facebookService->getAppId();
		$facebook_redirecturi = $this->facebookService->getRedirectUri();
		$vkLoginUrl = $this->vkService->getAuthorizationUri();
		$this->view->assignMultiple(array(
			'vk_url' => $vkLoginUrl,
			'google_url' => $this->googlePlusService->getAuthorizationUri(),
			'facebook_appid' => $facebook_appid,
			'facebook_redirecturi' => $facebook_redirecturi
		));
	}

	/**
	 * @return \Incvisio\LostFound\Domain\Model\User
	 */
	public function getCurrentUser(){
		if($this->securityContext->getAccount() !=NULL){
			return $this->securityContext->getAccount()->getParty();
		}else {
			$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error($this->translator->translateById('main.messages.pleaseLogin',array(), NULL, NULL, 'Main', 'Incvisio.LostFound')));
			$this->redirect('index', 'Standard');
		}
	}

}