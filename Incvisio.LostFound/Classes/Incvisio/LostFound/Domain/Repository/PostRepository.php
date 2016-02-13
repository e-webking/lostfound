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
class PostRepository extends Repository {

    /**
     * @Flow\Inject
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $entityManager;

    /**
     * DBAL connection
     *
     * @var \Doctrine\DBAL\Driver\PDOConnection
     */
    protected $connection;
    /**
     * Injected configuration manager dependency
     *
     * @Flow\Inject
     * @var \TYPO3\Flow\Configuration\ConfigurationManager
     */
    protected $configurationManager;
    
    /**
     * 
     * @var unknown
     */
    protected $defaultOrderings = array(
			'publishdate' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
	);

    public function findWithFilterLost($lost_input=NULL,$found_input=NULL,$city_input=NULL,$place_input=NULL,$date_from=NULL,$date_to=NULL,$category_found=NULL,$category_lost=NULL,$page_position,$item_per_page){
        $backendOptions = $this->configurationManager->getConfiguration('Settings', 'TYPO3.Flow.persistence.backendOptions');
        $config = new \Doctrine\DBAL\Configuration();
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($backendOptions, $config);
        $statement = "SELECT  * "
            . "FROM  `incvisio_lostfound_domain_model_post` as adv "
            . "WHERE adv.lost = 1 ";
            if($lost_input!=NULL){

                $word_count = count(str_word_count($lost_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь"));

                if($word_count==1) {
                    $statement .= "AND MATCH (adv.title) AGAINST ('+$lost_input'  IN BOOLEAN MODE)";

                }elseif($word_count ==2){
                    $statement .= "AND MATCH (adv.title) AGAINST ('~$lost_input'  IN BOOLEAN MODE)";
                }elseif($word_count >=3){
                    $words = str_word_count($lost_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь");
                    $statement .= "AND MATCH (adv.title) AGAINST ('(";
                    $searchWords = "";
                    foreach($words as $word){
                         $searchWords .="+".$word." ";
                        }

                    $statement.= "$searchWords)'  IN BOOLEAN MODE)";
                }
            }
            if($city_input!=NULL){
                $statement .= "AND adv.city = '".$city_input."' ";
            }

            if($place_input!=NULL){
                $statement .= "AND adv.place = '".$place_input."' ";

            };

            if ($category_found != NULL){
                $statement .= "AND adv.category ='".$category_found. "'";

            }
            if ($category_lost != NULL){
                $statement .= "AND adv.category ='".$category_lost. "'";

            }

            if($date_from!=NULL) {
                $newDate = date("Y-m-d", strtotime($date_from));
                $statement .= "AND dateLostOrFound >=  date('" . $newDate . "')";
            }
            if($date_to!=NULL) {
                $newDate = date("Y-m-d", strtotime($date_to));
                $statement .= "AND dateLostOrFound <=  date('" . $newDate . "') ";
            }
            $statement .= "ORDER BY publishDate DESC LIMIT $page_position, $item_per_page";
        $result = $this->connection->query($statement)->fetchAll();
        return $result;
    }

    public function findWithFilterFound($lost_input=NULL,$found_input=NULL,$city_input=NULL,$place_input=NULL,$date_from=NULL,$date_to=NULL,$category_found=NULL,$category_lost=NULL,$page_position,$item_per_page){
        $backendOptions = $this->configurationManager->getConfiguration('Settings', 'TYPO3.Flow.persistence.backendOptions');
        $config = new \Doctrine\DBAL\Configuration();
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($backendOptions, $config);
        $statement = "SELECT  * "
            . "FROM  `incvisio_lostfound_domain_model_post` as adv "
            . "WHERE adv.found = 1 ";

        if($found_input!=NULL){
            $word_count = count(str_word_count($found_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь"));

            if($word_count==1) {
                $statement .= "AND MATCH (adv.title) AGAINST ('+$found_input'  IN BOOLEAN MODE)";

            }elseif($word_count ==2){
                $statement .= "AND MATCH (adv.title) AGAINST ('~$found_input'  IN BOOLEAN MODE)";
            }elseif($word_count >=3){
                $words = str_word_count($found_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь");
                $statement .= "AND MATCH (adv.title) AGAINST ('(";
                $searchWords = "";
                foreach($words as $word){
                    $searchWords .="+".$word." ";
                }

                $statement.= "$searchWords)'  IN BOOLEAN MODE)";
            }
        }
        if($city_input!=NULL){
            $statement .= "AND adv.city = '".$city_input."' ";
        }

        if($place_input!=NULL){
            $statement .= "AND adv.place = '".$place_input."' ";

        };

        if ($category_found != NULL){
            $statement .= "AND adv.category ='".$category_found. "'";

        }
        if ($category_lost != NULL){
            $statement .= "AND adv.category ='".$category_lost. "'";

        }

        if($date_from!=NULL) {
            $newDate = date("Y-m-d", strtotime($date_from));
            $statement .= "AND dateLostOrFound >=  date('" . $newDate . "')";
        }
        if($date_to!=NULL) {
            $newDate = date("Y-m-d", strtotime($date_to));
            $statement .= "AND dateLostOrFound <=  date('" . $newDate . "') ";
        }
        $statement .= "ORDER BY publishDate DESC LIMIT $page_position, $item_per_page";
        $result = $this->connection->query($statement)->fetchAll();
        return $result;
    }

    public function countLost($lost_input=NULL,$city_input=NULL,$category_lost=NULL)
    {
        $backendOptions = $this->configurationManager->getConfiguration('Settings', 'TYPO3.Flow.persistence.backendOptions');
        $config = new \Doctrine\DBAL\Configuration();
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($backendOptions, $config);
        $statement = "SELECT  COUNT(*) as number "
            . "FROM  `incvisio_lostfound_domain_model_post` as adv "
            . "WHERE adv.lost = 1 ";
        if($city_input!=NULL){
            $statement .= "AND adv.city = '".$city_input."' ";
        }
        if ($category_lost != NULL){
            $statement .= "AND adv.category ='".$category_lost. "'";

        }
        if ($lost_input != NULL) {
            $word_count = count(str_word_count($lost_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь"));

            if($word_count==1) {
                $statement .= "AND MATCH (adv.title) AGAINST ('+$lost_input'  IN BOOLEAN MODE)";

            }elseif($word_count ==2){
                $statement .= "AND MATCH (adv.title) AGAINST ('~$lost_input'  IN BOOLEAN MODE)";
            }elseif($word_count >=3){
                $words = str_word_count($lost_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь");
                $statement .= "AND MATCH (adv.title) AGAINST ('(";
                $searchWords = "";
                foreach($words as $word){
                    $searchWords .="+".$word." ";
                }

                $statement.= "$searchWords)'  IN BOOLEAN MODE)";
            }
        }
        $result = $this->connection->query($statement)->fetchAll();
        return $result[0];
    }

    public function countFound($found_input=NULL,$city_input=NULL,$category_found=NULL)
    {
        $backendOptions = $this->configurationManager->getConfiguration('Settings', 'TYPO3.Flow.persistence.backendOptions');
        $config = new \Doctrine\DBAL\Configuration();
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($backendOptions, $config);
        $statement = "SELECT  COUNT(*) as number "
            . "FROM  `incvisio_lostfound_domain_model_post` as adv "
            . "WHERE adv.found = 1 ";
        if($city_input!=NULL){
            $statement .= "AND adv.city = '".$city_input."' ";
        }
        if ($category_found != NULL){
            $statement .= "AND adv.category ='".$category_found. "'";

        }
        if ($found_input != NULL) {
            $word_count = count(str_word_count($found_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь"));

            if($word_count==1) {
                $statement .= "AND MATCH (adv.title) AGAINST ('+$found_input'  IN BOOLEAN MODE)";

            }elseif($word_count==2){
                $statement .= "AND MATCH (adv.title) AGAINST ('~$found_input'  IN BOOLEAN MODE)";
            }elseif($word_count >=3){
                $words = str_word_count($found_input,2,"АаБбВвГгДдЕеЄєЁёЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяЬь");
                $statement .= "AND MATCH (adv.title) AGAINST ('(";
                $searchWords = "";
                foreach($words as $word){
                    $searchWords .="+".$word." ";
                }

                $statement.= "$searchWords)'  IN BOOLEAN MODE)";
            }
        }
        $result = $this->connection->query($statement)->fetchAll();
        return $result[0];
    }

    public function foundCity($city){
        $backendOptions = $this->configurationManager->getConfiguration('Settings', 'TYPO3.Flow.persistence.backendOptions');
        $config = new \Doctrine\DBAL\Configuration();
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($backendOptions, $config);
        $statement = "SELECT  `city`.`name`,`region`.`name` as `region`  FROM `city`  "
            . "LEFT JOIN `region` ON `city`.`region_id`=`region`.`id` "
            . "WHERE `city`.`name` like '" . $city . "%' ORDER BY `city`.`name` LIMIT 0,6";
        $result = $this->connection->query($statement)->fetchAll();
        return $result;
    }
}