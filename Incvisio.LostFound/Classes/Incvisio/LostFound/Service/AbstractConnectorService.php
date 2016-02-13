<?php
namespace Incvisio\LostFound\Service;
/*                                                                        *
 * This script belongs to the FLOW3 package "Incvisio.LostFound".            *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\Browser;
use TYPO3\Flow\Http\Client\CurlEngine;
/**
 * Abstract Connector
 *
 * @Flow\Scope("singleton")
 */

abstract class AbstractConnectorService{

    /**
     * @var \TYPO3\Flow\Http\Client\Browser
     */
    protected $browser;

    /**
     *
     */
    public function __construct(){
        $browser = new Browser();
        $browser->setRequestEngine(new CurlEngine());
        $this->browser = $browser;
    }

    /**
     * @param string $uri
     * @return \TYPO3\Flow\Http\Response
     */
    public function processUri($uri){
        return $this->browser->request($uri);
    }

    /**
     * @param string $uri
     * @return \TYPO3\Flow\Http\Response
     */
    public function processUriPOST($uri){
        return $this->browser->request($uri,$method = 'POST');
    }
}