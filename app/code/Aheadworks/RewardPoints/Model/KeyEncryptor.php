<?php
namespace Aheadworks\RewardPoints\Model;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class KeyEncryptor
 *
 * @package Aheadworks\RewardPoints\Model
 */
class KeyEncryptor
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt external key
     *
     * @param string $customerEmail
     * @param int $customerId
     * @param int $websiteId
     * @return string
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function encrypt($customerEmail, $customerId, $websiteId)
    {
        return base64_encode($this->encryptor->encrypt($customerEmail . ',' . $customerId . ',' . $websiteId));
    }

    /**
     * Decrypt external key
     *
     * @param string $key
     * @return string
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function decrypt($key)
    {
        list($email, $customerId, $websiteId) = explode(',', $this->encryptor->decrypt(base64_decode($key)));
        return ['email' => $email, 'customer_id' => $customerId, 'website_id' => $websiteId];
    }
}
