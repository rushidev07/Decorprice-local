<?php

namespace Unirgy\Dropship\Plugin;

class OrderShipmentCollection
{
    public function aroundLoad(
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $subject,
        \Closure $next,
        $printQuery = false,
        $logQuery = false
    ) {
    
        $shouldWalk = !$subject->isLoaded();
        $result = $next($printQuery, $logQuery);
        if ($shouldWalk) {
            foreach ($subject->getItems() as $id => $item) {
                $item->getResource()->unserializeFields($item);
            }
        }
        return $result;
    }
}
