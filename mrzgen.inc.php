<?php


function mrzGenerate($info) {
    $mrzGenerator = new MrzGenerator();


    if($info['fct']=='STAFF')
        $mrzGenerator->setType("I ");
    else
        $mrzGenerator->setType("AC");
    $mrzGenerator->setFirstName($info['fname']);
    $mrzGenerator->setMiddleName(null);
    $mrzGenerator->setLastName($info['lname']);
    $mrzGenerator->setIssuingStateCode("IA ");
    $mrzGenerator->setNationalityCode($info['nationality']);
    $mrzGenerator->setDateOfBirth(preg_replace('/(\d{2})\/(\d{2})\/(\d{2})(\d{2})/','\\4\\2\\1',$info['dob']));
    $mrzGenerator->setExpiryDate(preg_replace('/(\d{2})\/(\d{2})\/(\d{2})(\d{2})/','\\4\\2\\1',$info['valid']));

    if($info['sex'][0]=='M' || $info['sex'][0]=='F')
        $mrzGenerator->setSexCode($info['sex'][0]);
    else
        $mrzGenerator->setSexCode('X');
    $mrzGenerator->setCountryCode("FRA");
    $mrzGenerator->setPassportNo($info['uid']);
    $mrzGenerator->setExtraInfo(null);
    $mrzGenerator->setExtraInfo2($info['passport']);

    $out = $mrzGenerator->getFirstLine().PHP_EOL;
    $out.= $mrzGenerator->getSecondLine().PHP_EOL;
    $out.= $mrzGenerator->getThirdLine().PHP_EOL;

    return $out;
}

class MrzGenerator {
    private $type;
    //F or M
    private $sexCode;
    private $countryCode;
    private $firstName;
    private $middleName;
    private $lastName;
    // (name maker) firstName + middleName + lastName
    private $name;
    // YY/MM/DD
    private $expiryDate;
    // YY/MM/DD
    private $dateOfBirth;
    private $passportNo;
    private $nationalityCode;
    private $issuingStateCode;
    // Sec line
    private $extraInfo;
    private $extraInfo2;


    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getSexCode() {
        return $this->sexCode;
    }

    public function setSexCode($sexCode) {
        $this->sexCode = $sexCode;
    }

    public function getCountryCode() {
        return $this->countryCode;
    }

