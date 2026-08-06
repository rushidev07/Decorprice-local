<?php

namespace Unirgy\RapidFlow\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Unirgy\RapidFlow\DirectoryCodeException;
use Unirgy\RapidFlow\Exception;

class File
{
    protected $readByCode = [];
    protected $writeByCode = [];
    protected $codeByPath = [];
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem
    )
    {
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
    }

    public function getCodeByPath($path)
    {
        $findCode = false;
        if (!isset($this->codeByPath[$path])) {
            foreach ($this->getDirectoryListConfigSorted() as $code=>$value) {
                $checkPath = $this->directoryList->getPath($code);
                if (0===strpos($path, $checkPath)) {
                    $findCode = $code;
                    break;
                }
            }
            if ($findCode===false) {
                throw new DirectoryCodeException(__('Cannot resolve directory code by path.'));
            }
            $this->codeByPath[$path] = $findCode;
        }
        return $this->codeByPath[$path];
    }

    protected $directoryListConfigSorted;
    protected function getDirectoryListConfigSorted()
    {
        if (!$this->directoryListConfigSorted) {
            $this->directoryListConfigSorted = DirectoryList::getDefaultConfig();
            uasort($this->directoryListConfigSorted, function ($a, $b) {
                $aLen = strlen($a[DirectoryList::PATH]);
                $bLen = strlen($b[DirectoryList::PATH]);
                return $aLen<$bLen?-1:($aLen>$bLen?1:0);
            });
        }
        return $this->directoryListConfigSorted;
    }

    public function absoluteToRelative($path)
    {
        $code = $this->getCodeByPath($path);
        $stripPath = $this->directoryList->getPath($code);
        $relative = ltrim(substr($path, strlen($stripPath)), '/');
        return $relative;
    }

    public function getReadByCode($code)
    {
        if (!isset($this->readByCode[$code])) {
            $this->readByCode[$code] = $this->filesystem->getDirectoryRead($code);
        }
        return $this->readByCode[$code];
    }

    /**
     * @param $code
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getWriteByCode($code)
    {
        if (!isset($this->writeByCode[$code])) {
            $this->writeByCode[$code] = $this->filesystem->getDirectoryWrite($code);
        }
        return $this->writeByCode[$code];
    }

    /**
     * @param $path
     * @return Filesystem\Directory\ReadInterface|mixed
     * @throws DirectoryCodeException
     */
    public function getReadByPath($path)
    {
        $code = $this->getCodeByPath($path);
        return $this->getReadByCode($code);
    }

    /**
     * @param $path
     * @return Filesystem\Directory\WriteInterface
     * @throws DirectoryCodeException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getWriteByPath($path)
    {
        $code = $this->getCodeByPath($path);
        return $this->getWriteByCode($code);
    }

    public function isDir($path)
    {
        $write = $this->getWriteByPath($path);
        $relPath = $this->absoluteToRelative($path);
        return $write->isDirectory($relPath);
    }

    public function isFileExists($path)
    {
        $write = $this->getWriteByPath($path);
        $relPath = $this->absoluteToRelative($path);
        return $write->isExist($relPath);
    }

    public function isReadable($path)
    {
        $write = $this->getWriteByPath($path);
        $relPath = $this->absoluteToRelative($path);
        return $write->isReadable($relPath);
    }

    public function fileSize($path)
    {
        $write = $this->getWriteByPath($path);
        $relPath = $this->absoluteToRelative($path);
        return $write->stat($relPath)['size'];
    }

    public function filePutContents($path, $content)
    {
        $write = $this->getWriteByPath($path);
        $relPath = $this->absoluteToRelative($path);
        return $write->writeFile($relPath, $content);
    }

    public function unlink($path)
    {
        $write = $this->getWriteByPath($path);
        $relPath = $this->absoluteToRelative($path);
        if (!empty($relPath)) {
            return $write->delete($this->absoluteToRelative($path));
        } else {
            return false;
        }
    }

    public function copy($fromFilename, $toFilename)
    {
        $fromWrite = $this->getWriteByPath($fromFilename);
        $toWrite = $this->getWriteByPath($toFilename);
        $relFromPath = $this->absoluteToRelative($fromFilename);
        $relToPath = $this->absoluteToRelative($toFilename);
        $content = $fromWrite->readFile($relFromPath);
        return $toWrite->writeFile($relToPath, $content);
    }

    static public function filterObjectsInDump($data, $simple = false)
    {
        $result = '';
        if (is_array($data) || $data instanceof \Magento\Framework\Data\Collection) {
            $result = [];
            foreach ($data as $k => $v) {
                if ($v instanceof \Magento\Framework\DataObject) {
                    $_v = self::filterObjectsInDump($v->getData());
                    array_unshift($_v, spl_object_hash($v));
                    array_unshift($_v, get_class($v));
                } elseif ($v instanceof \Magento\Framework\Data\Collection) {
                    $_v = self::filterObjectsInDump($v);
                } elseif (is_array($v)) {
                    $_v = self::filterObjectsInDump($v);
                } elseif (is_object($v)) {
                    if (method_exists($v, '__toString')) {
                        $_v = get_class($v) . " - " . spl_object_hash($v);
                        if (!$simple) {
                            $_v .= "\n\n" . $v;
                        }
                    } else {
                        $_v = get_class($v) . " - " . spl_object_hash($v);
                        //if (!$simple) $_v .= var_export($v,1);
                    }
                } else {
                    $_v = $v;
                }
                $result[$k] = $_v;
            }
            if ($data instanceof \Magento\Framework\Data\Collection\AbstractDb) {
                array_unshift($result, $data->getSelect() . '');
            }
            if ($data instanceof \Magento\Framework\Data\Collection) {
                array_unshift($result, spl_object_hash($data));
                array_unshift($result, get_class($data));
            }
        } elseif ($data instanceof \Magento\Framework\DataObject) {
            $result = self::filterObjectsInDump($data->getData());
            array_unshift($result, spl_object_hash($data));
            array_unshift($result, get_class($data));
        } elseif (is_object($data)) {
            if (method_exists($data, '__toString')) {
                $result = get_class($data) . " - " . spl_object_hash($data);
                if (!$simple) {
                    $result .= "\n\n" . $data;
                }
            } else {
                $result = get_class($data) . " - " . spl_object_hash($data);
                if (!$simple) {
                    $result .= var_export($data, 1);
                }
            }
        } else {
            $result = $data;
        }
        return $result;
    }

    static protected $_dtlIps = ['127.0.0.1'];

    static public function dump($data, $file, $simple = false)
    {
        //if (!in_array(@$_SERVER['REMOTE_ADDR'], self::$_dtlIps) && !in_array(@$_SERVER['HTTP_X_FORWARDED_FOR'], self::$_dtlIps)) return;
        ob_start();
        $filtered = self::filterObjectsInDump($data, $simple);
        is_array($filtered) ? print_r($filtered) : var_dump($filtered);
        file_put_contents(realpath(self::directoryList()->getPath('var')) . '/' . 'log' . '/' . $file, ob_get_clean(), FILE_APPEND);
    }

    static public function directoryList()
    {
        return ObjectManager::getInstance()->get(DirectoryList::class);
    }

}
