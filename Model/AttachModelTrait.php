<?php

namespace Zk2\UsefulBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Zk2\UsefulBundle\Zk2UsefulBundle;
use Zk2\UsefulBundle\Model\AttachModelException;

/**
 * AttachModelTrait
 */
trait AttachModelTrait
{
    /**
     * @var UploadedFile $totalFile
     * @Assert\File(
     *     maxSizeMessage = "Max file size 20Mb",
     *     maxSize = "20480k",
     *     mimeTypes = {"image/jpg", "image/jpeg", "image/gif", "image/png"},
     *     mimeTypesMessage = "Only JPG, JPEG, GIF or PNG format."
     * )
     */
    protected $totalFile;

    /**
     * @var array $widthHeight
     */
    protected $widthHeight = array();

    /**
     * @var array $widthHeightPreview
     */
    protected $widthHeightPreview = array();

    /**
     * @var integer $minWidth
     */
    protected $minWidth;

    /**
     * @var integer $minHeight
     */
    protected $minHeight;

    /**
     * @var string $previewPrefix
     */
    protected $previewPrefix = 'preview_';

    /**
     * @var string $originalPrefix
     */
    protected $originalPrefix = '';

    /**
     * Get upload path
     *
     * @return string
     */
    abstract public function getUploadPath();

    /**
     * Upload Image
     *
     * @return string
     */
    abstract public function uploadImage();

    /**
     * Set Width Height
     *
     * @param integer $width
     * @param integer $height
     */
    public function setWidthHeight($width, $height = 0)
    {
        $this->widthHeight = array(abs((integer)$width), abs((integer)$height));
    }

    /**
     * Set Width Height Preview
     *
     * @param integer $width
     * @param integer $height
     */
    public function setWidthHeightPreview($width, $height = 0)
    {
        $this->widthHeightPreview = array(abs((integer)$width), abs((integer)$height));
    }

    /**
     * Set min Width
     *
     * @param integer $width
     */
    public function setMinWidth($width)
    {
        $this->minWidth = abs((integer)$width);
    }

    /**
     * Set min Height
     *
     * @param integer $height
     */
    public function setMinHeight($height)
    {
        $this->minHeight = abs((integer)$height);
    }

    /**
     * Get MinPrefix
     *
     * @return string
     */
    public function getPreviewPrefix()
    {
        return $this->previewPrefix;
    }

    /**
     * @param string $previewPrefix
     */
    public function setPreviewPrefix($previewPrefix)
    {
        $this->previewPrefix = $previewPrefix;
    }

    /**
     * @return string
     */
    public function getOriginalPrefix()
    {
        return $this->originalPrefix;
    }

    /**
     * @param string $originalPrefix
     */
    public function setOriginalPrefix($originalPrefix)
    {
        $this->originalPrefix = $originalPrefix;
    }

    /**
     * Set totalFile
     *
     * @param UploadedFile $totalFile
     */
    public function setTotalFile(UploadedFile $totalFile = null)
    {
        $this->totalFile = $totalFile;
    }

    /**
     * Get totalFile
     *
     * @return string
     */
    public function getTotalFile()
    {
        return $this->totalFile;
    }

    /**
     * Get root web path
     *
     * @return string
     */
    public function getFullWebPath()
    {
        //return realpath(__DIR__.'/../../../../web');
        return Zk2UsefulBundle::getFullWebPath();
    }

    /**
     * Get root web path
     *
     * @return string
     */
    public function getFullUploadPath()
    {
        return
            rtrim(
                rtrim($this->getFullWebPath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->getUploadPath(),
                DIRECTORY_SEPARATOR
            );
    }

    /**
     * Upload file
     *
     * @param null $fileName
     * @return bool|null|string
     * @throws AttachModelException
     */
    public function uploadFile($fileName = null)
    {
        if (!$this->totalFile instanceof UploadedFile) {
            return false;
        }

        if (!class_exists('Imagick')) {
            throw new AttachModelException('The requested PHP extension ext-imagick * is missing from your system.');
        }

        if (null === $fileName) {
            if (null === $ext = $this->totalFile->guessExtension()) {
                $ext = $this->totalFile->getExtension();
            }
            $fileName = sha1(uniqid(mt_rand(), true));
            if (null !== $ext) {
                $fileName .= '.'.$ext;
            }
        } else {
            if ($this->totalFile->getClientOriginalExtension()) {
                $fileName .= '.'.$this->totalFile->getClientOriginalExtension();
            } elseif ($this->totalFile->getExtension()) {
                $fileName .= '.'.$this->totalFile->getExtension();
            }
        }

        $fullUploadPath = $this->getFullUploadPath();
        if (!is_dir($fullUploadPath)) {
            if (!mkdir($fullUploadPath, 0775, true)) {
                throw new AttachModelException('Not possible create UploadDirectory: '.$fullUploadPath);
            }
        }

        $move = false;

        if ($this->widthHeight) {
            $move = $this->resize(
                $fullUploadPath,
                $this->getOriginalPrefix().$fileName,
                $this->widthHeight
            );
            if ($this->widthHeightPreview) {
                $this->resize(
                    $fullUploadPath,
                    $this->getPreviewPrefix().$fileName,
                    $this->widthHeightPreview
                );
            }
        }

        if (!$move) {
            $source = new \Imagick($this->totalFile->getRealPath());
            $source->writeImage($fullUploadPath.DIRECTORY_SEPARATOR.$this->getOriginalPrefix().$fileName);
        }

        return $fileName;
    }


    /**
     * Resize image
     *
     * @param $fullUploadPath
     * @param $fileName
     * @param array $widthHeight
     * @return bool
     * @throws AttachModelException
     */
    protected function resize($fullUploadPath, $fileName, array $widthHeight)
    {
        if (!array_sum($widthHeight)) {
            return false;
        }

        $ext = substr($fileName, strrpos($fileName, '.') + 1);

        if (!in_array($ext, array('jpg', 'png', 'gif', 'jpeg'))) {
            throw new AttachModelException('Only JPG, JPEG, GIF or PNG format... '.$ext);
        }

        $source = new \Imagick(realpath($this->totalFile->getRealPath()));
        $sourceWidth = $source->getImageWidth();
        $sourceHeight = $source->getImageHeight();
        $width = $widthHeight[0];
        $height = $widthHeight[1];

        if ($width == 0 or $height == 0) {
            if ($width) {
                $source->thumbnailImage($width, 0);
            } elseif ($height) {
                $source->thumbnailImage(0, $height);
            }
            $source->writeImage($fullUploadPath.DIRECTORY_SEPARATOR.$fileName);

            return true;
        } elseif ($sourceWidth >= $width and $sourceHeight >= $height) {
            if ($width == $height and $sourceWidth >= $sourceHeight) {
                $source->thumbnailImage(0, $height);
                $r = (integer)(($source->getImageWidth() - $width) / 2);
                $source->cropImage($width, $height, $r, 0);
                $source->writeImage($fullUploadPath.DIRECTORY_SEPARATOR.$fileName);

                return true;
            }
            $source->thumbnailImage($width, 0);
            $r = (integer)(($source->getImageHeight() - $height) / 2);
            $source->cropImage($width, $height, 0, $r);
            $source->writeImage($fullUploadPath.DIRECTORY_SEPARATOR.$fileName);

            return true;
        } else {
            throw new AttachModelException('The image is too small ('.$sourceWidth.'x'.$sourceHeight.')');
        }
    }
}