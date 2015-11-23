<?php

namespace Woojin\Utility\Factory;

use Woojin\GoodsBundle\Entity\Img;

use Gregwar\ImageBundle\Services\ImageHandling;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Woojin\Utility\Avenue\Avenue;

class ImgFactory
{
    const MARK_X_START_POS = 22;
    const MARK_X_END_POS = 130;
    const MARK_Y_START_POS = 22;
    const MARK_Y_END_POS = 130;
    const BORDER_WIDTH = 22;

    protected $img;

    protected $imageHandling;

    protected $resolver;

    public function __construct(ImageHandling $imageHandling)
    {
        $this->img = null;

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

        $this->img = new Img();

        if (!is_object($options['file'])) {
            $this->img->setPath('_____');

            return $this;
        }

        $product = $options['products'][0];

        $imgName = $product->getImgName($options['file']);
        $relDir = $product->getImgRelDir($options['user']);
        $absDir = $_SERVER['DOCUMENT_ROOT'] . $relDir;

        if (!is_dir($absDir)) {
            mkdir($absDir, 0777, true); 
        }
                
        if ($options['file']->move($absDir, $imgName)) {
            $this->img->setPath($relDir . '/' . $imgName);

            //$this->createRemoveBorder($this->img);
        }

        return $this;
    }

    public function getImg()
    {
        return $this->img;
    }

    /**
     * @param  Img $img
     * @return boolean        
     */
    public function createRemoveBorder(Img $img)
    {   
        /**
         * 圖片來源路徑(圖片路徑必須直接使用根路徑, 否則開不了)
         * 
         * @var string
         */
        $rootPath = $img->getPath(true);

        $fileName = $img->getPathNoBorder(true);

        // 2/3, 下, 左, 右 10px
        /**
         * 圖片物件
         * 
         * @var object
         */
        $image = $this->imageHandling->open($rootPath);

        $this->imageHandling
            ->open($rootPath)
            ->resize(Avenue::IMG_WIDTH, Avenue::IMG_WIDTH)
            ->rectangle(self::MARK_X_START_POS, self::MARK_Y_START_POS, self::MARK_X_END_POS, self::MARK_Y_END_POS, 'white', true)
            ->rectangle(0, 0, Avenue::IMG_WIDTH, self::BORDER_WIDTH, 'white', true) // 抹去上方 border
            ->rectangle(0, Avenue::IMG_WIDTH - self::BORDER_WIDTH, Avenue::IMG_WIDTH, Avenue::IMG_WIDTH, 'white', true) // 抹去下方border
            ->rectangle(0, 0, self::BORDER_WIDTH, Avenue::IMG_WIDTH, 'white', true) // 抹去左邊 border
            ->rectangle(Avenue::IMG_WIDTH - self::BORDER_WIDTH, 0, Avenue::IMG_WIDTH, Avenue::IMG_WIDTH, 'white', true) // 抹去右邊 border
            ->save($fileName, 'jpg', Avenue::DESIMG_CROP_QA)
        ;

        return true;
    }
}