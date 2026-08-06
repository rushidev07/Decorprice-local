<?php
namespace Ced\Houzz\Helper\Validator;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
     /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function getSchema()
    {
        return realpath(__DIR__ . '/../../etc/houzz/mp/MPItemFeed.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return null
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
