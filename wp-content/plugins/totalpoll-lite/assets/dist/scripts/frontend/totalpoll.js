"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var TotalPoll;
(function (TotalPoll) {
    TotalPoll.Polls = {};
    /**
     * Utils
     */
    var Modal = /** @class */ (function () {
        function Modal() {
            var _this = this;
            this.template = "\n<style type=\"text/css\">\n    @-webkit-keyframes modalFadeIn {\n        from {\n            opacity: 0;\n        }   \n        \n        to {\n            opacity: 1;\n        } \n    }\n        \n    @keyframes modalFadeIn {\n        from {\n            opacity: 0;\n        }   \n        \n        to {\n            opacity: 1;\n        } \n    }\n        \n    @-webkit-keyframes modalFadeOut {\n        from {\n            opacity: 1;\n        }   \n        \n        to {\n            opacity: 0;\n        } \n    }\n        \n    @keyframes modalFadeOut {\n        from {\n            opacity: 1;\n        }   \n        \n        to {\n            opacity: 0;\n        } \n    }\n    \n    .totalpoll-modal {\n        position:fixed;\n        top: 0;\n        right: 0;\n        bottom: 0;\n        left: 0;\n        z-index: 999999999;\n        width: 100%;\n        height: 100%;\n        background: rgba(0,0,0,0.9);\n        display: none;\n        -webkit-box-orient: vertical;\n        -webkit-box-direction: normal;\n        -webkit-flex-direction: column;\n            -ms-flex-direction: column;\n                flex-direction: column;\n        -webkit-box-align: center;\n        -webkit-align-items: center;\n            -ms-flex-align: center;\n                align-items: center;\n        -webkit-box-pack: center;\n        -webkit-justify-content: center;\n            -ms-flex-pack: center;\n                justify-content: center;\n    }\n    \n    .totalpoll-modal.totalpoll-is-visible {\n        display: -webkit-box;\n        display: -webkit-flex;\n        display: -ms-flexbox;\n        display: flex;\n        -webkit-animation: modalFadeIn 0.2s ease-in forwards;\n                animation: modalFadeIn 0.2s ease-in forwards;\n    }    \n    \n    .totalpoll-modal.totalpoll-is-hiding {\n        display: -webkit-box;\n        display: -webkit-flex;\n        display: -ms-flexbox;\n        display: flex;\n        -webkit-animation: modalFadeOut 0.2s ease-in forwards;\n                animation: modalFadeOut 0.2s ease-in forwards;\n    }\n    \n    .totalpoll-modal-content {\n        position: relative;\n        display: -webkit-box;\n        display: -webkit-flex;\n        display: -ms-flexbox;\n        display: flex;\n        -webkit-box-flex: 1;\n        -webkit-flex: 1;\n            -ms-flex: 1;\n                flex: 1;\n        width: 100%;\n        height: 100%;\n        max-width: 100vh;\n        max-width: 100%;\n        max-height: 100vh;\n        max-height: 100%;\n        -webkit-box-sizing: border-box;\n                box-sizing: border-box;\n        -webkit-box-align: center;\n        -webkit-align-items: center;\n            -ms-flex-align: center;\n                align-items: center;\n        -webkit-box-pack: center;\n        -webkit-justify-content: center;\n            -ms-flex-pack: center;\n                justify-content: center;\n    }\n    \n    @media (min-width:768px){\n        .totalpoll-modal-content {\n            max-width: 85vh;\n            max-width: 85%;\n            max-height: 85vh;\n            max-height: 85%;\n            padding: 2em;\n        }\n    }\n    \n    .totalpoll-modal-content img {\n        max-width: 100%!important;\n        max-height: 100%!important;\n        width: auto!important;\n        height: auto!important;\n    }\n        \n    .totalpoll-modal-content .totalpoll-embed-type-image {\n        position: absolute;\n        top: 0;\n        right: 0;\n        bottom: 0;\n        left: 0;\n        display: -webkit-box;\n        display: -webkit-flex;\n        display: -ms-flexbox;\n        display: flex;\n        -webkit-box-align: center;\n        -webkit-align-items: center;\n            -ms-flex-align: center;\n                align-items: center;\n        -webkit-box-pack: center;\n        -webkit-justify-content: center;\n            -ms-flex-pack: center;\n                justify-content: center;\n        margin: auto;\n        text-align: center;\n    }\n    \n    .totalpoll-modal-content .totalpoll-embed-type-video {\n        position: relative;\n        padding-bottom: 56.25%;\n        overflow: hidden;\n        max-width: 100%;\n        width: 100%;\n    }\n    \n    .totalpoll-modal-content .totalpoll-embed-type-video iframe, .totalpoll-modal-content .totalpoll-embed-type-video object, .totalpoll-modal-content .totalpoll-embed-type-video embed,.totalpoll-modal-content .totalpoll-embed-type-video video, .totalpoll-modal-content .totalpoll-embed-type-video audio {\n        position: absolute;\n        top: 0;\n        right: 0;\n        bottom: 0;\n        left: 0;\n        width: 100%;\n        height: 100%;\n    }\n    \n    .totalpoll-modal-close {\n        position: absolute;\n        top: 1em;\n        right: 1em;\n        width: 3em;\n        height: 3em;\n        padding: 1em;\n        border: 1px solid rgba(255, 255, 255, 0.1);\n        -webkit-border-radius: 50%;\n                border-radius: 50%;\n        fill: white;\n        cursor: pointer;\n        background: rgba(0, 0, 0, 0.5);\n        -webkit-box-sizing: border-box;\n                box-sizing: border-box;\n    }\n    \n    .totalpoll-modal-close svg {\n        vertical-align: top;\n        opacity: 0.5;\n        -webkit-transition: 0.2s all ease-out;\n        -o-transition: 0.2s all ease-out;\n        transition: 0.2s all ease-out;\n    }\n    \n    .totalpoll-modal-close:hover svg {\n        opacity: 1;\n        -webkit-transform: scale(1.1);\n            -ms-transform: scale(1.1);\n                transform: scale(1.1);\n    }\n    \n    .totalpoll-modal-close:active svg {\n        -webkit-transform: scale(0.8);\n            -ms-transform: scale(0.8);\n                transform: scale(0.8);\n    }\n    \n</style>\n<div class=\"totalpoll-modal\" role=\"dialog\">\n    <div class=\"totalpoll-modal-content\"></div>\n    <div class=\"totalpoll-modal-close\" role=\"button\" aria-label=\"Close\">\n        <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"5 5 14 14\"><path fill=\"none\" d=\"M0 0h24v24H0V0z\"/><path d=\"M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z\"/></svg>\n    </div>\n</div>";
            this.create();
            this.appendToBody();
            this.element.on('click', function (event) {
                if (jQuery(event.srcElement).hasClass('totalpoll-modal') || jQuery(event.srcElement).hasClass('totalpoll-modal-content')) {
                    _this.hide();
                }
            });
            this.element.find('.totalpoll-modal-close').on('click', function () {
                _this.hide();
            });
        }
        Modal.prototype.appendToBody = function () {
            jQuery(document.body).append(this.element);
        };
        Modal.prototype.create = function () {
            this.element = jQuery(this.template);
        };
        Modal.prototype.hide = function () {
            var _this = this;
            this.element.removeClass('totalpoll-is-visible');
            this.element.addClass('totalpoll-is-hiding');
            jQuery(document).unbind('keydown.totalpoll-modal');
            setTimeout(function () {
                _this.element.removeClass('totalpoll-is-hiding');
                document.documentElement.style.overflowY = 'initial';
                _this.element.find('.totalpoll-modal-content').html('');
            }, 200);
        };
        Modal.prototype.remove = function () {
            this.element.remove();
        };
        Modal.prototype.setContent = function (content) {
            this.element.find('.totalpoll-modal-content').html(content);
        };
        Modal.prototype.show = function () {
            var _this = this;
            document.documentElement.style.overflowY = 'hidden';
            this.element.addClass('totalpoll-is-visible');
            jQuery(document).one('keydown.totalpoll-modal', function (event) {
                if (event.which === 27) {
                    _this.hide();
                }
            });
        };
        return Modal;
    }());
    var AjaxBehaviour = /** @class */ (function () {
        function AjaxBehaviour(poll) {
            var _this = this;
            this.poll = poll;
            this.loader = jQuery("\n            <style>\n            .totalpoll-wrapper { \n                position: relative; \n            }\n            \n            @-webkit-keyframes totalpoll-fade-in {\n                form {\n                    opacity: 0;\n                }\n                to {\n                    opacity: 1;\n                }\n            }\n            \n            @keyframes totalpoll-fade-in {\n                form {\n                    opacity: 0;\n                }\n                to {\n                    opacity: 1;\n                }\n            }\n            \n            .totalpoll-form-loading {\n                position: absolute;\n                top: 0;\n                left: 0;\n                right: 0;\n                bottom: 0;\n                width: 100%;\n                height: 100%;\n                display: -webkit-box;\n                display: -webkit-flex;\n                display: -ms-flexbox;\n                display: flex;\n                -webkit-box-orient: vertical;\n                -webkit-box-direction: normal;\n                -webkit-flex-direction: column;\n                    -ms-flex-direction: column;\n                        flex-direction: column;\n                -webkit-box-pack: center;\n                -webkit-justify-content: center;\n                    -ms-flex-pack: center;\n                        justify-content: center;\n                -webkit-box-align: center;\n                -webkit-align-items: center;\n                    -ms-flex-align: center;\n                        align-items: center;\n                background-color: rgba(255,255,255, 0.3);\n                opacity: 0;\n                -webkit-animation: totalpoll-fade-in 300ms ease-in forwards;\n                        animation: totalpoll-fade-in 300ms ease-in forwards;\n            }\n            </style>\n<div class=\"totalpoll-form-loading\">\n    <svg width=\"38\" height=\"38\" viewBox=\"0 0 38 38\" xmlns=\"http://www.w3.org/2000/svg\" stroke=\"#000000\">\n    <g fill=\"none\" fill-rule=\"evenodd\">\n        <g transform=\"translate(1 1)\" stroke-width=\"2\">\n            <circle stroke-opacity=\".5\" cx=\"18\" cy=\"18\" r=\"18\"/>\n            <path d=\"M36 18c0-9.94-8.06-18-18-18\">\n                <animateTransform\n                    attributeName=\"transform\"\n                    type=\"rotate\"\n                    from=\"0 18 18\"\n                    to=\"360 18 18\"\n                    dur=\"1s\"\n                    repeatCount=\"indefinite\"/>\n            </path>\n        </g>\n    </g>\n</svg>\n</div>");
            poll.element.find('[type="submit"]').on('click', function (event) {
                poll.element.find('[name="totalpoll[action]"]').val(this.value);
            });
            poll.element.find('form').on('submit', function (event) {
                _this.load(_this.poll.config.ajaxEndpoint, new FormData(event.currentTarget), 'POST');
                event.preventDefault();
            });
        }
        AjaxBehaviour.prototype.destroy = function () {
        };
        AjaxBehaviour.prototype.load = function (url, data, method) {
            var _this = this;
            if (data === void 0) { data = {}; }
            if (method === void 0) { method = 'GET'; }
            this.poll.element.css('pointer-events', 'none');
            this.poll.element.parent().css('min-height', this.poll.element.parent().outerHeight());
            this.poll.element.prepend(this.loader);
            this.poll.transition.out(function () {
                jQuery.ajax({
                    url: url,
                    data: data,
                    processData: method != 'POST',
                    contentType: false,
                    type: method,
                })
                    .done(function (response) {
                    var $poll = jQuery(response);
                    $poll.find('.totalpoll-container').hide();
                    _this.poll.transition.out(function () {
                        _this.poll.element.replaceWith($poll);
                        var poll = new TotalPoll.Poll($poll, true, _this.poll.config['behaviours']['async']);
                        poll.transition.in(function () {
                            poll.element.parent().css('min-height', 0);
                        });
                    });
                })
                    .fail(function (error) {
                    _this.poll.element.removeAttr('style');
                    console.error(_this.poll.config['i18n']['Something went wrong! Please try again.'], error);
                });
            });
        };
        return AjaxBehaviour;
    }());
    var SliderBehaviour = /** @class */ (function () {
        //@PRO
        function SliderBehaviour(poll) {
            this.poll = poll;
            this.hasCustomFields = false;
            this.currentSlide = 0;
            if (poll.screen === 'vote' || poll.screen === 'results') {
                poll.element.find('.totalpoll-form-custom-fields').attr('totalpoll-valid-selection', 'true');
                this.setupButtons(poll.element.find('.totalpoll-buttons'), poll.element.find('.totalpoll-button'));
                this.setupSlides(poll.element.find('.totalpoll-question, .totalpoll-form-custom-fields:has(input)'));
            }
        }
        SliderBehaviour.prototype.destroy = function () {
        };
        SliderBehaviour.prototype.isFirstSlide = function () {
            return this.currentSlide === 0;
        };
        SliderBehaviour.prototype.isLastSlide = function () {
            return this.$slides.length === this.currentSlide + 1;
        };
        SliderBehaviour.prototype.isValidSelection = function () {
            return this.$currentSlide.is('[totalpoll-valid-selection="true"]');
        };
        SliderBehaviour.prototype.nextSlide = function (event) {
            if (this.isLastSlide()) {
                return;
            }
            else {
                event.preventDefault();
                this.setSlide(this.currentSlide + 1);
                this.scroll();
            }
        };
        SliderBehaviour.prototype.previousSlide = function (event) {
            if (this.isFirstSlide()) {
                return;
            }
            else {
                event.preventDefault();
                this.setSlide(this.currentSlide - 1);
                this.scroll();
            }
        };
        SliderBehaviour.prototype.refreshButtons = function () {
            this.$buttons.hide();
            this.$nextButton[this.isLastSlide() || (this.poll.behaviours['oneClick'] && this.poll.screen === 'vote') ? 'hide' : 'show']();
            this.$previousButton[this.isFirstSlide() || (this.poll.behaviours['oneClick'] && this.poll.screen === 'vote') ? 'hide' : 'show']();
            this.$buttons[this.isLastSlide() && (!this.poll.behaviours['oneClick'] || this.poll.screen === 'vote') ? 'show' : 'hide']();
            if (this.poll.screen === 'vote') {
                this.$voteButton.prop('disabled', !this.isValidSelection());
                this.$nextButton.prop('disabled', !this.isValidSelection());
                if (this.poll.behaviours['oneClick']) {
                    this.$voteButton[this.hasCustomFields && this.isLastSlide() ? 'show' : 'hide']();
                    this.$resultsButton.show();
                }
            }
        };
        SliderBehaviour.prototype.setSlide = function (index) {
            var _this = this;
            this.$slides.eq(this.currentSlide).fadeOut(500, function () {
                _this.currentSlide = index;
                _this.$currentSlide = _this.$slides.eq(_this.currentSlide);
                _this.$currentSlide.fadeIn();
                _this.refreshButtons();
                if (_this.poll.config.scrollUp) {
                    jQuery('html, body').animate({ scrollTop: _this.poll.element.offset().top - 100 }, 1000);
                }
            });
        };
        SliderBehaviour.prototype.setupButtons = function (container, buttons) {
            var _this = this;
            this.$buttons = buttons;
            this.$voteButton = this.$buttons.filter('button[value="vote"]');
            this.$resultsButton = this.$buttons.filter('button[value="results"]');
            this.$previousButton = jQuery("<button type=\"button\" class=\"totalpoll-button totalpoll-buttons-slider-previous\">" + this.poll.config['i18n']['Previous'] + "</button>");
            this.$nextButton = jQuery("<button type=\"button\" class=\"totalpoll-button totalpoll-buttons-slider-next\">" + this.poll.config['i18n']['Next'] + "</button>");
            container.prepend(this.$previousButton);
            container.append(this.$nextButton);
            if (this.poll.behaviours['oneClick'] && this.poll.screen === 'vote') {
                if (this.$resultsButton) {
                    this.$resultsButton.show();
                }
                this.$voteButton.hide();
                this.$previousButton.hide();
                this.$nextButton.hide();
            }
            if (this.poll.screen === 'results' && !this.poll.element.find('.totalpoll-question').length) {
                this.$previousButton.hide();
                this.$nextButton.hide();
            }
            this.$voteButton.on('click', function (event) { return _this.nextSlide(event); });
            this.$previousButton.on('click', function (event) { return _this.previousSlide(event); });
            this.$nextButton.on('click', function (event) { return _this.nextSlide(event); });
        };
        SliderBehaviour.prototype.setupSlides = function (slides) {
            var _this = this;
            this.hasCustomFields = slides.is('.totalpoll-form-custom-fields');
            this.$slides = slides;
            this.$slides.hide();
            var $questions = this.$slides.filter('.totalpoll-question');
            $questions.each(function (index, element) {
                var $question = jQuery(element);
                $question.append("<div class=\"totalpoll-question-number\">" + (index + 1) + " " + _this.poll.config['i18n']['of'] + " " + $questions.length + "</div>");
                $question.on('change', 'input[type="checkbox"], input[type="radio"]', function (event) {
                    _this.refreshButtons();
                });
            });
            this.setSlide(0);
            // Move to custom fields if there are errors
            if (this.$slides.last().find('.totalpoll-form-field-errors-item').length) {
                this.setSlide(this.$slides.length - 1);
            }
        };
        SliderBehaviour.prototype.scroll = function () {
            if (this.poll.config['behaviours']['scrollUp']) {
                jQuery('html, body').animate({ scrollTop: this.poll.element.offset().top - 100 }, 1000);
            }
        };
        return SliderBehaviour;
    }());
    var OneClickBehaviour = /** @class */ (function () {
        //@PRO
        function OneClickBehaviour(poll) {
            var $voteButton = poll.element.find('.totalpoll-buttons-vote');
            $voteButton.hide();
            poll.element.find('.totalpoll-question input[type="checkbox"], .totalpoll-question input[type="radio"]').on('change', function (event) {
                $voteButton.prop('disabled', false).trigger('click');
            });
        }
        OneClickBehaviour.prototype.destroy = function () {
        };
        return OneClickBehaviour;
    }());
    var ScrollUpBehaviour = /** @class */ (function () {
        function ScrollUpBehaviour(poll) {
            if (poll.isViaAjax() && !poll.isViaAsync()) {
                setTimeout(function () {
                    jQuery('html, body').animate({ scrollTop: poll.element.offset().top - 100 }, 1000);
                }, 200);
            }
        }
        ScrollUpBehaviour.prototype.destroy = function () {
        };
        return ScrollUpBehaviour;
    }());
    var EmbedResizingBehaviour = /** @class */ (function () {
        function EmbedResizingBehaviour(poll) {
            var _this = this;
            this.poll = poll;
            this.listener = function (event) { return _this.receiveRequest(event); };
            window.addEventListener("message", this.listener, false);
        }
        EmbedResizingBehaviour.prototype.destroy = function () {
            window.removeEventListener("message", this.listener);
        };
        EmbedResizingBehaviour.prototype.postHeight = function () {
            top.postMessage({
                totalpoll: {
                    id: this.poll.id,
                    action: 'resizeHeight',
                    value: jQuery(document.body).height()
                }
            }, '*');
        };
        EmbedResizingBehaviour.prototype.receiveRequest = function (event) {
            if (event.data.totalpoll && event.data.totalpoll.id === this.poll.id && event.data.totalpoll.action === 'requestHeight') {
                this.postHeight();
            }
        };
        return EmbedResizingBehaviour;
    }());
    var SelectionBehaviour = /** @class */ (function () {
        function SelectionBehaviour(poll) {
            this.poll = poll;
            poll.element.find('.totalpoll-question input[type="checkbox"], .totalpoll-question input[type="radio"]').on('change', function (event) {
                var valid = false;
                var $this = jQuery(this);
                var $question = $this.closest('.totalpoll-question');
                var $otherChoice = $question.find('.totalpoll-question-choices-other');
                var $otherChoiceField = $otherChoice.find('input[type="text"]');
                var minSelection = parseInt($question.attr('totalpoll-min-selection')) || 0;
                var maxSelection = parseInt($question.attr('totalpoll-max-selection')) || 1;
                $question.find('.totalpoll-question-choices-item').removeClass('totalpoll-question-choices-item-checked');
                $question.find('.totalpoll-question-choices-item:has(input:checked)').addClass('totalpoll-question-choices-item-checked');
                var $checked = $question.find('input[type="checkbox"]:checked, input[type="radio"]:checked');
                var $unchecked = $question.find('input[type="checkbox"]:not(:checked), input[type="radio"]:not(:checked)');
                if (maxSelection == 1) {
                    valid = Boolean($checked.length) || minSelection == 0;
                }
                else if ($checked.length >= maxSelection) {
                    $unchecked.prop('disabled', true);
                    valid = true;
                    if ($otherChoiceField.length && !$otherChoiceField.val().trim() && !$this.parents('.totalpoll-question-choices-other').length) {
                        $otherChoice.hide();
                    }
                }
                else {
                    $unchecked.prop('disabled', false);
                    valid = $checked.length >= minSelection;
                    if ($otherChoiceField.length && !$otherChoiceField.val().trim()) {
                        $otherChoice.show();
                    }
                }
                $question.attr('totalpoll-valid-selection', valid ? 'true' : 'false');
                $question.find('.totalpoll-question-choices-item').removeClass('totalpoll-question-choices-item-disabled');
                $question.find('.totalpoll-question-choices-item:has(input:disabled)').addClass('totalpoll-question-choices-item-disabled');
            });
            poll.element.find('.totalpoll-question input[type="text"]').on('focus', function (event) {
                jQuery(this).closest('label').find('input[type="checkbox"], input[type="radio"]').prop('checked', true).change();
            });
            poll.element.find('.totalpoll-question input[type="text"]').on('blur', function (event) {
                jQuery(this).closest('label').find('input[type="checkbox"], input[type="radio"]').prop('checked', Boolean(this.value.trim())).change();
            });
            poll.element.find('.totalpoll-question input[type="checkbox"], .totalpoll-question input[type="radio"]').change();
        }
        SelectionBehaviour.prototype.destroy = function () {
        };
        return SelectionBehaviour;
    }());
    var ModalBehaviour = /** @class */ (function () {
        function ModalBehaviour(poll) {
            var _this = this;
            this.poll = poll;
            this.modal = new Modal();
            jQuery(poll.element).on('click', '[totalpoll-modal]', function (event) {
                var selector = jQuery(event.currentTarget).attr('totalpoll-modal');
                if (selector) {
                    _this.modal.setContent(jQuery(selector).clone());
                }
                _this.modal.show();
                event.preventDefault();
            });
        }
        ModalBehaviour.prototype.destroy = function () {
            this.modal.remove();
        };
        return ModalBehaviour;
    }());
    var reCaptchaBehaviour = /** @class */ (function () {
        function reCaptchaBehaviour(poll) {
            var _this = this;
            this.poll = poll;
            this.valid = false;
            this.$recaptcha = poll.element.find('.g-recaptcha');
            this.$form = poll.element.find('form');
            this.invisible = this.$recaptcha.data('size') === 'invisible';
            this.widget = this.invisible ? this.$form.find('[type="submit"][value="vote"]').get(0) : this.$recaptcha.get(0);
            // Invisible
            if (this.invisible) {
                this.$form.on('submit', function (event) { return _this.validate(event); });
                window['grecaptcha'].ready(function () { return _this.render(); });
            }
            else if (this.poll.isViaAjax()) {
                this.render();
            }
        }
        reCaptchaBehaviour.prototype.destroy = function () {
            this.$recaptcha.remove();
        };
        reCaptchaBehaviour.prototype.render = function () {
            var _this = this;
            window['grecaptcha'].render(this.widget, {
                sitekey: this.$recaptcha.data('sitekey'),
                callback: function () {
                    _this.valid = true;
                    if (_this.invisible) {
                        _this.$form.trigger('submit');
                    }
                }
            });
        };
        reCaptchaBehaviour.prototype.validate = function (event) {
            if (!this.valid) {
                event.preventDefault();
            }
        };
        return reCaptchaBehaviour;
    }());
    var CountUpBehaviour = /** @class */ (function () {
        function CountUpBehaviour(poll) {
            var _this = this;
            this.poll = poll;
            poll.element.find('[totalpoll-count-up]').each(function (index, element) {
                // Prepare arguments
                var $element = jQuery(element);
                var countTo = Number($element.attr('totalpoll-count-up'));
                var format = $element.attr('totalpoll-count-format') || "{counter}";
                var pad = Number($element.attr('totalpoll-count-pad')) || 2;
                // Start
                _this.start($element, countTo, format, pad);
            });
        }
        CountUpBehaviour.prototype.destroy = function () {
        };
        CountUpBehaviour.prototype.start = function (el, countTo, format, pad) {
            if (pad === void 0) { pad = 2; }
            jQuery({ counter: 0, format: format, pad: pad, el: el })
                .animate({ counter: countTo }, {
                duration: 900,
                step: this.step,
                complete: this.step
            });
        };
        CountUpBehaviour.prototype.step = function () {
            var formatedCounter = this['format'].replace('{counter}', CountUpBehaviour.padStart(Math.round(this['counter']), this['pad']));
            this['el'].text(formatedCounter);
        };
        CountUpBehaviour.padStart = function (number, targetLength, padString) {
            if (padString === void 0) { padString = "0"; }
            targetLength = targetLength >> 0; //truncate if number, or convert non-number to 0;
            padString = String(typeof padString !== 'undefined' ? padString : ' ');
            if (number.length >= targetLength) {
                return String(number);
            }
            else {
                targetLength = targetLength - number.length;
                if (targetLength > padString.length) {
                    padString += padString['repeat'](targetLength / padString.length); //append to original to ensure we are longer than needed
                }
                return padString.slice(0, targetLength) + String(number);
            }
        };
        return CountUpBehaviour;
    }());
    var CustomFieldsPosition = /** @class */ (function () {
        function CustomFieldsPosition(poll) {
            this.poll = poll;
            poll.element.find('.totalpoll-questions').before(poll.element.find('.totalpoll-form-custom-fields'));
        }
        CustomFieldsPosition.prototype.destroy = function () {
        };
        return CustomFieldsPosition;
    }());
    /**
     * Transitions
     */
    var Transition = /** @class */ (function () {
        function Transition(element, duration) {
            this.element = element;
            this.duration = duration;
            this.element = jQuery(element);
        }
        Transition.prototype.getDuration = function () {
            return Number(this.duration);
        };
        Transition.prototype.getElement = function () {
            return this.element;
        };
        return Transition;
    }());
    var SimpleTransition = /** @class */ (function (_super) {
        __extends(SimpleTransition, _super);
        function SimpleTransition() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        SimpleTransition.prototype.in = function (callback, duration) {
            if (duration === void 0) { duration = this.getDuration(); }
            this.getElement().css({ 'visibility': 'visible', 'display': 'inherit' });
            if (callback) {
                callback();
            }
        };
        SimpleTransition.prototype.out = function (callback, duration) {
            if (duration === void 0) { duration = this.getDuration(); }
            this.getElement().css('visibility', 'hidden');
            if (callback) {
                callback();
            }
        };
        return SimpleTransition;
    }(Transition));
    var FadeTransition = /** @class */ (function (_super) {
        __extends(FadeTransition, _super);
        function FadeTransition() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        FadeTransition.prototype.in = function (callback, duration) {
            if (duration === void 0) { duration = this.getDuration(); }
            this.getElement().fadeIn(duration, callback);
        };
        FadeTransition.prototype.out = function (callback, duration) {
            if (duration === void 0) { duration = this.getDuration(); }
            this.getElement().fadeTo(duration, 0.2, callback);
        };
        return FadeTransition;
    }(Transition));
    var SlideTransition = /** @class */ (function (_super) {
        __extends(SlideTransition, _super);
        function SlideTransition() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        SlideTransition.prototype.in = function (callback, duration) {
            if (duration === void 0) { duration = this.getDuration(); }
            this.getElement().slideDown(duration, callback);
        };
        SlideTransition.prototype.out = function (callback, duration) {
            if (duration === void 0) { duration = this.getDuration(); }
            this.getElement().slideUp(duration, callback);
        };
        return SlideTransition;
    }(Transition));
    var Hooks = /** @class */ (function () {
        function Hooks() {
        }
        Hooks.addAction = function (event, callback) {
            jQuery(TotalPoll).on(event, function (event) {
                var payload = [].slice.call(arguments).splice(1);
                callback.apply(event, payload);
            });
        };
        Hooks.doAction = function (event, payload) {
            jQuery(TotalPoll).triggerHandler(event, payload);
        };
        return Hooks;
    }());
    TotalPoll.Hooks = Hooks;
    /**
     * Poll
     */
    var Poll = /** @class */ (function () {
        function Poll(element, viaAjax, viaAsync) {
            if (viaAjax === void 0) { viaAjax = false; }
            if (viaAsync === void 0) { viaAsync = false; }
            this.element = element;
            this.viaAjax = viaAjax;
            this.viaAsync = viaAsync;
            this.behaviours = {};
            this.config = {};
            this.screen = '';
            this.id = element.attr('totalpoll');
            this.config = JSON.parse(element.find('[totalpoll-config]').text() || jQuery("[totalpoll-config=\"" + this.id + "\"]").text());
            this.screen = element.attr('totalpoll-screen');
            if (TotalPoll.Polls[this.id]) {
                // Destroy the old instance
                TotalPoll.Polls[this.id].destroy();
            }
            TotalPoll.Polls[this.id] = this;
            element.data('poll', this);
            // Selection
            this.behaviours['selection'] = new SelectionBehaviour(this);
            // Modal
            if (this.config['behaviours']['disableModal']) {
                this.element.find('[totalpoll-modal]').remove();
                this.element.find('.has-thumbnail').removeClass('has-thumbnail');
            }
            else {
                this.behaviours['modal'] = new ModalBehaviour(this);
            }
            // Scroll up
            if (this.config['behaviours']['scrollUp']) {
                this.behaviours['scrollUp'] = new ScrollUpBehaviour(this);
            }
            // One-click
            if (this.config['behaviours']['oneClick']) {
                this.behaviours['oneClick'] = new OneClickBehaviour(this);
            }
            // Questions slider
            if (this.config['behaviours']['slider']) {
                this.behaviours['slider'] = new SliderBehaviour(this);
            }
            // Count up
            if (this.config['behaviours']['countUp']) {
                this.behaviours['countUp'] = new CountUpBehaviour(this);
            }
            // Custom fields position
            if (this.config['behaviours']['fieldsFirst']) {
                this.behaviours['fieldsFirst'] = new CustomFieldsPosition(this);
            }
            // Transition
            var transition = this.config['effects']['transition'];
            var container = this.element.find('.totalpoll-container');
            if (transition == 'fade') {
                this.transition = new FadeTransition(container, this.config['effects']['duration']);
            }
            else if (transition == 'slide') {
                this.transition = new SlideTransition(container, this.config['effects']['duration']);
            }
            else {
                this.transition = new SimpleTransition(container, this.config['effects']['duration']);
            }
            // reCaptcha
            if (window['grecaptcha'] && element.find('.g-recaptcha').length) {
                this.behaviours['recaptcha'] = new reCaptchaBehaviour(this);
            }
            // Ajax
            if (this.config['behaviours']['async'] || this.config['behaviours']['ajax']) {
                this.behaviours['ajax'] = new AjaxBehaviour(this);
            }
            // Async
            if (this.config['behaviours']['async']) {
                this.behaviours['ajax'].load(element.attr('totalpoll-ajax-url'));
            }
            // Embed
            if (window.top !== window.self) {
                this.behaviours['embed'] = new EmbedResizingBehaviour(this);
                this.behaviours['embed'].postHeight();
            }
            Hooks.doAction('totalpoll/init', this);
        }
        Poll.prototype.destroy = function () {
            jQuery.each(this.behaviours, function (id, behaviour) {
                behaviour.destroy();
            });
            Hooks.doAction('totalpoll/destroy', this);
            // this.element.remove();
        };
        Poll.prototype.isViaAjax = function () {
            return this.viaAjax;
        };
        Poll.prototype.isViaAsync = function () {
            return this.viaAsync;
        };
        return Poll;
    }());
    TotalPoll.Poll = Poll;
})(TotalPoll || (TotalPoll = {}));
jQuery(function ($) {
    $('[totalpoll]').each(function () {
        new TotalPoll.Poll($(this));
    });
});

//# sourceMappingURL=../maps/frontend/totalpoll.js.map
