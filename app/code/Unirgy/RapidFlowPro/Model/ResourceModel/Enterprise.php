<?php

namespace Unirgy\RapidFlowPro\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource\Fixed;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product\AbstractProduct;
use Unirgy\RapidFlow\Helper\Data as RfHlp;

class Enterprise
    extends Fixed
{

    protected $_translateModule = 'Unirgy_RapidFlowPro';

    protected $_dataType = 'enterprise';

    protected $_exportRowCallback = [
        'GCAH' => '_exportProcessGCAH',
        'GCA' => '_exportProcessGCA',
    ];

    protected $_attributes;

    protected function _construct()
    {
        AbstractProduct::validateLicense('Unirgy_RapidFlowPro');
        parent::_construct();
    }

    protected function _getGiftcard($code, $id=false)
    {
        $gaTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT);
        $fetchMethod = $id ? 'fetchOne' : 'fetchRow';
        $fetchCols = $id ? 'giftcardaccount_id' : '*';
        return $this->_write->$fetchMethod(
            $this->_write->select()
                ->from($gaTable, $fetchCols)
                ->where('code=?', $code)
        );
    }

    protected function _getGiftcardHistory($code, $updatedAt, $id=false)
    {
        $gaId = $this->_getGiftcard($code, true);
        $gahTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT_HISTORY);
        $fetchMethod = $id ? 'fetchOne' : 'fetchRow';
        $fetchCols = $id ? 'history_id' : '*';
        return $this->_write->$fetchMethod(
            $this->_write->select()
                ->from($gahTable, $fetchCols)
                ->where('giftcardaccount_id=?', $gaId)
                ->where('updated_at=?', $updatedAt)
        );
    }

    public function assertGCA($row)
    {
        if (count($row) < 5) {
            throw new LocalizedException(__('Invalid row format'));
        }
    }
    public function assertGCAH($row)
    {
        if (count($row) < 5) {
            throw new LocalizedException(__('Invalid row format'));
        }
    }

    public function _exportProcessGCAH(&$row)
    {
        if (is_numeric($row['action'])) $row['action'] = $this->getActionName($row['action']);

        return true;
    }

    public function _exportProcessGCA(&$row)
    {
        $row['website'] = $this->getWebsite($row['website_id'])->getCode();

        return true;
    }

    protected function _deleteRowGCA($row)
    {
        $this->assertGCA($row);

        $gaTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT);

        $code = $row[1];
        $existsId = $this->_getGiftcard($code, true);
        if ($existsId) {
            $this->_write->delete($gaTable, ["giftcardaccount_id=?"=>$existsId]);

            return self::IMPORT_ROW_RESULT_SUCCESS;
        }

        return self::IMPORT_ROW_RESULT_NOCHANGE;
    }

    protected function _deleteRowGCAH($row)
    {
        $this->assertGCAH($row);

        $gahTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT_HISTORY);

        $code = $this->_convertEncoding($row[1]);
        $updatedAt = $this->_convertEncoding($row[2]);
        $gahId = $this->_getGiftcardHistory($code, $updatedAt, true);
        if ($gahId) {
            $this->_write->delete($gahTable, ["history_id=?"=>$gahId]);

            return self::IMPORT_ROW_RESULT_SUCCESS;
        }

        return self::IMPORT_ROW_RESULT_NOCHANGE;
    }

    protected function _exportInitGCA()
    {
        if (!RfHlp::isEnterpriseEdition()) {
            $this->_initEmptySelect();
            return ;
        }

        $gaTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT);

        $this->_select = $this->_read->select()
            ->from(['main' => $gaTable]);

        $this->_exportConvertFields = ['code'];
    }

    protected function _exportInitGCAH()
    {
        if (!RfHlp::isEnterpriseEdition()) {
            $this->_initEmptySelect();
            return ;
        }

        $gaTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT);
        $gahTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT_HISTORY);

        $this->_select = $this->_read->select()
            ->from(['main' => $gaTable], ['code'])
            ->join(['h' => $gahTable], 'h.giftcardaccount_id=main.giftcardaccount_id');

        $this->_exportConvertFields = ['additional_info'];
    }

    protected function _importRowGCA($row)
    {
        $this->assertGCA($row);

        $sourceTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT);

        $code = $row[1];

        $new = [
            'code' => $this->_convertEncoding($row[1]),
            'balance' => $row[2],
            'status' => $row[3],
            'state' => $row[4],
            'is_redeemable' => $row[5],
            'date_created' => isset($row[6])&&$row[6]!=='' ? $row[6] : RfHlp::now(),
            'date_expires' => isset($row[7])&&$row[7]!=='' ? $row[7] : null,
            'website_id' => isset($row[8])&&$row[8]!==''
                ? $this->_getWebsiteId($row[8], true)
                : $this->_storeManager->getDefaultStoreView()->getWebsiteId()
        ];

        $exists = $this->_getGiftcard($code, false);

        if (!$exists) {
            $this->_write->insert($sourceTable, $new);
            return self::IMPORT_ROW_RESULT_SUCCESS;
        } else {
            $this->_write->update($sourceTable, $new, ['code=?'=>$code]);
            return self::IMPORT_ROW_RESULT_SUCCESS;
        }

    }

    protected function _importRowGCAH($row)
    {
        $this->assertGCAH($row);

        $gaTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT);
        $gahTable = $this->_t(self::TABLE_GIFTCARD_ACCOUNT_HISTORY);

        $code = $row[1];
        $updatedAt = $row[2];
        $action = $row[3];
        $gaId = $this->_getGiftcard($code, true);
        if (!$gaId) {
            $this->_profile->getLogger()->setColumn(1);
            throw new LocalizedException(__('Giftcard account not found'));
        }

        $actionNames = $this->getActionNamesArray();
        $actionId = $action;
        if (is_numeric($action)) {
            if (!array_key_exists($action, $actionNames)) {
                $this->_profile->getLogger()->setColumn(3);
                throw new LocalizedException(__('Giftcard account history action invalid'));
            }
        } else {
            $actionId = array_search($action, $actionNames);
            if (false===$actionId) {
                $this->_profile->getLogger()->setColumn(3);
                throw new LocalizedException(__('Giftcard account history action invalid'));
            }
        }

        $new = [
            'updated_at' => $row[2],
            'action' => $actionId,
            'balance_amount' => $row[4],
            'balance_delta' => $row[5],
            'additional_info' => isset($row[6])&&$row[6]!=='' ? $row[6] : null,
            'giftcardaccount_id' => $gaId
        ];

        $gahId = $this->_getGiftcardHistory($code, $updatedAt, true);

        if (!$gahId) {
            $this->_write->insert($gahTable, $new);
            return self::IMPORT_ROW_RESULT_SUCCESS;
        } else {
            unset($new['giftcardaccount_id']);
            unset($new['updated_at']);
            $this->_write->update($gahTable, $new, ['giftcardaccount_id=?'=>$gaId,'history_id=?'=>$gahId]);
            return self::IMPORT_ROW_RESULT_SUCCESS;
        }

    }

    public function getActionName($id)
    {
        $actionNames = $this->getActionNamesArray();
        if (!array_key_exists($id, $actionNames)) {
            throw new LocalizedException(__('Giftcard account history action invalid'));
        }
        return $actionNames[$id];
    }

    public function getActionNamesArray()
    {
        return [
            0 => __('Created'),
            5 => __('Updated'),
            2 => __('Sent'),
            1 => __('Used'),
            3 => __('Redeemed'),
            4 => __('Expired')
        ];
    }

}