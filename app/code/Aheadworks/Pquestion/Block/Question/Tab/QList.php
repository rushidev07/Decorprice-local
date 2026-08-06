<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Question\Tab;

class QList extends \Aheadworks\Pquestion\Block\Question\QList
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Aheadworks_Pquestion::catalog/product/view/list.phtml');
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $sorter = $this->getLayout()
            ->createBlock(\Aheadworks\Pquestion\Block\Question\Sort::class, 'aw_pq_question_sort')
            ->setTemplate('Aheadworks_Pquestion::question/sort.phtml');
        $questionForm = $this->getLayout()
            ->createBlock(\Aheadworks\Pquestion\Block\Question\Form::class, 'aw_pq_ask_question_form')
            ->setTemplate('Aheadworks_Pquestion::question/form.phtml');
        $answerForm = $this->getLayout()
            ->createBlock(\Aheadworks\Pquestion\Block\Answer\Sort::class, 'aw_pq_add_answer_form')
            ->setTemplate('Aheadworks_Pquestion::answer/form.phtml');

        $this->setChild('aw_pq_question_sort', $sorter);
        $this->setChild('aw_pq_ask_question_form', $questionForm);
        $this->setChild('aw_pq_add_answer_form', $answerForm);

        return parent::_prepareLayout();
    }
}
