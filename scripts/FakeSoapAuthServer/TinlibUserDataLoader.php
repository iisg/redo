<?php
// @codingStandardsIgnoreFile

// --- MODIFIED ---
$CONFIG = require('config.php');
define('CONFIG', $CONFIG);
// --- END MODIFIED ---

//require_once 'suw/src/common/XmlParserHelper.php';  // unused

abstract class TinlibUserDataLoader
{
    private static function loadXMLFromTinlib($userLogin) {
        if (isset($GLOBALS['clientDataXML']) && is_object($GLOBALS['clientDataXML']))
            return true;        //already loaded

        if (strlen($userLogin) < 6)
            return false;

        if (strlen($userLogin) == 6)
            $userId = "B/" . $userLogin;
        else
            $userId = $userLogin;

        try {
            $url = CONFIG['wsdlUrl'];
            // --- MODIFIED ---
            $mergedOptions = array_merge(array(
                'trace' => 1,
                'exceptions' => true,
            ), CONFIG['clientOptions']);
            $client = new SoapClient($url, $mergedOptions);
            // --- END MODIFIED ---

            $GLOBALS['soap_client'] = $client;
        } catch (Exception $e) {
            return false;
        }

        try {
            $results = $client->getClientDataById($userId);
        } catch (Exception $e) {
            return false;
        }

        $GLOBALS['clientDataXML'] = $results;

        // FIXME There's something wrong with this part.
        // If $GLOBALS['clientDataXml'] is really an object, then ValidLogin() will fail because it attempts to use it
        // with array syntax (ie. $obj['something'] instead of $obj->something). It was like this originally, so I'm
        // not touching it. Returned value of this method isn't used anyway.
        if (is_object($GLOBALS['clientDataXML']))
            return true;
    }

    public static function ValidLogin($clientId, $password) {
        TinlibUserDataLoader::loadXMLFromTinlib($clientId);
        if (isset($GLOBALS['clientDataXML'])) {
            if (isset($GLOBALS['clientDataXML']['plainPassword'])) {
                return strcmp($GLOBALS['clientDataXML']['plainPassword'], $password) === 0;
            } else if (isset($GLOBALS['clientDataXML']['password']) && isset($GLOBALS['soap_client'])) {
                $client = $GLOBALS['soap_client'];

                try {
                    return $client->isValidPassword($password, $GLOBALS['clientDataXML']['password']);
                } catch (Exception $e) {
                    return false;
                }
            }
        }

        return false;
    }

    public static function GetUserPassword($userLogin) {
        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);
        if (isset($GLOBALS['clientDataXML'])) {
            if (isset($GLOBALS['clientDataXML']['plainPassword'])) {
                return $GLOBALS['clientDataXML']['plainPassword'];
            } else if (isset($GLOBALS['clientDataXML']['password'])) {
                return $GLOBALS['clientDataXML']['password'];
            }
        }

