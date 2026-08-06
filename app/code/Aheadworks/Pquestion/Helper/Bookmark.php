<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Helper;

class Bookmark
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecode;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncode;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecode
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncode
     */
    public function __construct(
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecode,
        \Magento\Framework\Json\EncoderInterface $jsonEncode
    ) {
        $this->jsonDecode = $jsonDecode;
        $this->jsonEncode = $jsonEncode;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * @param \Magento\User\Model\User $user
     *
     * @return void
     */
    public function proceedAll(\Magento\User\Model\User $user)
    {
        $pendingQuestions = $this->getPendingQuestionsBookmark($user);
        $privateQuestions = $this->getPrivateQuestionsBookmark($user);
        $newAnswers = $this->getNewAnswersBookmark($user);
        $noQuestions = $this->getNoQuestionsBookmark($user);

        $this->bookmarkRepository->save($pendingQuestions);
        $this->bookmarkRepository->save($privateQuestions);
        $this->bookmarkRepository->save($newAnswers);
        $this->bookmarkRepository->save($noQuestions);
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function getPendingQuestionsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->_createNewQuestionGridBookmark(
            $user,
            'aw_pq_question_listing',
            'aw_pq_pending_questions',
            __('Pending Questions')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'],
            [
                'status'          => ['visible' => false, 'sorting' => false],
                'visibility'      => ['visible' => false, 'sorting' => false],
                'total_answers'   => ['visible' => false, 'sorting' => false],
                'pending_answers' => ['visible' => false, 'sorting' => false],
                'helpfulness'     => ['visible' => false, 'sorting' => false],
                'actions'         => ['visible' => true, 'sorting' => false]
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'],
            [
                'status' => \Aheadworks\Pquestion\Model\Source\Question\Status::PENDING_VALUE,
                'visibility' => \Aheadworks\Pquestion\Model\Source\Question\Visibility::PUBLIC_VALUE,
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function getPrivateQuestionsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->_createNewQuestionGridBookmark(
            $user,
            'aw_pq_question_listing',
            'aw_pq_private_questions',
            __('Private Questions')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'],
            [
                'status'          => ['visible' => false, 'sorting' => false],
                'visibility'      => ['visible' => false, 'sorting' => false],
                'total_answers'   => ['visible' => false, 'sorting' => false],
                'pending_answers' => ['visible' => false, 'sorting' => false],
                'helpfulness'     => ['visible' => false, 'sorting' => false],
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'],
            [
                'status' => \Aheadworks\Pquestion\Model\Source\Question\Status::PENDING_VALUE,
                'visibility' => \Aheadworks\Pquestion\Model\Source\Question\Visibility::PRIVATE_VALUE,
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function getNewAnswersBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->_createNewQuestionGridBookmark(
            $user,
            'aw_pq_question_listing',
            'aw_pq_new_answers',
            __('New Answers')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'],
            [
                'status'        => ['visible' => false, 'sorting' => false],
                'visibility'    => ['visible' => false, 'sorting' => false],
                'total_answers' => ['visible' => false, 'sorting' => false],
                'helpfulness'   => ['visible' => false, 'sorting' => false],
                'author_name'   => ['visible' => false, 'sorting' => false],
                'created_at'    => ['visible' => false, 'sorting' => false],
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'],
            ['pending_answers' => ['from' => '1', 'to' => '']]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function getNoQuestionsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->_createNewProductGridBookmark(
            $user,
            'aw_pq_product_listing',
            'aw_pq_no_questions',
            __('No Questions')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'],
            ['product_only_questions' => ['from' => '0', 'to' => '0']]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @param mixed $namespace
     * @param mixed $identifier
     * @param mixed $title
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    protected function _createNewQuestionGridBookmark(
        \Magento\User\Model\User $user,
        $namespace,
        $identifier,
        $title
    ) {
        return $this->_createNewBookmark(
            $user,
            $namespace,
            $identifier,
            $title,
            $this->getQuestionGridDefaultConfigData()
        );
    }

    /**
     * @param \Magento\User\Model\User $user
     * @param mixed $namespace
     * @param mixed $identifier
     * @param mixed $title
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    protected function _createNewProductGridBookmark(
        \Magento\User\Model\User $user,
        $namespace,
        $identifier,
        $title
    ) {
        return $this->_createNewBookmark(
            $user,
            $namespace,
            $identifier,
            $title,
            $this->getProductGridDefaultConfigData()
        );
    }

    /**
     * @param \Magento\User\Model\User $user
     * @param mixed $namespace
     * @param mixed $identifier
     * @param mixed $title
     * @param mixed $dataConfig
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    protected function _createNewBookmark(
        \Magento\User\Model\User $user,
        $namespace,
        $identifier,
        $title,
        $dataConfig
    ) {
        $bookmark = $this->bookmarkFactory->create();
        $bookmark->addData([
            'user_id' => $user->getId(),
            'namespace' => $namespace,
            'identifier' => $identifier,
            'current' => '0',
            'title' => $title
        ]);
        $config = [
            'views' => [
                $identifier => [
                    'index' => $identifier,
                    'label' => $title,
                    'data' => $dataConfig
                ]
            ]
        ];
        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * @return array
     */
    public function getQuestionGridDefaultConfigData()
    {
        return [
            "search"    => ['value' => ''],
            "filters"   => ["placeholder" => true, 'applied' => ["placeholder" => true]],
            "columns" => [
                "content"         => ["visible" => true, "sorting" => false],
                "product_name"    => ["visible" => true, "sorting" => false],
                "total_answers"   => ["visible" => true, "sorting" => false],
                "pending_answers" => ["visible" => true, "sorting" => false],
                "helpfulness"     => ["visible" => true, "sorting" => false],
                "author_name"     => ["visible" => true, "sorting" => false],
                "status"          => ["visible" => true, "sorting" => false],
                "visibility"      => ["visible" => true, "sorting" => false],
                "ids"             => ["visible" => true, "sorting" => false],
                "actions"         => ["visible" => false, "sorting" => false],
                "store_id"        => ["visible" => true, "sorting" => false],
                "created_at"      => ["visible" => true, "sorting" => false]
            ],
            "paging"    => [
                "options" => [
                    "20"  => ["value" => 20, "label" => 20],
                    "30"  => ["value" => 30, "label" => 30],
                    "50"  => ["value" => 50, "label" => 50],
                    "100" => ["value" => 100, "label" => 100],
                    "200" => ["value" => 200, "label" => 200]
                ],
                "value"   => 20
            ],
            "displayMode" => "grid",
            "positions" => [
                "ids"             => 0, "content" => 1, "product_name" => 2,
                "status"          => 3, "visibility" => 4, "total_answers" => 5,
                "pending_answers" => 6, "helpfulness" => 7, "author_name" => 8,
                "created_at"      => 9, "store_id" => 10, "actions" => 11
            ]
        ];
    }

    /**
     * @return array
     */
    public function getProductGridDefaultConfigData()
    {
        return [
            "search"    => "",
            "filters"   => ["placeholder" => true, 'applied' => ["placeholder" => true]],
            "columns" => [
                "name"                   => ["visible" => true, "sorting" => false],
                "sku"                    => ["visible" => true, "sorting" => false],
                "total_questions"        => ["visible" => true, "sorting" => false],
                "shared_questions"       => ["visible" => true, "sorting" => false],
                "product_only_questions" => ["visible" => true, "sorting" => false],
                "actions"                => ["visible" => true, "sorting" => false],
            ],
            "paging"    => [
                "options" => [
                    "20"  => ["value" => 20, "label" => 20],
                    "30"  => ["value" => 30, "label" => 30],
                    "50"  => ["value" => 50, "label" => 50],
                    "100" => ["value" => 100, "label" => 100],
                    "200" => ["value" => 200, "label" => 200]
                ],
                "value"   => 20
            ],
            "displayMode" => "grid",
            "positions" => [
                "name"             => 0, "sku" => 1, "total_questions" => 2,
                "shared_questions" => 3, "product_only_questions" => 4, "actions" => 5,
            ]
        ];
    }
}
