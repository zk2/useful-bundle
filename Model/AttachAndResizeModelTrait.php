<?php

namespace Zk2\UsefulBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zk2\UsefulBundle\Zk2UsefulBundle;

/**
 * AttachModelTrait
 */
trait AttachAndResizeModelTrait
{
    /**
     * @var UploadedFile $sourceFile
     */
    protected $sourceFile;

    /**
     * @var string $defaultBaseSide
     */
    protected $defaultBaseSide = 'least_side';

    /**
     * @var array $widthHeight
     */
    protected $widthHeight = [0, 0];

    /**
     * @var array $childrenWidthHeight
     */
    protected $childrenWidthHeight = [];

    /**
     * @var string $originalSuffix
     */
    protected $originalSuffix = 'main';

    /**
     * @var array $sides
     */
    protected $sides = ['width', 'height', 'largest_side', 'least_side'];

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
     * @param $defaultBaseSide
     * @throws AttachModelException
     */
    public function setDefaultBaseSide($defaultBaseSide)
    {
        $defaultBaseSide = strtolower($defaultBaseSide);
        if (!in_array($defaultBaseSide, $this->sides)) {
            throw new AttachModelException('Base side is incorrect... Use '.implode(', ', $this->sides));
        }
        $this->defaultBaseSide = $defaultBaseSide;
    }

    /**
     * Set Width Height
     *
     * @param integer $width
     * @param integer $height
     */
    public function setWidthHeight($width, $height)
    {
        $this->widthHeight = array(abs((integer)$width), abs((integer)$height));
    }

    /**
     * @param array $children
     * @throws AttachModelException
     */
    public function setChildrenWidthHeight(array $children)
    {
        foreach ($children as $child) {
            if (!is_array($child) or 2 != count($child) or array_filter(
                    $child,
                    function ($val) {
                        return !is_numeric($val) or $val < 0;
                    }
                )
            ) {
                throw new AttachModelException('Children must be array, e.g. array(array(300, 200), array(400, 300))');
            }
            $this->childrenWidthHeight[implode('x', $child)] = array_values($child);
        }
    }

    /**
     * @return string
     */
    public function getOriginalSuffix()
    {
        return $this->originalSuffix;
    }

    /**
     * @param string $originalSuffix
     */
    public function setOriginalSuffix($originalSuffix)
    {
        $this->originalSuffix = $originalSuffix;
    }

    /**
     * Set SourceFile
     *
     * @param UploadedFile $sourceFile
     */
    public function setSourceFile(UploadedFile $sourceFile = null)
    {
        $this->sourceFile = $sourceFile;
    }

    /**
     * Get SourceFile
     *
     * @return string
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * Get root web path
     *
     * @return string
     */
    public function getFullWebPath()
    {
        return Zk2UsefulBundle::getFullWebPath();
    }

    /**
     * Get root web path
     *
     * @return string
     */
    public function getFullUploadPath()
    {
        return realpath($this->getFullWebPath().DIRECTORY_SEPARATOR.$this->getUploadPath());
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
        if (!$this->sourceFile instanceof UploadedFile) {
            return false;
        }

        if (!class_exists('Imagick')) {
            throw new AttachModelException('The requested PHP extension ext-imagick * is missing from your system.');
        }

        if (null === $fileName) {
            if (null === $ext = $this->sourceFile->guessExtension()) {
                $ext = $this->sourceFile->getExtension();
            }
            $fileName = sha1(uniqid(mt_rand(), true));
            if (null !== $ext) {
                $fileName .= '.'.$ext;
            }
        } else {
            if ($this->sourceFile->getClientOriginalExtension()) {
                $fileName .= '.'.$this->sourceFile->getClientOriginalExtension();
            } elseif ($this->sourceFile->getExtension()) {
                $fileName .= '.'.$this->sourceFile->getExtension();
            }
        }

        $fullUploadPath = $this->getFullUploadPath();
        if (!is_dir($fullUploadPath)) {
            if (!mkdir($fullUploadPath, 0775, true)) {
                throw new AttachModelException(sprintf('Not possible create UploadDirectory %s', $fullUploadPath));
            }
        }
        if (!is_writable($fullUploadPath)) {
            throw new AttachModelException(sprintf('UploadDirectory %s is not writable', $fullUploadPath));
        }

        $move = $this->resize(
            $fullUploadPath,
            $this->buildFileName($fileName, $this->getOriginalSuffix()),
            $this->widthHeight
        );

        foreach ($this->childrenWidthHeight as $suffix => $widthHeight) {
            $this->resize(
                $fullUploadPath,
                $this->buildFileName($fileName, $suffix),
                $widthHeight
            );
        }

        if (!$move) {
            try {
                $source = new \Imagick($this->sourceFile->getRealPath());
            } catch (\ImagickException $e) {
                throw new AttachModelException($e->getMessage());
            }
            $source->writeImage(
                $fullUploadPath.DIRECTORY_SEPARATOR.$this->buildFileName($fileName, $this->getOriginalSuffix())
            );
        }

        return $fileName;
    }

    /**
     * @param string $fileName
     * @param string $suffix
     * @return string
     */
    protected function buildFileName($fileName, $suffix)
    {
        if ((string)$suffix !== '') {
            $arr = explode('.', $fileName);
            $ext = array_pop($arr);
            $fileName = implode('.', $arr).'_'.$suffix.'.'.$ext;
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

        try {
            $source = new \Imagick(realpath($this->sourceFile->getRealPath()));
        } catch (\ImagickException $e) {
            throw new AttachModelException($e->getMessage());
        }
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

            $defaultBaseSide = $this->defaultBaseSide;
            switch ($defaultBaseSide) {
                case 'largest_side':
                    $defaultBaseSide = $width > $height ? 'width' : 'height';
                    break;
                case 'least_side':
                    $defaultBaseSide = $width < $height ? 'width' : 'height';
                    break;
            }

            if ('width' == $defaultBaseSide) {
                $source->thumbnailImage($width, 0);
                $r = (integer)(($source->getImageHeight() - $height) / 2);
                $source->cropImage($width, $height, 0, $r);
            } else {
                $source->thumbnailImage(0, $height);
                $r = (integer)(($source->getImageWidth() - $width) / 2);
                $source->cropImage($width, $height, $r, 0);
            }
            $source->writeImage($fullUploadPath.DIRECTORY_SEPARATOR.$fileName);

            return true;
        } else {
            throw new AttachModelException('The image is too small ('.$sourceWidth.'x'.$sourceHeight.')');
        }
    }
}