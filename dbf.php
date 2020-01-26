<h1>D2 RANDOMIZER</h1>
<?php

class DisciplesRandomize
{
    const ATCK_TWICE = 'ATCK_TWICE';

    public $unitsBooleans = [
        'SIZE_SMALL',
        'SEX_M',
        'WATER_ONLY',
    ];


    public $db = [];
    public $unitsArr = [];

    public function __construct()
    {
        $this->connectDB();
        $this->getUnitData();
        $this->generateUnitsStats();
        $this->saveUnit();
        $this->closeDB();
    }

    public function connectDB()
    {
        $this->db = [
            'gUnitsDb' => 'Globals/Gunits.dbf',
        ];
        foreach ($this->db as $dbName => $dbPath) {
            $this->db[$dbName] = dbase_open($dbPath, 2);
        }

    }

    public function getUnitData()
    {
        $gUnitsRecordNum = dbase_numrecords($this->db['gUnitsDb']);
        for ($record = 1; $record <= $gUnitsRecordNum; $record++) {
            $unit = dbase_get_record_with_names($this->db['gUnitsDb'], $record);
            $this->unitsArr[] = [
                'unitId' => $unit['UNIT_ID'],
                'unitRow' => $record,
                'unitData' => $unit,
            ];
        }
    }

    public function generateUnitsStats()
    {

        foreach ($this->unitsArr as $key => $value) {


            if ($value['unitData']['PREV_ID'] == 'g000000000') {
                $unitHealth = &$this->unitsArr[$key]['unitData']['HIT_POINT'];
                $unitHealth = intval(mt_rand($unitHealth / 2, $unitHealth * 1.5));
                unset($unitHealth);
            } else {

                $prevUnitIndex = array_search($value['unitData']['PREV_ID'], array_column($this->unitsArr, 'unitId'));
                $prevUnitHealth = $this->unitsArr[$prevUnitIndex]['unitData']['HIT_POINT'];
                $unitHealth = &$this->unitsArr[$key]['unitData']['HIT_POINT'];
                $unitHealth = $prevUnitHealth + intval(mt_rand(0, $unitHealth));
                unset($unitHealth);
            }

            foreach ($this->unitsBooleans as $defaultBoolean) {
                $unit = &$this->unitsArr[$key]['unitData'][$defaultBoolean];
                if ($unit == 'TRUE') $unit = 'TRUE';
                else $unit = 'FALSE';
                unset($unit);
            }

            $attackTwice = &$this->unitsArr[$key]['unitData']['ATCK_TWICE'];
            $attackTwice = (mt_rand(1, 10) == 10) ? 'TRUE' : 'FALSE';
            unset($attackTwice);

        }
    }


    public function saveUnit(){
         foreach ($this->unitsArr as $key => $unit) {
            unset($this->unitsArr[$key]['unitData']['deleted']);
            $this->unitsArr[$key]['unitData'] = array_values($this->unitsArr[$key]['unitData']);
            dbase_replace_record($this->db['gUnitsDb'], $this->unitsArr[$key]['unitData'], $this->unitsArr[$key]['unitRow']);
        }

        }

        public function closeDB(){
        foreach ($this->db as $db){
            dbase_close($db);
            }
        }







}

$startTime = microtime(true);
$srcFile =   __DIR__ . '/Globals/original/Gunits.dbf';
$copyFile = __DIR__ . '/Globals/Gunits.dbf';
copy($srcFile, $copyFile);
$D2 = new DisciplesRandomize();
$endTime = microtime(true);
$processTime = round($endTime - $startTime,1);
echo '...Done in ' . $processTime . ' seconds';