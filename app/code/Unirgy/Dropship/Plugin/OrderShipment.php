<?php

namespace Unirgy\Dropship\Plugin;

class OrderShipment
{
    public function beforeGetComments(\Magento\Sales\Model\Order\Shipment $subject)
    {
        if (!$subject->getUdInGetComments()
            && !$subject->getUdInGetCommentsCollection()
        ) {
            $subject->setUdInGetComments(true);
            $subject->getCommentsCollection();
        }
        $subject->unsUdInGetComments();
    }
    public function afterGetComments(\Magento\Sales\Model\Order\Shipment $subject, $result)
    {
        $newResult = $result;
        if ($result instanceof \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\Collection
            && !$subject->getUdInGetCommentsCollection()
        ) {
            $newResult = $result->getItems();
        }
        return $newResult;
    }
    public function aroundGetCommentsCollection(\Magento\Sales\Model\Order\Shipment $subject, \Closure $next, $reload = false)
    {
        $subject->setUdInGetCommentsCollection(true);
        $result = $next($reload);
        $subject->setUdInGetCommentsCollection(false);
        return $result;
    }
    public function beforeGetTracks(\Magento\Sales\Model\Order\Shipment $subject)
    {
        if (!$subject->getUdInGetTracks()
            && !$subject->getUdInGetTracksCollection()
        ) {
            $subject->setUdInGetTracks(true);
            $subject->getTracksCollection();
        }
        $subject->unsUdInGetTracks();
    }
    public function afterGetTracks(\Magento\Sales\Model\Order\Shipment $subject, $result)
    {
        $newResult = $result;
        if ($result instanceof \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
            && !$subject->getUdInGetTracksCollection()
        ) {
            $newResult = $result->getItems();
        }
        return $newResult;
    }
    public function aroundGetTracksCollection(\Magento\Sales\Model\Order\Shipment $subject, \Closure $next, $reload = false)
    {
        $subject->setUdInGetTracksCollection(true);
        $result = $next($reload);
        $subject->setUdInGetTracksCollection(false);
        return $result;
    }
}
