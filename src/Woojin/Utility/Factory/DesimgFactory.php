<?php

namespace Woojin\Utility\Factory;

use Woojin\GoodsBundle\Entity\Desimg;
use Woojin\Utility\Avenue\Avenue;
use Gregwar\ImageBundle\Services\ImageHandling;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DesimgFactory
{
    protected $desimg;

    protected $imageHandling;

    protected $resolver;

    public function __construct(ImageHandling $imageHandling)
    {
        $this->desimg = null;

        $this->imageHandling = $imageHandling;

        $this->resolver = new OptionsResolver();
        
        $this->configureOptions($this->resolver);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('product', 'file', 'user'));

        return $this;
    } 

    public function create(array $options = array())
    {
        if (!isset($options['products'][0])) {
            return $this;
        }

        $this->desimg = new Desimg();

        if (!is_object($options['file'])) {
            $this->desimg->setPath('_____');

            return $this;
        }

        $product = $options['products'][0];

        $imgName = $product->getImgName($options['file'], 'des');
        $relDir = $product->getImgRelDir($options['user']);
        $absDir = $_SERVER['DOCUMENT_ROOT'] . $relDir;

        if (!is_dir($absDir)) {
            mkdir($absDir, 0777, true); 
        }
                
        if ($options['file']->move($absDir, $imgName)) {
            $this->desimg->setPath($relDir . '/' . $imgName);
        }

        return $this;
    }

    public function getDesimg()
    {
        return $this->desimg;
    }

    /**
     * 因為Yahoo上傳圖片限定大小為 500k, 而我們的附圖往往至少都超過1 MB, 
     * 因此透過此函式利用 GregwarImage 去做圖片切割
     * 
     * @param  DesImg $desimg
     * @return boolean        
     */
    public function spliceDesImage(DesImg $desimg)
    {   
        /**
         * x方向切割起點
         * 
         * @var integer
         */
        $xPos = 0;

        /**
         * y方向切割起點
         * 
         * @var integer
         */
        $yPos = 0;

        /**
         * 區塊高度陣列
         * 
         * @var array
         */
        $partHeights = array(
            Avenue::DESIMG_1_HEIGHT, 
            Avenue::DESIMG_2_HEIGHT, 
            Avenue::DESIMG_3_HEIGHT, 
            Avenue::DESIMG_4_HEIGHT, 
            Avenue::DESIMG_5_HEIGHT
        );

        /**
         * 圖片來源路徑(圖片路徑必須直接使用根路徑, 否則開不了)
         * 
         * @var string
         */
        // prod $rootPath = substr($desimg->getPath(), 1);
        $rootPath = $_SERVER['DOCUMENT_ROOT'] . $desimg->getPath();

        /**
         * 圖片物件
         * 
         * @var object
         */
        $image = $this->imageHandling->open($rootPath);

        /**
         * 取得來源圖片寬度
         * 
         * @var integer
         */
        $width = $image->width();

        // 根據區塊高度陣列進行切割
        foreach ($partHeights as $key => $partHeight) {            
            $this->imageHandling
                ->open($rootPath)
                ->crop($xPos, $yPos, $width, $partHeight)
                ->save(substr(str_replace('http://avenue2003.29230696.com/', '', $rootPath), 0, -4) . $key . '.jpg', 'jpg', Avenue::DESIMG_CROP_QA)
            ;
    
            $yPos += $partHeight;
        }

        return true;
    }
}