        return false;
    }

    public static function LoadUserPersonalData($user) {
        TinlibUserDataLoader::loadXMLFromTinlib($user->data['LOGIN']);
        if (isset($GLOBALS['clientDataXML'])) {
            $user->data['FAMILY_NAME'] = $GLOBALS['clientDataXML']['last_name'];
            $user->data['FIRST_NAME'] = $GLOBALS['clientDataXML']['first_name'];
            $user->data['ADDRESS_STREET'] = $GLOBALS['clientDataXML']['street'];
            $user->data['ADDRESS_CITY'] = $GLOBALS['clientDataXML']['city'];
            $user->data['PESEL'] = $GLOBALS['clientDataXML']['pesel'];
        }
    }

    public static function LoadUserVariableData($user) {
        TinlibUserDataLoader::loadXMLFromTinlib($user->data['LOGIN']);
        if (isset($GLOBALS['clientDataXML'])) {
            $user->data['STATUS'] = $GLOBALS['clientDataXML']['categorycode'];    // było 'status'
            $user->data['AFFILIATION'] = $GLOBALS['clientDataXML']['department'];
            $user->data['AFFILIATION_CODE'] = $GLOBALS['clientDataXML']['dpcode'];
            $user->data['SUBAFFILIATION'] = $GLOBALS['clientDataXML']['institute_desc'];
            $user->data['SUBAFFILIATION_CODE'] = $GLOBALS['clientDataXML']['institute'];    //institute - CODE
            $user->data['DATE_VALID'] = TinlibUserDataLoader::GetAccountExpDate($user->data['LOGIN']);
            $user->data['EMAIL'] = isset($GLOBALS['clientDataXML']['email_last_used']) ? $GLOBALS['clientDataXML']['email_last_used'] : "";
            $user->tinlibAuthString = $GLOBALS['clientDataXML']['auth_string'];
            $user->FillAffiliationData($GLOBALS['clientDataXML']['department'], $GLOBALS['clientDataXML']['dpcode'], $GLOBALS['clientDataXML']['institute_desc'], $GLOBALS['clientDataXML']['institute']);


//                            echo "department: ". $GLOBALS['clientDataXML']['department']."<br><BR>";
//                            echo "dpcode: ". $GLOBALS['clientDataXML']['dpcode']."<br><BR>";
//                            echo "institute_desc: ". $GLOBALS['clientDataXML']['institute_desc']."<br><BR>";
//                            echo "institute: ". $GLOBALS['clientDataXML']['institute']."<br><BR>";
//                            exit();

        }

    }

    public static function LoadUserLoans($userLogin) {
        if (is_array($GLOBALS['clientLoans']))
            return $GLOBALS['clientLoans'];        //already loaded

        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);

        if (isset($GLOBALS['clientDataXML']) && isset($GLOBALS['clientDataXML']['loans'])) {
            $loans = array();
            if (is_array($GLOBALS['clientDataXML']['loans'])) {
                foreach ($GLOBALS['clientDataXML']['loans'] as $loan) {
                    $loans[] = new ClientLoan($loan['item_number'], $loan['item_title'], $loan['date_due']);
                }
            }
            $GLOBALS['clientLoans'] = $loans;
            return $loans;
        }
    }

    ///returns an array [total][soon][late]
    public static function GetUsersTotalSoonAndLateLoansCount($userLogin) {
        $counts = array();
        $loans = TinlibUserDataLoader::LoadUserLoans($userLogin);
        $counts [0] = count($loans);
        $counts [1] = 0;
        $counts [2] = 0;
        foreach ($loans as $loan) {
            if ($loan->isSoon)
                $counts [1]++;
            else if ($loan->isLate)
                $counts [2]++;
        }
        return $counts;
    }

    public static function GetUsersItemsAwaiting($userLogin) {
        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);
        return $GLOBALS['clientDataXML']['items_awaiting'];;
    }

    public static function GetUsersTotalFine($userLogin) {
        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);
        return $GLOBALS['clientDataXML']['client_total_fine'];
    }

    public static function GetAccountExpDate($userLogin) {
        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);
        return str_replace("/", ".", $GLOBALS['clientDataXML']['account_exp_date']);
    }

    public static function GetAccountValidityDaysLeft($userLogin) {
        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);

        $time = ClientLoan::getTimeInverted($GLOBALS['clientDataXML']['account_exp_date']);
        return (int)(($time - time()) / (24 * 60 * 60));
    }

    /// this should be optimized, so that key renoval is used when possible, not a full login
    public static function GetTinlibSessionId($userLogin) {
        TinlibUserDataLoader::loadXMLFromTinlib($userLogin);
        return $GLOBALS['clientDataXML']['auth_string'];
    }

}


class ClientLoan
{
    var $loanId;
    var $title;
    var $returnDate;
    var $returnDateString;
    var $isSoon;
    var $isLate;

    public function __construct($id, $title, $returnDateString) {
        $this->loanId = $id;
        $this->title = $title;
        $this->returnDate = $id;
        $this->returnDateString = $returnDateString;
        $this->returnDate = ClientLoan::getTime($returnDateString);
        if ($this->returnDate < time())
            $this->isLate = true;
        else if ($this->returnDate < time() + (7 * 24 * 60 * 60))    // one week to go
            $this->isSoon = true;
    }

    // parses 26/06/2002 to unix timestamp
    public static function getTime($date) {
        $date = explode("/", $date);
        return mktime(23, 59, 59, $date[1], $date[0], $date[2]);
    }

    // parses 2002/06/30 to unix timestamp
    public static function getTimeInverted($date) {
        $date = explode("/", $date);
        return mktime(23, 59, 59, $date[1], $date[2], $date[0]);
    }


}

	
