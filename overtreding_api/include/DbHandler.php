<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 */
class DbHandler {

    private $conn;
    private $textIds;

    function __construct() {
        //require_once dirname(__FILE__) . './DbConnect.php';
        // opening db connection
        //$db = new DbConnect();
        $this->conn = mysqli_connect("localhost", "root", "", "OvertredingDB"); //$db->connect();
    }

    function bulkInsert() {
        $inputFileName = '../uploads/sheet.xlsx';

        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $this->mapTextIds($objPHPExcel);

        $textResult = $this->createTextsBulk($objPHPExcel);
        if(!$textResult){
            return FALSE;
        }

        $speedResult = $this->createSpeedBulk($objPHPExcel);
        if(!$speedResult){
            return FALSE;
        }
        //
        $alchResult = $this->createAlchBulk($objPHPExcel);
        if(!$alchResult){
            return FALSE;
        }
        //
        $drugsResult = $this->createDrugshBulk($objPHPExcel);
        if(!$drugsResult){
            return FALSE;
        }

        $othersResult = $this->createOtherBulk($objPHPExcel);
        if(!$othersResult){
            return FALSE;
        }
        //
        $rightsResult = $this->createRightsBulk($objPHPExcel);
        if(!$rightsResult){
            return FALSE;
        }
        //
        // return TRUE;
    }

    public function getTextId($textNr){
        return $this->textIds[$textNr];
    }

    public function getLastId() {
        return mysqli_insert_id($this->conn);
    }

