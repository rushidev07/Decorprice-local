<?php

namespace Unirgy\RapidFlow\Model;

class UrlRewriteDbStorageBase extends \Magento\UrlRewrite\Model\Storage\DbStorage
{
    protected function uProcessDuplicates($urls, $conflicts)
    {
        $incByPath = [];
        $origPathMap = [];
        /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url */
        foreach ($urls as $url) {
            $path = $url->getRequestPath();
            if (isset($origPathMap[$path])) {
                $path = $origPathMap[$path];
            }
            if (!isset($incByPath[$path])) {
                $incByPath[$path] = 0;
            }
            $altered = false;
            $newPath = str_replace(
                '.htm',
                sprintf('-%s-%s.htm', $url->getEntityId(), $incByPath[$path]),
                $path
            );
            foreach ($conflicts as $conflict) {
                if ($path == $conflict['request_path']) {
                    $url->setRequestPath($newPath);
                    $altered = true;
                }
            }
            if (!$altered && $incByPath[$path]>0) {
                $url->setRequestPath($newPath);
                $altered = true;
            }
            if ($altered) {
                $origPathMap[$newPath] = $path;
            }
            $incByPath[$path]++;
        }
        return $this;
    }
}
