<?php
namespace corpsepk\yii2imagecache;

use Yii;
use yii\helpers\BaseFileHelper;
use yii\helpers\Html;

/**
 * ImageCache Component
 * @author Alexsandr Khramov <corpsepk@gmail.com>
 */
class ImageCache extends \yii\base\Component
{
    public $defaultSize = '800x';
    public $cachePath;
    public $cacheUrl;
    public $graphicsLibrary = 'Imagick';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset($this->cachePath))
            throw new \yii\base\InvalidConfigException('Please, set "cachePath" at $config["components"]["imageCache"]["cachePath"].');
        $this->cachePath = Yii::getAlias($this->cachePath);
    }

    /**
     * Get thumbnail
     * @param string $srcImagePath
     * @param string $size
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * @return string
     */
    public function img($srcImagePath, $preset = false, $options = [])
    {
        return Html::img(self::imgSrc($srcImagePath, $preset), $options);
    }

    public function imgSrc($srcImagePath, $preset = false)
    {
        // Проверяем, существует ли закешированный файл
        if(is_file($this->getCachedFilePath($srcImagePath,$preset)))
            return $this->getCachedFileUrl($srcImagePath,$preset);

        // Проверяем, существует ли исходный файл
        if(!is_file($srcImagePath))
            return null;

        $this->createCachedFile($srcImagePath, $preset);

        return $this->getCachedFileUrl($srcImagePath,$preset);
    }

    /**
     * @param string $srcImagePath
     * @param string $preset
     * @return null|string
     */
    public function getCachedFilePath($srcImagePath,$preset)
    {
        $file = pathinfo($srcImagePath);
        if(!$file['basename'])
            return null;

        return $this->createCachedFilePath($preset,$file['basename']);
    }

    /**
     * @param string|array $sizes
     * @param $fileName
     * @throws \Exception
     */
    public function createCachedFilePath($sizes,$fileBasename)
    {
        if(is_string($sizes))
            $size = $this->parseSize($sizes);
        else
            $size = $sizes;

        return $this->cachePath.'/'.$size['width'].'x'.$size['height'].'/'.$fileBasename;
    }

    /**
     * @param string $srcImagePath
     * @param string $preset
     * @return null|string
     */
    public function getCachedFileUrl($srcImagePath,$preset)
    {
        $file = pathinfo($srcImagePath);
        if(!$file['basename'])
            return null;

        return $this->createCachedFileUrl($preset,$file['basename']);
    }

    /**
     * @param string|array $sizes
     * @param $fileName
     * @throws \Exception
     */
    public function createCachedFileUrl($sizes,$fileBasename)
    {
        if(is_string($sizes) || !$sizes) {
            $size = $this->parseSize($sizes);
        }else {
            $size = $sizes;
        }

        return $this->cacheUrl.'/'.$size['width'].'x'.$size['height'].'/'.$fileBasename;
    }

    /**
     * Parses size string
     * For instance: 400x400, 400x, x400
     *
     * @param $sizeString
     * @return array|null
     */
    public function parseSize($sizeString)
    {
        if(!$sizeString)
            $sizeString = $this->defaultSize;

        $sizeArray = explode('x', $sizeString);
        $part1 = (isset($sizeArray[0]) and $sizeArray[0] != '');
        $part2 = (isset($sizeArray[1]) and $sizeArray[1] != '');
        if ($part1 && $part2) {
            if (intval($sizeArray[0]) > 0
                &&
                intval($sizeArray[1]) > 0
            ) {
                $size = [
                    'width' => intval($sizeArray[0]),
                    'height' => intval($sizeArray[1])
                ];
            } else {
                $size = null;
            }
        } elseif ($part1 && !$part2) {
            $size = [
                'width' => intval($sizeArray[0]),
                'height' => null
            ];
        } elseif (!$part1 && $part2) {
            $size = [
                'width' => null,
                'height' => intval($sizeArray[1])
            ];
        } else {
            throw new \Exception('Error parsing size.');
        }

        return $size;
    }

    /**
     * @param $srcImagePath
     * @param bool $preset
     * @return string Path to cached file
     * @throws \Exception
     */
    public function createCachedFile($srcImagePath, $preset = false)
    {
        if(!$preset)
            $preset = $this->defaultSize;

        $fileExtension =  pathinfo($srcImagePath, PATHINFO_EXTENSION);
        $fileName =  pathinfo($srcImagePath, PATHINFO_FILENAME);

        $pathToSave = $this->cachePath.'/'.$preset.'/'.$fileName.'.'.$fileExtension;
        BaseFileHelper::createDirectory(dirname($pathToSave), 0777, true);

        $size = $preset ? $this->parseSize($preset) : false;

//        if($this->graphicsLibrary == 'Imagick'){
            $image = new \Imagick($srcImagePath);
            $image->setImageCompressionQuality(100);

            if($size){
                if($size['height'] && $size['width']){
                    $image->cropThumbnailImage($size['width'], $size['height']);
                }elseif($size['height']){
                    $image->thumbnailImage(0, $size['height']);
                }elseif($size['width']){
                    $image->thumbnailImage($size['width'], 0);
                }else{
                    throw new \Exception('Error at $this->parseSize($sizeString)');
                }
            }

            $image->writeImage($pathToSave);
//        }

        if(!is_file($pathToSave))
            throw new \Exception('Error while creating cached file');

        return $image;
    }
}