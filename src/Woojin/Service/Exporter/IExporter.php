<?php

namespace Woojin\Service\Exporter;

Interface IExporter
{
    /**
     * 根據傳入的資料建立文件
     * 
     * @param  array $entitys
     * @return $this
     */
    public function create($entitys);

    /**
     * 透過excel service 將資料寫入 stream 回傳
     * 
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getResponse();
}