<?php 

namespace Woojin\Utility\ExcelGenerator;

use Woojin\Utility\ExcelGenerator\ExcelGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class UitoxExcelGenerator extends ExcelGenerator
{
    const SN_POS = 14;
    const NAME_POS = 1;
    const PRICE_POS = 5;
    const DES_POS = 6;
    const STORED_POST = 7;

    protected $container;

    protected $context;

    public function __construct(ContainerInterface $container, TokenStorage $context)
    {
        $this->context = $context;

        $this->container = $container;

        $this->authorityJudger = $this->container->get('authority.judger');
    }

    public function genWithYahooCsv($mainDataCsvPath, $mainPriceCsvPath, $mainStockCsvPath)
    {
        $uitoxCsvFileName = str_replace('temp_main_data.csv', 'uitox.csv', $mainDataCsvPath);

        // 開啟 uitox csv
        $uRows = fopen($uitoxCsvFileName, 'w+');

        // 開啟main data
        $dRows = fopen($mainDataCsvPath, 'r');

        // 開啟main price
        $pRows = fopen($mainPriceCsvPath, 'r');

        $sRows = fopen($mainStockCsvPath, 'r');

        $files = array($uRows, $dRows, $pRows, $sRows);

        $this
            ->genUitoxCsvViaLoop($dRows, $pRows, $uRows, $sRows)
            ->closeFiles($files)
        ;

        try {
            $outputPath = str_replace('temp_main_data.csv', 'uitox.xls', $mainDataCsvPath);

            $this->convertCsvToExcel($uitoxCsvFileName, $outputPath);

            return $outputPath;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function genUitoxCsvViaLoop($dRows, $pRows, $uRows, $sRows)
    {
        $dCount = 0;

        $pCount = 0;

        while (!feof($dRows)) {
            $dRow = fgetcsv($dRows);
            $pRow = fgetcsv($pRows);
            $sRow = fgetcsv($sRows);

            if ($dCount === 0) {
                fwrite($uRows, trim($this->getUnitoxHead()) . "\r\n");

                $dCount ++;

                continue;
            }

            if (!isset($dRow[self::SN_POS]) || !$this->authorityJudger->isOwnStore($dRow[self::SN_POS]) || !isset($sRow[self::STORED_POST]) ||  ($sRow[self::STORED_POST] < 1)) {
                continue;
            }

            fwrite($uRows, $this->getUitoxFormatRow($dRow, $pRow));

            $dCount ++;
        }    

        return $this;
    }

    protected function getUnitoxHead()
    {     
        return '廠商自用料號,EAN,*商品名稱,顏色,尺寸,同樣商品不同顏色尺寸ID,市價,*售價,賣點1,賣點2,賣點3,賣點4,賣點5,*主商品圖(寬360以上),商品文案1,商品文案2,商品文案3,商品圖片,是否有間隙,本商品規格,本商品標準配備,*是否有保固,年,月,*是否有維修保固資訊,維修網址,維修電話,維修地址,箱入數,最小進貨量';
    }

    protected function setUitoxDes(&$row, $deses)
    {
        $desArray = explode("\n", str_replace(',', "，", $deses));

        $remain = 4;

        foreach ($desArray as $eachDes) {
            if ($remain < 0 || empty($eachDes)) {
                continue;
            }

            $this->concat($row, $eachDes);

            $remain --;
        }

        for ($remain; $remain >= 0; $remain --) {
            $this->concat($row, '');
        }
    
        return $this;
    }

    protected function getUitoxFormatRow($dRow, $pRow)
    {
        return $this->genUitoxFormatRow($dRow[self::SN_POS], $dRow[self::NAME_POS], $pRow[self::PRICE_POS], $dRow[self::DES_POS]);
    }

    protected function genUitoxFormatRow($sn, $name, $price, $deses)
    {
        $row = '';

        $this
            ->concat($row, $sn) // 商品自用料號 
            ->concat($row, '') // EAN
            ->concat($row, $name) // *商品名稱
            ->concat($row, '紅色') // 顏色
            ->concat($row, '') // 尺寸
            ->concat($row, '') // 同樣商品不同顏色尺寸ID
            ->concat($row, '') // 市價
            ->concat($row, $price) // *售價
            ->setUitoxDes($row, $deses) // 賣點1,2,3,4,5
            ->concat($row, $sn . '.jpg') // 主商品圖(寬360以上)
            ->concat($row, '') // 商品文案1
            ->concat($row, '') // 商品文案2
            ->concat($row, '') // 商品文案3
            ->concat($row, $sn . '_2.jpg') // 商品圖片
            ->concat($row, '') // 是否有間隙
            ->concat($row, '') // 本商品規格
            ->concat($row, '') // 本商品標準配備
            ->concat($row, 'N') // *是否有保固
            ->concat($row, '') // 年
            ->concat($row, '') // 月
            ->concat($row, 'Y') // *是否有維修保固資訊
            ->concat($row, '') // 維修網址
            ->concat($row, '0229401997') // 維修電話
            ->concat($row, '新北市中和區興南路一段3號') // 維修地址
            ->concat($row, '') // 箱入數
            ->concat($row, '', "\r\n") // 最小進貨量
        ;

        return $row;
    } 
}