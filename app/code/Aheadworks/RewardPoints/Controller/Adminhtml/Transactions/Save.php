<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Transactions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\RewardPoints\Model\Data\CommandInterface;

/**
 * Class Aheadworks\RewardPoints\Controller\Adminhtml\Transactions\Save
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_RewardPoints::aw_reward_points_transaction_save';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CommandInterface
     */
    private $createCommand;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param CommandInterface $createCommand
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        CommandInterface $createCommand
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->createCommand = $createCommand;
    }

    /**
     *  {@inheritDoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $data = $this->prepareData($data);
                $this->dataPersistor->set('transaction', $data);
                $this->createCommand->execute($data);
                $this->dataPersistor->clear('transaction');
                $this->messageManager->addSuccessMessage(__('You saved the transactions.'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Please select customers or confirm that they belong to the website of the current transaction'
                    )
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the transaction.')
                );
            }
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Prepare form data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        if (isset($data['customer_selections'])) {
            $data['customer_selections'] = json_decode($data['customer_selections'], true);
        }
        return $data;
    }
}
