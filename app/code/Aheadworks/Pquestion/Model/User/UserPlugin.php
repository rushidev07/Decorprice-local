<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\User;

class UserPlugin
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Bookmark
     */
    protected $definedBookmarkHelper;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var mixed
     */
    protected $userCollection;

    /**
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Aheadworks\Pquestion\Helper\Bookmark $bookmarkHelper
     */
    public function __construct(
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Aheadworks\Pquestion\Helper\Bookmark $bookmarkHelper
    ) {
        $this->definedBookmarkHelper = $bookmarkHelper;
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * @param \Magento\User\Model\User $subject
     * @param \Closure $proceed
     *
     * @return void
     */
    public function aroundAfterSave(
        \Magento\User\Model\User $subject,
        \Closure $proceed
    ) {
        $proceed();
        if ($subject->isObjectNew()) {
            $this->definedBookmarkHelper->proceedAll($subject);
        }
    }
}
