/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    "prototype"
    ], function(jQuery) {
    window.AWPqVoteManager = Class.create();
    window.AWPqVoteManager.prototype = {
        initialize: function (config) {
            this.config = config;
            this.containerList = $$(this.config.voterContainerSelector);

            this.voterList = [];
            this.initVoterList();
        },
        initVoterList: function () {
            var me = this;
            if (this.containerList.length > 0) {
                this.containerList.each(function (voterContainer) {
                    var voterElement = new AWPqVoter(voterContainer, me.config);
                    me.voterList.push(voterElement);
                });
            }
        }
    };

    window.AWPqVoter = Class.create();
    window.AWPqVoter.prototype = {
        initialize: function (voterContainer, config) {
            this.lock = false;
            this.config = config;
            this.voterContainer = voterContainer;

            this.likeElement = this.voterContainer.select(this.config.likeSelector).first();
            this.dislikeElement = this.voterContainer.select(this.config.dislikeSelector).first();

            this.valueElement = this.voterContainer.select(this.config.valueSelector).first();
            this.progressElement = this.voterContainer.select(this.config.progressSelector).first();

            this.initObservers();
        },

        initObservers: function () {
            var me = this;

            Event.observe(this.likeElement, 'click', function (e) {
                Event.stop(e);
                if (me.likeElement.hasClassName(me.config.disabledClass) || me.lock) {
                    return;
                }
                me.like();
            });

            Event.observe(this.dislikeElement, 'click', function (e) {
                Event.stop(e);
                if (me.dislikeElement.hasClassName(me.config.disabledClass) || me.lock) {
                    return;
                }
                me.dislike();
            });

        },

        /**
         * Get like url
         *
         * @returns {string|null}
         */
        getLikeUrl: function () {
            if (this.likeElement.getAttribute(this.config.questionAttrName)) {
                return this.config.likeQuestionUrl.replace('placeholder', this.likeElement.getAttribute(this.config.questionAttrName));
            } else if (this.likeElement.getAttribute(this.config.answerAttrName)) {
                return this.config.likeAnswerUrl.replace('placeholder', this.likeElement.getAttribute(this.config.answerAttrName));
            }
            return null;
        },

        /**
         * Get dislike url
         *
         * @returns {string|null}
         */
        getDislikeUrl: function () {
            if (this.dislikeElement.getAttribute(this.config.questionAttrName)) {
                return this.config.dislikeQuestionUrl.replace('placeholder', this.likeElement.getAttribute(this.config.questionAttrName));
            } else if (this.dislikeElement.getAttribute(this.config.answerAttrName)) {
                return this.config.dislikeAnswerUrl.replace('placeholder', this.likeElement.getAttribute(this.config.answerAttrName));
            }
            return null;
        },

        like: function () {
            var me = this;
            new Ajax.Request(
                me.getLikeUrl(),
                {
                    method: 'post',
                    parameters: {
                        'value': me.getLikeValue(),
                        'form_key': me.config.formKey,
                        'request_id': new Date().getTime()
                    },
                    loaderArea: false,
                    onCreate: me._onVotingStart.bind(me),
                    onComplete: me._onLikeVoteCompleteFn.bind(me)
                }
            );
        },

        dislike: function () {
            var me = this;
            new Ajax.Request(
                me.getDislikeUrl(),
                {
                    method: 'post',
                    parameters: {
                        'value': me.getDislikeValue(),
                        'form_key': me.config.formKey,
                        'request_id': new Date().getTime()
                    },
                    loaderArea: false,
                    onCreate: me._onVotingStart.bind(me),
                    onComplete: me._onDislikeVoteCompleteFn.bind(me)
                }
            );
        },

        updateValue: function (value) {
            this.valueElement.update(parseInt(this.valueElement.innerHTML) + value);
        },

        getLikeValue: function () {
            if (this.likeElement.hasClassName(this.config.votedLikeClass)) {
                return -1;
            }
            if (this.dislikeElement.hasClassName(this.config.votedDislikeClass)) {
                return 2;
            }
            return 1;
        },

        getDislikeValue: function () {
            if (this.dislikeElement.hasClassName(this.config.votedDislikeClass)) {
                return 1;
            }
            if (this.likeElement.hasClassName(this.config.votedLikeClass)) {
                return -2;
            }
            return -1;
        },

        _onLikeVoteCompleteFn: function (transport) {
            try {
                eval("var json = " + transport.responseText + " || {}");
            } catch (e) {
                return;
            }
            if (json.success) {
                this.updateValue(this.getLikeValue());
                this.dislikeElement.removeClassName(this.config.votedDislikeClass);
                this.likeElement.toggleClassName(this.config.votedLikeClass);
            }
            this.progressElement.hide();
            this.valueElement.show();
            this.lock = false;
        },

        _onDislikeVoteCompleteFn: function (transport) {
            try {
                eval("var json = " + transport.responseText + " || {}");
            } catch (e) {
                return;
            }
            if (json.success) {
                this.updateValue(this.getDislikeValue());
                this.likeElement.removeClassName(this.config.votedLikeClass);
                this.dislikeElement.toggleClassName(this.config.votedDislikeClass);
            }
            this.progressElement.hide();
            this.valueElement.show();
            this.lock = false;
        },

        _onVotingStart: function () {
            this.lock = true;
            this.valueElement.hide();
            this.progressElement.show();
        }
    };
});