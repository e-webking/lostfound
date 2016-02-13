<?php

namespace Incvisio\LostFound\Factory;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Party\Domain\Model\ElectronicAddress;
use TYPO3\Flow\Security\Account;
use TYPO3\Flow\Validation\Error;
use TYPO3\SwiftMailer\Message as SwiftMessage;
use TYPO3\Flow\Error\Message;

/**
 * Class UserFactory
 *
 * @package Incvisio\LostFound\Factory
 * @Flow\Scope("singleton")
 */
class UserFactory {

    const AUTHENTICATION_PROVIDER_NAME = 'DefaultProvider';
    const CURRENT_PACKAGE_KEY = 'Incvisio.LostFound';
    const BASIC_USER_GROUP = 'Incvisio.LostFound:User';

    /**
     * @var \TYPO3\Flow\Security\AccountFactory
     * @Flow\Inject
     */
    protected $accountFactory;

    /**
     * @var \TYPO3\Flow\Security\AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

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
     * @var \TYPO3\Flow\Security\Policy\PolicyService
     * @Flow\Inject
     */
    protected $policyService;

    /**
     * Returns the authentication provider name
     *
     * @return string
     */
    public function getAuthenticationProviderName() {
        return self::AUTHENTICATION_PROVIDER_NAME;
    }

    /**
     * Creates a User with the given information
     *
     * The User is not added to the repository, the caller has to add the
     * User account to the AccountRepository and the User to the
     * PartyRepository to persist it.
     *
     * @param string $username The username of the user to be created.
     * @param string $password Password of the user to be created
     * @param string $firstName First name of the user to be created
     * @param string $lastName Last name of the user to be created
     * @param array $roleIdentifiers A list of role identifiers to assign
     * @return TYPO3\Party\Domain\Model\Person The created user instance
     */
    public function create($username, $password, $firstName, $lastName, array $roleIdentifiers = NULL) {
        $user = new  \Incvisio\LostFound\Domain\Model\User();
        $name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName, '', $username);

        $user->setName($name);

        if ($roleIdentifiers === NULL || $roleIdentifiers === array()) {
            $roleIdentifiers = array(self::BASIC_USER_GROUP);
        }

        $account = $this->accountFactory->createAccountWithPassword($username, $password, $roleIdentifiers, self::AUTHENTICATION_PROVIDER_NAME);
        $user->addAccount($account);

        return $user;
    }

    /**
     * Sends an email to a user with the new password
     *
     * @param \TYPO3\Flow\Security\Account $account
     * @param array $settings
     * @param string $newEnteredPassword
     * @return boolean $success
     */
    public function sendMail(Account $account, $settings, $newEnteredPassword = NULL) {

        if ($newEnteredPassword !== NULL) {
            $newPassword = $newEnteredPassword;
        } else {
            $newPassword = $this->algorithms->generateRandomString(10);

            $account->setCredentialsSource($this->hashService->hashPassword($newPassword, 'default'));
            $this->accountRepository->update($account);
        }

        // @TODO: Localize the email format
        $mailBody[] = 'Dear %1$s';
        $mailBody[] = '';
        $mailBody[] = 'Your password for First Visit.';
        $mailBody[] = 'The password is %2$s';
        $mailBody[] = '';
        $mailBody[] = 'If you haven\'t requested this information, please change your password at once';
        $mailBody[] = 'as others might be able to access your account';

        $success = FALSE;
        $message = new SwiftMessage();
        if (
        $message->setTo(array($account->getAccountIdentifier() => $account->getParty()->getName()))
            ->setFrom(array($settings['PasswordRecovery']['Sender']['Email'] => $settings['PasswordRecovery']['Sender']['Name']))
            ->setSubject($settings['PasswordRecovery']['Subject'])
            ->setBody(vsprintf(implode(PHP_EOL, $mailBody), array($account->getParty()->getName(), $newPassword)))
            ->send()
        ) {
            $success = TRUE;
        }

        return $success;
    }

    /**
     * Returns all roles defined in the  Incvisio.LostFound package
     *
     * @return array<\TYPO3\Flow\Security\Policy\Role>
     */
    public function getRoles() {
        $roles = array();
        foreach ($this->policyService->getRoles() as $role) {
            if ($role->getPackageKey() === self::CURRENT_PACKAGE_KEY) {
                $roles[] = $role;
            }
        }
        return $roles;
    }

}