    public function setCountryCode($countryCode) {
        $this->countryCode = $countryCode;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getMiddleName() {
        return $this->middleName;
    }

    public function setMiddleName($middleName) {
        $this->middleName = $middleName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getName() {
        $firstName = $this->firstName;
        $middleName = $this->middleName;
        $lastName = $this->lastName;
        if (!is_null($firstName) && !is_null($lastName)) {
            $master = $lastName . "  " . $firstName;
            return $master;
        }
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getExpiryDate() {
        return $this->expiryDate;
    }

    public function setExpiryDate($expiryDate) {
        $this->expiryDate = $expiryDate;
    }

    public function getDateOfBirth() {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth($dateOfBirth) {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getPassportNo() {
        return $this->passportNo;
    }

    public function setPassportNo($passportNo) {
        $this->passportNo = $passportNo;
    }

    public function getNationalityCode() {
        return $this->nationalityCode;
    }

    public function setNationalityCode($nationalityCode) {
        $this->nationalityCode = $nationalityCode;
    }

    public function getIssuingStateCode() {
        return $this->issuingStateCode;
    }

    public function setIssuingStateCode($issuingStateCode) {
        $this->issuingStateCode = $issuingStateCode;
    }

    public function getExtraInfo() {
        if (is_null($this->extraInfo)) {
            $this->setExtraInfo(str_repeat(" ",15));
        }
        return sprintf("%-15s",$this->extraInfo);
    }

    public function setExtraInfo($extraInfo) {
        $this->extraInfo = $extraInfo;
    }

    public function getExtraInfo2() {
        if (is_null($this->extraInfo2)) {
            $this->setExtraInfo2(str_repeat(" ",11));
        }
        return sprintf("%-11s",$this->extraInfo2);
    }

    public function setExtraInfo2($extraInfo2) {
        $this->extraInfo2 = $extraInfo2;
    }

    //////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////
    private function getPassportNumChecksum() {
        return $this->checksumGenerator($this->getPassportNo());
    }

    private function getDateBirthChecksum() {
        return $this->checksumGenerator($this->getDateOfBirth());
    }

    private function getDateExpiryChecksum() {
        return $this->checksumGenerator($this->getExpiryDate());
    }

    private function generate($text) {
        for($i = strlen($text); $i <= 29; $i++) {
            $text .= " ";
        }
        return strtoupper(str_replace(" ", "<",$text));
    }

    private function zeroGenerator($text) {
        $zero = "";
        for($i = 0; i < 29 - strlen($text); $i++) {
            $zero += 0;
        }
        return $zero;
    }

    function checksumGenerator($text) {
        $length=strlen($text);
        $weights = [
                7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1,
                7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1,
                7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1,
                7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1,
        ];
        $sum = 0;
        for($i = 0; $i < $length; $i++) {
            $c = ord($text[$i]);
            
            if ($c >= 48 && $c < 58) {
                $sum += $weights[$i] * ($c - 0x30);
            } elseif ($text[$i] >= 'A' && $text[$i] <= 'Z') {
                $sum += $weights[$i] * ($c - 0x37);
            } elseif ($text[$i] >= 'a' && $text[$i] <= 'z') {
                $sum += $weights[$i] * ($c - 32 - 0x37);
            }
        }
        return $sum % 10;
    }

    //
    private function getPersonalNumberChecksum() {
        $full = $this->getExtraInfo();
        return $this->checksumGenerator($full);
    }

    //
    private function lastChecksumDigit() {

        if ($this->getExtraInfo() == str_repeat(" ",15)) {
            $this->setExtraInfo(str_repeat("0",15));
        }
        if ($this->getExtraInfo2() == str_repeat(" ",11)) {
            $this->setExtraInfo2(str_repeat("0",11));
        }

        $all = //$this->getType() // 2 pos 1 to 2
             //. $this->getIssuingStateCode() // 3 pos 3 to 5
               $this->getPassportNo() // 9 pos 6 to 14
             . $this->getPassportNumChecksum() // 1 pos 15
             . $this->getExtraInfo() // 15 pos 16 to 30
             . $this->getDateOfBirth() // 6 pos 1 to 6
             . $this->getDateBirthChecksum() // 1 pos 7
             //. $this->getSexCode() // 1 pos 8
             . $this->getExpiryDate() // 6 pos 9 to 14
             . $this->getDateExpiryChecksum() // 1 pos 15
             //. $this->getNationalityCode() // 3 pos 16 to 18
             . $this->getExtraInfo2(); // 11 pos 19 to 29

        return $this->checksumGenerator($all);
    }

    //
    public function getFirstLine() {
        $all = 
              $this->getType()
            . $this->getIssuingStateCode()
            . $this->getPassportNo()
            . $this->getPassportNumChecksum()
            . $this->getExtraInfo();
//            . $this->getPersonalNumberChecksum();
            
        return $this->generate($all);
    }

    public function getSecondLine() {

        $all = 
              $this->getDateOfBirth() // 6
            . $this->getDateBirthChecksum() // 1
            . $this->getSexCode() // 1
            . $this->getExpiryDate() // 6
            . $this->getDateExpiryChecksum() // 1
            . $this->getNationalityCode() // 3
            . $this->getExtraInfo2() // 11
            //. $this->getPersonalNumberChecksum()
            . $this->lastChecksumDigit(); // 1

        return $this->generate($all);
    }

    public function getThirdLine() {
        $all = $this->getName();
        return $this->generate($all);
    }
}
?>
