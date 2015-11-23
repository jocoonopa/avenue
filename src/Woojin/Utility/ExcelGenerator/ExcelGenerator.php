<?php

namespace Woojin\Utility\ExcelGenerator;

class ExcelGenerator 
{
    public function joinFiles(array $files, $outputPath) 
    {
        $this->isFilesValid($files);

        $self = $this;
        
        $wH = fopen($outputPath, 'w+');

        array_map(function ($file) use ($self, $wH) {
            $this->addContent($file, $wH);
            $this->toNext($wH);
        }, $files);

        fclose($wH);

        return $this;
    }

    protected function isFilesValid($files)
    {
        if (!is_array($files)) {
            throw new Exception('\'$files\' must be an array');
        }

        return $this;
    }

    protected function addContent($file, $wH)
    {
        $fh = fopen($file, 'r');

        while (!feof($fh)) {
            fwrite($wH, fgets($fh));
        }

        return fclose($fh);  
    }

    protected function toNext($wH)
    {
        return fwrite($wH, "\n");
    }

    /**
     * 無網址下載檔案
     */
    public function download($response, $outputPath, $displayName)
    {
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($outputPath));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($displayName) . '";');
        $response->headers->set('Content-length', filesize($outputPath));

        $response->sendHeaders();

        $response->setContent(readfile($outputPath));

        return $response->send();
    }

    public function closeFiles($files)
    {
        foreach ($files as $file) {
            fclose($file);
        }

        return $this;
    }

    public function convertCsvToExcel($loadPath, $outputPath)
    {
        $objReader = \PHPExcel_IOFactory::createReader('CSV');

        $objPHPExcel = $objReader->load($loadPath);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save($outputPath);
    }

    protected function concat(&$string, $concat, $con = ',')
    {
        $string .= $concat . $con;

        return $this;
    }
}