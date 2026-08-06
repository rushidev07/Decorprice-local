<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\Io;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Unirgy\RapidFlow\DirectoryCodeException;
use Unirgy\RapidFlow\Exception;

/**
 * Class File
 * @method File setBaseDir(string $dir)
 * @method string getBaseDir()
 * @method int getReadLength()
 * @method int getWriteLength()
 * @package Unirgy\RapidFlow\Model\Io
 */
class File extends AbstractIo
{
    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    protected $_openMode;
    protected $_filename;

    /**
     * @var FileWriteInterface
     */
    protected $_fp;
    /**
     * @var FileDriver
     */
    protected $_fileDriver;

    /**
     * @var \Unirgy\RapidFlow\Helper\File
     */
    protected $fileHelper;

    public function __construct(
        \Unirgy\RapidFlow\Helper\File $fileHelper,
        FileDriver $fileDriver,
        DirectoryList $directoryList,
        array $data = []
    )
    {
        $this->_fileDriver = $fileDriver;
        $this->_directoryList = $directoryList;
        $this->fileHelper = $fileHelper;

        parent::__construct($data);
    }

    /**
     * @param $filename
     * @param $mode
     * @return File|null
     * @throws \Exception
     */
    public function open($filename, $mode)
    {
        $filename = $this->getFilepath($filename);
        if ($this->_fp) {
            if ($this->_filename == $filename && $this->_openMode == $mode) {
                return null;
            } else {
                $this->close();
            }
        }

        $relFilename = $this->absoluteToRelative($filename);
        if ($mode=='r') {
            $this->_fp = $this->getReadByPath($filename)->openFile($relFilename);
        } else {
            $this->_fp = $this->getWriteByPath($filename)->openFile($relFilename, $mode);
        }
        if ($this->_fp === false) {
            $e = error_get_last();
            throw new \Exception(__("Unable to open the file:\n %1\n with mode: '%2',\n error: (%3)", $filename, $mode, $e['message']));
        }

        $this->_openMode = $mode;
        $this->_filename = $filename;

        return $this;
    }

    public function isOpen()
    {
        return (bool)$this->_fp;
    }

    /**
     * Close file and reset file pointer
     */
    public function close()
    {
        if (!$this->_fp) {
            return;
        }
        @$this->_fp->close();

        $this->_fp = null;
        $this->_filename = null;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        @$this->_fp->seek($offset, $whence);
        return $this;
    }

    public function tell()
    {
        return $this->_fp->tell();
    }

    public function read()
    {
        $length = $this->getReadLength();
        if ($length) {
            $data = $this->_fp->read($length);
        } else {
            $data = $this->_fp->read(1024);
        }
        return $data;
    }

    public function write($data)
    {
        /*if ($this->getWriteLength()) {
            $this->_fp->write($data, $this->getWriteLength());
        } else {
            $this->_fp->write($data);
        }*/
        $this->_fp->write($data);
        return $this;
    }

    /**
     * @param $filename
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getFilepath($filename)
    {
        return rtrim($this->dir(), '/') . '/' . ltrim($filename, '/');
    }

    protected function getCodeByPath($path)
    {
        return $this->fileHelper->getCodeByPath($path);
    }

    protected function absoluteToRelative($path)
    {
        return $this->fileHelper->absoluteToRelative($path);
    }

    /**
     * @param $path
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface|mixed
     * @throws DirectoryCodeException
     */
    protected function getReadByPath($path)
    {
        return $this->fileHelper->getReadByPath($path);
    }

    /**
     * @param $path
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     * @throws DirectoryCodeException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getWriteByPath($path)
    {
        return $this->fileHelper->getWriteByPath($path);
    }


    protected function getWriteByCode($code)
    {
        return $this->fileHelper->getWriteByCode($code);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function dir()
    {
        if (!$this->getBaseDir()) {
            $this->setBaseDir($this->_directoryList->getPath(DirectoryList::VAR_DIR).'urapidflow');
        }
        $dir = $this->getBaseDir();
        try {
            $write = $this->getWriteByPath($dir);
        } catch (DirectoryCodeException $e) {
            $dir = $this->_directoryList->getPath(DirectoryList::ROOT).$dir;
            $write = $this->getWriteByPath($dir);
        }
        if ($dir && !$write->isExist($dir)) {
            $relative = $this->absoluteToRelative($dir);
            $write->create($relative);
        }

        return $dir;
    }

    public function reset()
    {
        $filename = $this->_filename;
        $openMode = $this->_openMode;
        $this->close();
        $write = $this->getWriteByPath($filename);
        $write->delete($this->absoluteToRelative($filename));
        $this->open($filename, $openMode);
        return $this;
    }

    /**
     * Close file on object destruct
     */
    public function __destruct()
    {
        $this->close();
    }

    public function rename($newName)
    {
        $oldName = $this->_filename;
        if (!$this->isOpen()) {
            throw new Exception(__('Cannot rename once file has been released.'));
        }

        $this->close();
        $newName = $this->getFilepath($newName);

        $relOldName = $this->absoluteToRelative($oldName);
        $relNewName = $this->absoluteToRelative($newName);

        $write = $this->getWriteByPath($oldName);
        try {
            if ($write->renameFile($relOldName, $relNewName)) {
                $this->_filename = $newName;
                return true;
            }
        } catch (\Exception $e) {
            if ($write->copyFile($relOldName, $relNewName)) {
                $write->delete($relOldName);
                $this->_filename = $newName;
                return true;
            }
        }

        throw new Exception(__('Failed to rename file %1', $newName));
    }
}
