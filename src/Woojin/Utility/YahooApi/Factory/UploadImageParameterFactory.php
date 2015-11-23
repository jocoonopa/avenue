<?php

namespace Woojin\Utility\YahooApi\Factory;

use Woojin\Utility\Avenue\Avenue;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Entity\DesImg;

class UploadImageParameterFactory implements IParameterFactory
{
    protected $parameters;

    public function get()
    {
        return $this->parameters;
    }

    public function create($product)
    {
        $this
            ->addProductId($product)
            ->addPurge()
            ->addMainImg($product)
            ->addDesImg($product)
        ;

        return $this;
    }

    public function set($key, $val)
    {
        $this->parameters[$key] = $val;

        return $this;
    }

    public function clear()
    {
        $this->parameters = array();

        return $this;
    }

    protected function addProductId(GoodsPassport $product)
    {
        $this->parameters['ProductId'] = $product->getYahooId();

        return $this;
    }

    protected function addPurge()
    {
        $this->parameters['Purge'] = 'true';

        return $this;
    }

    protected function addMainImg(GoodsPassport $product)
    {
        $img = $product->getImg();

        if ($img) {
            $this->parameters['ImageFile1'] = '@' . realpath('.' . $img->getPath());
            $this->parameters['MainImage'] = 'ImageFile1';
        }

        return $this;
    }

    protected function addDesImg(GoodsPassport $product)
    {
        $desimg = $product->getDesimg();

        if (!$desimg) {
            return $this;
        }

        for ($i = 0; $i < Avenue::DESIMG_PIECE_NUM; $i ++) {
            $this->parameters['ImageFile' . ($i + 2)] = $this->sourcePath($desimg->getSplitPath($i));
        }

        return $this;
    }

    protected function sourcePath($path)
    {
        return '@' . realpath('.' . $path);
    }
}





