<?php
namespace Incvisio\LostFound\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Session\SessionInterface
	 */
	protected $session;

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

	public function authAction(){
		$this->redirectToUri($this->twitterService->getAuthorizationUri());
	}
	/**
	 * @return void
	 */
	public function indexAction() {
		$this->session->start();
	}

	/**
	 * @return void
	 */
	public function aboutAction() {

	}

	/**
	 * @return void
	 */
	public function ruleAction() {

	}

    /**
     * @return void
     */
    public function contactsAction() {

    }

	/**
	 * @return void
	 */
	public function thanksAction() {

	}

	/**
	 * @return void
	 */
	public function donateAction(){

	}

}