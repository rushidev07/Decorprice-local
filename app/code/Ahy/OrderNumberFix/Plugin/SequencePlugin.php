<?php
namespace Ahy\OrderNumberFix\Plugin;

use Magento\SalesSequence\Model\Sequence;

class SequencePlugin
{
    /**
     * After plugin on getNextValue().
     *
     * Magento core returns something like "002025001" (padded).
     * We turn that into "2025001".
     */
    public function afterGetNextValue(Sequence $subject, $result)
    {
        // remove left padding zeroes
        $clean = ltrim($result, '0');

        // safety: if somehow result was all zeroes (shouldn't happen), fall back
        return $clean === '' ? $result : $clean;
    }
}
