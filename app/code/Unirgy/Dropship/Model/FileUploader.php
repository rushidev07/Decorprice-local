<?php

namespace Unirgy\Dropship\Model;

class FileUploader extends \Magento\MediaStorage\Model\File\Uploader
{
    /*
    public function __construct(
        $fileId,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
    ) {
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $this->_mySetUploadFileId($fileId);
        if (!file_exists($this->_file['tmp_name'])) {
            $code = empty($this->_file['tmp_name']) ? self::TMP_NAME_EMPTY : 0;
            throw new \Exception('The file was not uploaded.', $code);
        } else {
            $this->_fileExists = true;
        }
    }
    private function _mySetUploadFileId($fileId)
    {
        if (is_array($fileId)) {
            $this->_uploadType = self::MULTIPLE_STYLE;
            $this->_file = $fileId;
        } else {
            if (empty($_FILES)) {
                throw new \Exception('$_FILES array is empty');
            }

            $file = explode('[', $fileId);

            if (count($file) > 1) {
                $topKey = array_shift($file);
                $this->_uploadType = self::MULTIPLE_STYLE;

                $fileAttributes = $_FILES[$topKey];
                $tmpVar = [];

                foreach ($fileAttributes as $attributeName => $attributeValue) {
                    $tmpVar[$attributeName] = $attributeValue;
                    foreach ($file as $subKey) {
                        $subKey = trim($subKey, ']');
                        $tmpVar[$attributeName] = $tmpVar[$attributeName][$subKey];
                    }
                }

                $fileAttributes = $tmpVar;
                $this->_file = $fileAttributes;
            } elseif (isset($_FILES[$fileId])) {
                $this->_uploadType = self::SINGLE_STYLE;
                $this->_file = $_FILES[$fileId];
            } elseif ($fileId == '') {
                throw new \Exception('Invalid parameter given. A valid $_FILES[] identifier is expected.');
            }
        }
    }
    */
}