    public function mapTextIds($objPHPExcel){
        $this->textIds = [];
        $sheet = $objPHPExcel->getSheet(5);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);
        for ($x = 1; $x < count($sheetArray); $x++) {
            $this->textIds[$sheetArray[$x][0]] = $x;
        }

    }

    public function createTextsBulk($objPHPExcel) {
        $sheet = $objPHPExcel->getSheet(5);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);

        $stmt = $this->conn->prepare("INSERT INTO `Texts`(`body`) VALUES (?)");
        for ($x = 1; $x < count($sheetArray); $x++) {
            $textBody =$sheetArray[$x][1];
            $stmt->bind_param("s", $textBody);
            $result = $stmt->execute();
            if (!$result) {
                return FALSE;
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function createSpeedBulk($objPHPExcel) {
        $sheet = $objPHPExcel->getSheet(1);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);

        $stmt = $this->conn->prepare("INSERT INTO `Speed`(`exceed`, `road`, `text_id_1`, `text_id_2`, `text_id_3`) VALUES (?,?,?,?,?)");

        for ($x = 1; $x < count($sheetArray); $x++) {
            $exceedId = $this->parseExceed($sheetArray[$x][0]);
            $road = $this->parseRoadType($sheetArray[$x][1]);
            $text_id_1 = $this->getTextId($sheetArray[$x][2]);
            $text_id_2 = $this->getTextId($sheetArray[$x][4]);
            $text_id_3 = $this->getTextId($sheetArray[$x][6]);

            $stmt->bind_param("iiiii", $exceedId, $road, $text_id_1, $text_id_2, $text_id_3);
            $result = $stmt->execute();
            if (!$result) {
                return FALSE;
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function createAlchBulk($objPHPExcel) {
        $sheet = $objPHPExcel->getSheet(2);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);
        $stmt = $this->conn->prepare("INSERT INTO `Alchohol`(`intoxication`, `text_id_1`, `text_id_2`, `text_id_3`) VALUES (?,?,?,?)");
        for ($x = 1; $x < count($sheetArray); $x++) {
            $intoxicationId =  $this->parseIntoxication($sheetArray[$x][0]);
            $text_id_1 = $this->getTextId($sheetArray[$x][2]);
            $text_id_2 = $this->getTextId($sheetArray[$x][4]);
            $text_id_3 = $this->getTextId($sheetArray[$x][6]);
            $stmt->bind_param("iiii", $intoxicationId, $text_id_1, $text_id_2, $text_id_3);
            $result = $stmt->execute();
            if ($result) {
            } else {
                return FALSE;
            }

        }
        $stmt->close();
        return TRUE;
    }

    public function createDrugshBulk($objPHPExcel) {
        $sheet = $objPHPExcel->getSheet(3);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);
        $stmt = $this->conn->prepare("INSERT INTO `Drugs`(`blood_test`, `text_id_1`, `text_id_2`, `text_id_3`) VALUES (?,?,?,?)");
        for ($x = 1; $x < count($sheetArray); $x++) {
            $intoxicationId = $this->parseBloodTest($sheetArray[$x][0]);
            $text_id_1 = $this->getTextId($sheetArray[$x][2]);
            $text_id_2 = $this->getTextId($sheetArray[$x][4]);
            $text_id_3 = $this->getTextId($sheetArray[$x][6]);
            $stmt->bind_param("iiii", $intoxicationId, $text_id_1, $text_id_2, $text_id_3);
            $result = $stmt->execute();
            if ($result) {
            } else {
                return FALSE;
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function createOtherBulk($objPHPExcel) {
        $sheet = $objPHPExcel->getSheet(4);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);
        $stmt = $this->conn->prepare("INSERT INTO `Other`(`degree`, `description`, `text_id_1`, `text_id_2`, `text_id_3`) VALUES (?,?,?,?,?)");
        for ($x = 1; $x < count($sheetArray); $x++) {
            $degree = $sheetArray[$x][0];
            $tags = $sheetArray[$x][1];
            $description = $sheetArray[$x][2];
            $text_id_1 = $this->getTextId($sheetArray[$x][3]);
            $text_id_2 = $this->getTextId($sheetArray[$x][5]);
            $text_id_3 = $this->getTextId($sheetArray[$x][7]);
            $stmt->bind_param("isiii", $degree, $description, $text_id_1, $text_id_2, $text_id_3);
            $result = $stmt->execute();
            $tagsResult = $this->createTagsBulk($tags, $this->getLastId());
            if ($result) {
            } else {
                return FALSE;
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function createTagsBulk($tagsStr, $offense_id) {
        $tags = explode(" ", $tagsStr);
        $stmt = $this->conn->prepare("INSERT INTO `Other_Tags`(`tag_name`, `offense_id`) VALUES (?,?)");
        for ($x = 1; $x < count($tags); $x++) {
            $stmt->bind_param("si", $tags[$x], $offense_id);
            $result = $stmt->execute();
            if ($result) {
            } else {
                return FALSE;
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function createRightsBulk($objPHPExcel) {
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $sheetArray = $sheet->rangeToArray('A1'.':' . $highestColumn .$highestRow,
          NULL,
          TRUE,
          FALSE);
        $stmt = $this->conn->prepare("INSERT INTO `Rights`(`type`,`body`) VALUES (?,?)");
        for ($x = 1; $x < count($sheetArray); $x++) {
            $text = $sheetArray[$x][0];
            $type = 0;
            $stmt->bind_param("is", $type, $text);
            $result = $stmt->execute();
            if ($result) {
            } else {
                return FALSE;
            }
        }
        for ($x = 1; $x < count($sheetArray); $x++) {
            $text = $sheetArray[$x][1];
            $type = 1;
            $stmt->bind_param("is", $type, $text);
            $result = $stmt->execute();
            if ($result) {
            } else {
                return FALSE;
            }
        }
        $stmt->close();
        return TRUE;
    }



    public function getTexts() {
        $stmt = $this->conn->prepare("SELECT * FROM `Texts`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getRights() {
        $stmt = $this->conn->prepare("SELECT * FROM `Rights`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getAlchohol() {
        $stmt = $this->conn->prepare("SELECT * FROM `Alchohol`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }
    public function getDrugs() {
        $stmt = $this->conn->prepare("SELECT * FROM `Drugs`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getSpeed() {
        $stmt = $this->conn->prepare("SELECT * FROM `Speed`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getOther() {
        $stmt = $this->conn->prepare("SELECT * FROM `Other`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }
    public function getOtherTags() {
        $stmt = $this->conn->prepare("SELECT * FROM `Other_Tags`");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function parseExceed($exceedStr){
        switch ($exceedStr) {
            case '1-10':
            return 0;
            break;
            case '11-20':
            return 1;
            break;
            case '21-30':
            return 2;
            break;
            case '31-40':
            return 3;
            break;
            case '40-…':
            return 4;
            break;

            default:
            return -1;
            break;
        }
    }

    public function parseRoadType($roadStr){
        switch ($roadStr) {
            case 'Woonerf, schoolomgeving, zone 30, bebouwde kom':
            return 0;
            break;

            case 'Andere wegen':
            return 1;
            break;

            default:
            return -1;
            break;
        }
    }

    public function parseBloodTest($roadStr){
        switch ($roadStr) {
            case 'U wordt positief bevonden op de aanwezigheid van drugs in uw bloed':
            return 0;
            break;

            case 'U weigert zonder wettige reden de speekseltest of -analyse':
            return 1;
            break;

            default:
            return -1;
            break;
        }
    }

    public function parseIntoxication($roadStr){
        switch ($roadStr) {
            case '0,20 - 0,50 promille':
            return 0;
            break;

            case '0,50 - 0,80 promille':
            return 1;
            break;

            case '0,80 - 1,00 promille':
            return 2;
            break;

            case '1,00 - 1,14 promille':
            return 3;
            break;

            case '1,14 - 1,48 promille':
            return 4;
            break;

            case '1,48 promille - ...':
            return 5;
            break;

            case 'Weigering ademtest of analyse zonder wettige reden':
            return 6;
            break;

            case 'Dronkenschap':
            return 7;
            break;

            case 'U bent reeds eerder betrapt op alcoholintoxicatie van meer dan 0,8 promille of voor dronkenschap en wordt nu opnieuw betrapt op een alcoholintoxicatie van meer dan of 0,8 promille':
            return 8;
            break;

            case 'U bent reeds eerder betrapt op  alcoholintoxicatie van meer dan 0,8 promille of voor dronkenschap en wordt nu opnieuw betrapt op dronkenschap':
            return 9;
            break;

            default:
            return -1;
            break;
        }
    }

}

?>
