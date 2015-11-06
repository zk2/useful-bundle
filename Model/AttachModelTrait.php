<?php

namespace Zk2\UsefulBundle\Model;

use AppBundle\Model\AttachModelException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AttachModelTrait
 */
trait AttachModelTrait
{
    /**
     * @Assert\File(
     *     maxSizeMessage = "Max file size 20Mb",
     *     maxSize = "20480k",
     *     mimeTypes = {"image/jpg", "image/jpeg", "image/gif", "image/png"},
     *     mimeTypesMessage = "Only JPG, JPEG, GIF or PNG format."
     * )
     */
    protected $totalFile;

    /**
     * @var array $width_height
     */
    protected $width_height;

    /**
     * @var array $width_preview
     */
    protected $width_height_preview;

    /**
     * @var integer $min_width
     */
    protected $min_width;

    /**
     * @var integer $min_height
     */
    protected $min_height;

    /**
     * Get upload path
     *
     * @return string
     */
    abstract protected function getUploadPath();

    /**
     * Set Widht Height
     *
     * @param integer $width
     * @param integer $height
     */
    public function setWidhtHeight($width, $height)
    {
        $this->width_height = array((integer)$width, (integer)$height);
    }

    /**
     * Set Widht Preview
     *
     * @param integer $width
     */
    public function setWidhtHeightPreview($width, $height)
    {
        $this->width_height_preview = array((integer)$width, (integer)$height);
    }

    /**
     * Set min Widht
     *
     * @param integer $width
     */
    public function setMinWidht($width)
    {
        $this->min_width = (integer)$width;
    }

    /**
     * Set min Height
     *
     * @param integer $height
     */
    public function setMinHeight($height)
    {
        $this->min_height = (integer)$height;
    }

    /**
     * Set totalFile
     *
     * @param string $totalFile
     */
    public function setTotalFile($totalFile = null)
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
     * Get web upload path
     *
     * @param null|string $uploadPath
     * @return string
     */
    public function getFullUploadPath($uploadPath = null)
    {
        return $uploadPath ?: $this->getUploadPath();
    }

    /**
     * Get root web path
     *
     * @return string
     */
    public function getRootWebPath()
    {
        return realpath(__DIR__.'/../../../../web');
    }

    /**
     * Get root web path
     *
     * @return string
     */
    public function getFullRootPath()
    {
        return $this->getRootWebPath().$this->getFullUploadPath();
    }

    /**
     * Upload file
     *
     * @param string $uploadPath
     * @param string $fileName
     * @return bool|string
     */
    public function uploadFile($uploadPath = null, $fileName = null, $return_full = true)
    {
        if (!$this->totalFile instanceof UploadedFile) {
            return false;
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
            if(!$ext = substr($fileName, strrpos($fileName, '.') + 1)){
                if($ex = $this->totalFile->getClientOriginalExtension()) {
                    $fileName .= '.'.$ex;
                }
            }
        }

        $fullUploadPath = $this->getFullUploadPath($uploadPath);
        $rootPath = $this->getRootWebPath();
        if (!is_dir($rootPath.$fullUploadPath)) {
            mkdir($rootPath.$fullUploadPath, 0775, true);
        }

        $movePath = $rootPath.$fullUploadPath;

        $move = false;

        if ($this->width_height) {
            $move = $this->resize(
                $this->totalFile->getRealPath(),
                $movePath,
                $fileName,
                $this->width_height,
                null
            );
        }

        if (is_array($this->width_height_preview) and array_sum($this->width_height_preview)) {
            $this->resize(
                $this->totalFile->getRealPath(),
                $movePath,
                $fileName,
                $this->width_height_preview,
                'preview_'
            );
        }

        if (!$move and class_exists('Imagick')) {
            $source = new \Imagick($this->totalFile->getRealPath());
            $source->writeImage($movePath.'/'.$fileName);
        }

        return $return_full ? $fullUploadPath.'/'.$fileName : $fileName;
    }

    /**
     * Resize image
     */
    protected function resize($path, $movePath, $fileName, array $width_height, $prefix = null)
    {
        if (!is_array($this->width_height_preview) or !array_sum($width_height)) {
            return false;
        }

        if (class_exists('Imagick')) {
            $ext = substr($fileName, strrpos($fileName, '.') + 1);

            if (!in_array($ext, array('jpg', 'png', 'gif', 'jpeg'))) {
                throw new AttachModelException('Images only jpg, png, gif, jpeg --- '.$ext);
            }

            $source = new \Imagick(realpath($path));
            $source_width = $source->getImageWidth();
            $source_height = $source->getImageHeight();
            $width = $width_height[0];
            $height = $width_height[1];

            if ($width == 0 or $height == 0) {
                if ($width) {
                    $source->thumbnailImage($width, 0);
                    $source->writeImage($movePath.'/'.$prefix.$fileName);

                    return true;
                } elseif ($height) {
                    $source->thumbnailImage(0, $height);
                    $source->writeImage($movePath.'/'.$prefix.$fileName);

                    return true;
                }
            } elseif ($source_width >= $width and $source_height >= $height) {
                if ($width == $height and $source_width >= $source_height) {
                    $source->thumbnailImage(0, $height);
                    $r = (integer)(($source->getImageWidth() - $width) / 2);
                    $source->cropImage($width, $height, $r, 0);
                    $source->writeImage($movePath.'/'.$prefix.$fileName);

                    return true;
                }
                $source->thumbnailImage($width, 0);
                $r = (integer)(($source->getImageHeight() - $height) / 2);
                $source->cropImage($width, $height, 0, $r);
                $source->writeImage($movePath.'/'.$prefix.$fileName);

                return true;
            } else {
                throw new AttachModelException($source_width.'x'.$source_height);
            }
        }

        return false;
    }
}