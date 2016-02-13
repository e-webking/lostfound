<?php
namespace Incvisio\LostFound\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class CommentsRepository extends Repository {

    /**
     * @var array
     */
    protected $defaultOrderings = array(
    	'publishdatetime' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
    );
}