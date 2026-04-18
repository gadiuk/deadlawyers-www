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
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
/**
 * Decorators.
 */
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        /**
         * A small helper to inject dependencies dynamically.
         *
         * @param func
         */
        function annotate(func) {
            var $injector = angular.injector(['ng']);
            func.$inject = $injector.annotate(func).map(function (member) { return member.replace(/^_/, ''); });
        }
        /**
         * Injectable decorator.
         *
         * @returns {(Entity: any) => void}
         * @constructor
         */
        function Injectable() {
            return function (Entity) {
                annotate(Entity);
            };
        }
        Common.Injectable = Injectable;
        /**
         * Service decorator.
         *
         * @param {string} moduleName
         * @returns {(Service: any) => void}
         * @constructor
         */
        function Service(moduleName) {
            return function (Service) {
                var module;
                var name = Service.name;
                var isProvider = Service.hasOwnProperty('$get');
                annotate(Service);
                try {
                    module = angular.module(moduleName);
                }
                catch (exception) {
                    module = angular.module(moduleName, []);
                }
                module[isProvider ? 'provider' : 'service'](name, Service);
            };
        }
        Common.Service = Service;
        /**
         * Factory decorator.
         *
         * @param {string} moduleName
         * @param selector
         * @returns {(Factory: any) => void}
         * @constructor
         */
        function Factory(moduleName, selector) {
            return function (Factory) {
                var module;
                var name = selector || ("" + Factory.name.charAt(0).toLowerCase() + Factory.name.slice(1)).replace('Factory', '');
                annotate(Factory);
                try {
                    module = angular.module(moduleName);
                }
                catch (exception) {
                    module = angular.module(moduleName, []);
                }
                module.factory(name, Factory);
            };
        }
        Common.Factory = Factory;
        /**
         * Controller decorator.
         *
         * @param {string} moduleName
         * @returns {(Controller: any) => void}
         * @constructor
         */
        function Controller(moduleName) {
            return function (Controller) {
                var module;
                var name = Controller.name;
                annotate(Controller);
                try {
                    module = angular.module(moduleName);
                }
                catch (exception) {
                    module = angular.module(moduleName, []);
                }
                module.controller(name, Controller);
            };
        }
        Common.Controller = Controller;
        /**
         * Filter decorator.
         *
         * @param {string} moduleName
         * @param selector
         * @returns {(Filter: any) => void}
         * @constructor
         */
        function Filter(moduleName, selector) {
            return function (Filter) {
                var module;
                var name = selector || ("" + Filter.name.charAt(0).toLowerCase() + Filter.name.slice(1)).replace('Filter', '');
                annotate(Filter);
                try {
                    module = angular.module(moduleName);
                }
                catch (exception) {
                    module = angular.module(moduleName, []);
                }
                module.filter(name, Filter);
            };
        }
        Common.Filter = Filter;
        /**
         * Component decorator.
         *
         * @param moduleName
         * @param {angular.IComponentOptions} options
         * @param {any} selector
         * @returns {(Class: any) => void}
         * @constructor
         */
        function Component(moduleName, options, selector) {
            if (selector === void 0) { selector = null; }
            return function (Class) {
                var module;
                selector = selector || ("" + Class.name.charAt(0).toLowerCase() + Class.name.slice(1)).replace('Component', '');
                options.controller = Class;
                annotate(Class);
                try {
                    module = angular.module(moduleName);
                }
                catch (exception) {
                    module = angular.module(moduleName, []);
                }
                module.component(selector, options);
            };
        }
        Common.Component = Component;
        /**
         * Directive decorator.
         *
         * @param moduleName
         * @param {any} selector
         * @returns {(Class: any) => void}
         * @constructor
         */
        function Directive(moduleName, selector) {
            if (selector === void 0) { selector = null; }
            return function (Class) {
                var module;
                selector = selector || ("" + Class.name.charAt(0).toLowerCase() + Class.name.slice(1)).replace('Directive', '');
                annotate(Class);
                try {
                    module = angular.module(moduleName);
                }
                catch (exception) {
                    module = angular.module(moduleName, []);
                }
                module.directive(selector, Class);
            };
        }
        Common.Directive = Directive;
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
/**
 * Helpers.
 */
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        /**
         * Extraction type.
         */
        var EXTRACT_TYPE;
        (function (EXTRACT_TYPE) {
            EXTRACT_TYPE[EXTRACT_TYPE["Values"] = 0] = "Values";
            EXTRACT_TYPE[EXTRACT_TYPE["Keys"] = 1] = "Keys";
        })(EXTRACT_TYPE = Common.EXTRACT_TYPE || (Common.EXTRACT_TYPE = {}));
        /**
         * Extract values/keys of object.
         *
         * @param object
         * @param {TotalCore.EXTRACT_TYPE} extract
         * @returns {any[]}
         * @private
         */
        function extract(object, extract) {
            var values = [];
            angular.forEach(object, function (value, key) { return values.push(extract === EXTRACT_TYPE.Values ? value : key); });
            return values;
        }
        Common.extract = extract;
        /**
         * Shuffle array.
         *
         * @param {Array} array
         * @returns {Array}
         */
        function shuffle(array) {
            var currentIndex = array.length, temporaryValue, randomIndex;
            while (0 !== currentIndex) {
                randomIndex = Math.floor(Math.random() * currentIndex);
                currentIndex -= 1;
                temporaryValue = array[currentIndex];
                array[currentIndex] = array[randomIndex];
                array[randomIndex] = temporaryValue;
            }
            return array;
        }
        Common.shuffle = shuffle;
        /**
         * Processable trait.
         */
        var Processable = /** @class */ (function () {
            function Processable() {
                this.processed = false;
                this.processing = false;
            }
            /**
             * Check processed.
             * @returns {boolean}
             */
            Processable.prototype.isProcessed = function () {
                return this.processed;
            };
            /**
             * Check processing.
             * @returns {boolean}
             */
            Processable.prototype.isProcessing = function () {
                return this.processing;
            };
            Processable.prototype.setProcessed = function (processed) {
                this.processed = processed;
            };
            /**
             * Start processing.
             */
            Processable.prototype.startProcessing = function () {
                this.processing = true;
            };
            /**
             * Stop processing.
             */
            Processable.prototype.stopProcessing = function () {
                this.processing = false;
            };
            return Processable;
        }());
        Common.Processable = Processable;
        /**
         * Progressive trait.
         */
        var Progressive = /** @class */ (function (_super) {
            __extends(Progressive, _super);
            function Progressive() {
                var _this = _super !== null && _super.apply(this, arguments) || this;
                _this.progress = false;
                return _this;
            }
            /**
             * Get progress.
             * @returns {Number | Boolean}
             */
            Progressive.prototype.getProgress = function () {
                return this.progress;
            };
            /**
             * Set progress.
             * @param {Number | Boolean} progress
             */
            Progressive.prototype.setProgress = function (progress) {
                this.progress = progress;
            };
            return Progressive;
        }(Processable));
        Common.Progressive = Progressive;
        /**
         * Paginated table.
         */
        var PaginatedTable = /** @class */ (function () {
            function PaginatedTable() {
                this.pagination = {
                    page: 1,
                    total: 1,
                };
            }
            PaginatedTable.prototype.getPage = function () {
                return this.pagination.page;
            };
            PaginatedTable.prototype.getTotalPages = function () {
                return this.pagination.total;
            };
            PaginatedTable.prototype.hasNextPage = function () {
                return !this.isLastPage();
            };
            PaginatedTable.prototype.hasPreviousPage = function () {
                return !this.isFirstPage();
            };
            PaginatedTable.prototype.isFirstPage = function () {
                return this.isPage(1);
            };
            PaginatedTable.prototype.isLastPage = function () {
                return this.getPage() == this.getTotalPages();
            };
            PaginatedTable.prototype.isPage = function (page) {
                return this.getPage() == page;
            };
            PaginatedTable.prototype.nextPage = function () {
                var _this = this;
                var nextPage = this.getPage() + 1;
                return this.fetchPage(nextPage)
                    .then(function (result) {
                    _this.setPage(nextPage);
                    return result;
                });
            };
            PaginatedTable.prototype.previousPage = function () {
                var _this = this;
                var previousPage = this.pagination.page + 1;
                return this.fetchPage(previousPage)
                    .then(function (result) {
                    _this.setPage(previousPage);
                    return result;
                });
            };
            PaginatedTable.prototype.setPage = function (page) {
                this.pagination.page = Math.abs(page);
            };
            PaginatedTable.prototype.setTotalPages = function (total) {
                this.pagination.total = Math.abs(total) || 1;
            };
            return PaginatedTable;
        }());
        Common.PaginatedTable = PaginatedTable;
        /**
         * Transitions
         */
        var Transition = /** @class */ (function () {
            function Transition(element, duration) {
                if (duration === void 0) { duration = 500; }
                this.duration = 500;
                this.element = window['jQuery'](element);
            }
            Transition.prototype.getDuration = function () {
                return this.duration;
            };
            Transition.prototype.getElement = function () {
                return this.element;
            };
            return Transition;
        }());
        Common.Transition = Transition;
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
        Common.SimpleTransition = SimpleTransition;
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
                this.getElement().fadeTo(duration, 0.00001, callback);
            };
            return FadeTransition;
        }(Transition));
        Common.FadeTransition = FadeTransition;
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
        Common.SlideTransition = SlideTransition;
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Configs;
        (function (Configs) {
            var GlobalConfig = /** @class */ (function () {
                function GlobalConfig($locationProvider, $compileProvider) {
                    $locationProvider.html5Mode({ enabled: true, requireBase: false, rewriteLinks: false });
                    // $compileProvider.debugInfoEnabled(false);
                    // $compileProvider.commentDirectivesEnabled(false);
                    // $compileProvider.cssClassDirectivesEnabled(false);
                }
                GlobalConfig = __decorate([
                    Common.Injectable()
                ], GlobalConfig);
                return GlobalConfig;
            }());
            Configs.GlobalConfig = GlobalConfig;
        })(Configs = Common.Configs || (Common.Configs = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Configs;
        (function (Configs) {
            var HttpConfig = /** @class */ (function () {
                function HttpConfig($resourceProvider, $httpProvider, $compileProvider) {
                    // Don't strip trailing slashes from calculated URLs
                    $resourceProvider.defaults.stripTrailingSlashes = false;
                    $httpProvider.defaults.transformRequest = function (data) {
                        if (data === undefined) {
                            return data;
                        }
                        return HttpConfig_1.serializer(new FormData(), data);
                    };
                    $httpProvider.defaults.headers.post['Content-Type'] = undefined;
                    $compileProvider.debugInfoEnabled(false);
                }
                HttpConfig_1 = HttpConfig;
                HttpConfig.serializer = function (form, fields, parent) {
                    angular.forEach(fields, function (fieldValue, fieldName) {
                        if (parent) {
                            fieldName = parent + "[" + fieldName + "]";
                        }
                        if (fieldValue !== null && typeof fieldValue === 'object' && (fieldValue.__proto__ === Object.prototype || fieldValue.__proto__ === Array.prototype)) {
                            HttpConfig_1.serializer(form, fieldValue, fieldName);
                        }
                        else {
                            if (typeof fieldValue === 'boolean') {
                                fieldValue = Number(fieldValue);
                            }
                            else if (fieldValue === null) {
                                fieldValue = '';
                            }
                            form.append(fieldName, fieldValue);
                        }
                    });
                    return form;
                };
                HttpConfig = HttpConfig_1 = __decorate([
                    Common.Injectable()
                ], HttpConfig);
                return HttpConfig;
                var HttpConfig_1;
            }());
            Configs.HttpConfig = HttpConfig;
        })(Configs = Common.Configs || (Common.Configs = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Providers;
        (function (Providers) {
            var SettingsService = /** @class */ (function () {
                function SettingsService(namespace, prefix) {
                    this.namespace = namespace;
                    this.prefix = prefix;
                    this.account = window[this.namespace + "Account"] || [];
                    this.activation = window[this.namespace + "Activation"] || [];
                    this.defaults = window[this.namespace + "Defaults"] || {};
                    this.i18n = window[this.namespace + "I18n"] || [];
                    this.information = window[this.namespace + "Information"] || {};
                    this.languages = window[this.namespace + "Languages"] || [];
                    this.modules = window[this.namespace + "Modules"] || {};
                    this.presets = window[this.namespace + "Presets"] || [];
                    this.settings = window[this.namespace + "Settings"] || {};
                    this.support = window[this.namespace + "Support"] || [];
                    this.templates = window[this.namespace + "Templates"] || {};
                    this.versions = window[this.namespace + "Versions"] || [];
                    this.settings['id'] = this.defaults['id'];
                    if (this.defaults.expressions && angular.isArray(this.defaults.expressions)) {
                        this.defaults.expressions = {};
                    }
                    this.settings = angular.merge({}, this.defaults, this.settings);
                }
                SettingsService = __decorate([
                    Common.Service('services.common')
                ], SettingsService);
                return SettingsService;
            }());
            Providers.SettingsService = SettingsService;
        })(Providers = Common.Providers || (Common.Providers = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Providers;
        (function (Providers) {
            var wp = window['wp'];
            var MediaUploadService = /** @class */ (function () {
                function MediaUploadService($q) {
                    this.$q = $q;
                }
                MediaUploadService.prototype.customFrame = function (args) {
                    return wp.media(angular.extend({}, { title: wp.media.view.l10n.insertMediaTitle }, args));
                };
                MediaUploadService.prototype.frame = function (type) {
                    if (!this.wpMediaFrame) {
                        this.wpMediaFrame = wp.media({
                            title: wp.media.view.l10n.insertMediaTitle,
                            multiple: false,
                            library: {
                                type: type
                            }
                        });
                    }
                    if (type !== this.wpMediaFrame.options.library.type) {
                        this.wpMediaFrame = false;
                        this.frame(type);
                    }
                    return this.wpMediaFrame;
                };
                MediaUploadService.prototype.open = function (type) {
                    var _this = this;
                    return this.$q(function (resolve, reject) {
                        try {
                            _this.frame(type).open();
                            _this.wpMediaFrame
                                .state('library')
                                .off('select')
                                .on('select', function () {
                                resolve(this.get('selection').first());
                            });
                        }
                        catch (exception) {
                            reject(exception);
                        }
                    });
                };
                MediaUploadService = __decorate([
                    Common.Service('services.common')
                ], MediaUploadService);
                return MediaUploadService;
            }());
            Providers.MediaUploadService = MediaUploadService;
        })(Providers = Common.Providers || (Common.Providers = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Providers;
        (function (Providers) {
            var wp = window['wp'];
            var EmbedService = /** @class */ (function () {
                function EmbedService($q, $http) {
                    this.$q = $q;
                    this.$http = $http;
                    this.providersUrlPatterns = [
                        /https?:\/\/((m|www)\.)?youtube\.com\/watch.*/i,
                        /https?:\/\/((m|www)\.)?youtube\.com\/playlist.*/i,
                        /https?:\/\/youtu\.be\/.*/i,
                        /https?:\/\/(.+\.)?vimeo\.com\/.*/i,
                        /https?:\/\/(www\.)?dailymotion\.com\/.*/i,
                        /https?:\/\/dai\.ly\/.*/i,
                        /https?:\/\/(www\.)?(animoto|video214)\.com\/play\/.*/i,
                        /https?:\/\/(www\.)?twitter\.com\/i\/moments\/.*/i,
                        /https?:\/\/www\.facebook\.com\/video\.php.*/i,
                        /https?:\/\/www\.facebook\.com\/.*\/videos\/.*/i,
                        /https?:\/\/vine\.co\/v\/.*/i,
                        /https?:\/\/(www\.)?mixcloud\.com\/.*/i,
                        /https?:\/\/(www\.)?reverbnation\.com\/.*/i,
                        /https?:\/\/(www\.)?soundcloud\.com\/.*/i,
                        /https?:\/\/(open|play)\.spotify\.com\/.*/i,
                    ];
                }
                EmbedService.prototype.discover = function (url) {
                    return this.$http
                        .get('https://noembed.com/embed', {
                        params: {
                            format: 'json',
                            url: url,
                        },
                        cache: true,
                        responseType: 'json'
                    })
                        .then(function (response) {
                        return response.data;
                    });
                };
                EmbedService.prototype.fetch = function (url) {
                    var _this = this;
                    return this.$q(function (resolve, reject) {
                        for (var _i = 0, _a = _this.providersUrlPatterns; _i < _a.length; _i++) {
                            var regExp = _a[_i];
                            if (regExp.exec(url)) {
                                return _this.discover(url).then(function (response) {
                                    resolve(response);
                                }).catch(reject);
                            }
                        }
                        reject();
                    });
                };
                EmbedService = __decorate([
                    Common.Service('services.common')
                ], EmbedService);
                return EmbedService;
            }());
            Providers.EmbedService = EmbedService;
        })(Providers = Common.Providers || (Common.Providers = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Providers;
        (function (Providers) {
            var UniqueIdService = /** @class */ (function () {
                function UniqueIdService() {
                }
                /**
                 * Generate GUID
                 * @see http://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
                 * @returns {string}
                 */
                UniqueIdService.prototype.generate = function () {
                    var d = new Date().getTime();
                    if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
                        d += performance.now(); //use high-precision timer if available
                    }
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                        var r = (d + Math.random() * 16) % 16 | 0;
                        d = Math.floor(d / 16);
                        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                    });
                };
                UniqueIdService = __decorate([
                    Common.Service('services.common')
                ], UniqueIdService);
                return UniqueIdService;
            }());
            Providers.UniqueIdService = UniqueIdService;
        })(Providers = Common.Providers || (Common.Providers = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Providers;
        (function (Providers) {
            var TabService = /** @class */ (function () {
                function TabService($location, $rootScope) {
                    var _this = this;
                    this.$location = $location;
                    this.$rootScope = $rootScope;
                    this.currentTab = '';
                    this.tabs = {};
                    var urlParams = this.$location.search();
                    $rootScope.isCurrentTab = function (tab) {
                        return _this.is(tab);
                    };
                    $rootScope.setCurrentTab = function (tab) {
                        var parsed = _this.parse(tab);
                        return _this.set(parsed.group, parsed.name);
                    };
                    $rootScope.getCurrentTab = function () {
                        return _this.currentTab;
                    };
                    var tabs = (urlParams.tab || '')['split']('>');
                    var _loop_1 = function (index) {
                        var group = tabs[index + 1] ? tabs[index] : tabs[index - 1];
                        var tab = tabs[index + 1] || tabs[index];
                        $rootScope.$applyAsync(function () {
                            _this.set(group, tab);
                        });
                    };
                    for (var index = 0; index < tabs.length; index = index + 2) {
                        _loop_1(index);
                    }
                }
                TabService.prototype.get = function (group, name) {
                    return this.tabs[group][name] || false;
                };
                TabService.prototype.is = function (tabName) {
                    return this.currentTab.indexOf(tabName) !== -1;
                };
                TabService.prototype.parse = function (tab) {
                    var composedName;
                    var name;
                    var group;
                    composedName = tab.split('>');
                    name = composedName.pop();
                    group = composedName.pop();
                    return { group: group, name: name, root: composedName.join('>') };
                };
                TabService.prototype.put = function (fullName, group, name, element) {
                    this.tabs[group] = this.tabs[group] || {};
                    this.tabs[group][name] = {
                        element: element,
                        fullName: fullName
                    };
                };
                TabService.prototype.set = function (group, name) {
                    if (!this.tabs[group] || !this.tabs[group][name]) {
                        return;
                    }
                    angular.forEach(this.tabs[group], function (tab, key) {
                        angular.element(document).find("[tab=\"" + tab.fullName + "\"]").removeClass('active');
                        tab.element.removeClass('active');
                    });
                    this.tabs[group][name].element.addClass('active');
                    this.currentTab = this.tabs[group][name].fullName;
                    angular.element(document).find("[tab=\"" + this.currentTab + "\"]").addClass('active');
                    this.$location.search('tab', this.currentTab);
                };
                TabService = __decorate([
                    Common.Service('services.common')
                ], TabService);
                return TabService;
            }());
            Providers.TabService = TabService;
        })(Providers = Common.Providers || (Common.Providers = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
///<reference path="../providers/tab.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var Tabs = /** @class */ (function () {
                function Tabs(TabService) {
                    return {
                        restrict: 'A',
                        link: function ($scope, element, attributes) {
                            if (!attributes.tabSwitch) {
                                return;
                            }
                            var parsed = TabService.parse(attributes.tabSwitch);
                            if (!parsed.name || parsed.name.trim() == "") {
                                parsed.name = Date.now().toString();
                            }
                            if (!parsed.group || parsed.group.trim() == "") {
                                parsed.group = 'default';
                                element.attr('tab-switch', parsed.group + ">" + parsed.name);
                            }
                            TabService.put("" + (parsed.root ? parsed.root + '>' : '') + parsed.group + ">" + parsed.name, parsed.group, parsed.name, element);
                            element.on('click', function () {
                                $scope.$applyAsync(function () { return TabService.set(parsed.group, parsed.name); });
                                return false;
                            });
                        }
                    };
                }
                Tabs = __decorate([
                    Common.Directive('directives.common', 'tabSwitch')
                ], Tabs);
                return Tabs;
            }());
            Directives.Tabs = Tabs;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var Datetimepicker = /** @class */ (function () {
                function Datetimepicker() {
                    return {
                        restrict: 'A',
                        require: 'ngModel',
                        scope: {
                            'model': '=ngModel'
                        },
                        link: function ($scope, element, attributes, ngModel) {
                            var defaultOptions = {
                                format: 'm/d/Y H:i',
                                validateOnBlur: false
                            };
                            var userOptions = JSON.parse(attributes.datetimePicker || "{}");
                            var mergedOptions = angular.merge({}, defaultOptions, userOptions);
                            element.datetimepicker(mergedOptions);
                            $scope.$watch('model', function (date, oldDate) {
                                if (date != oldDate) {
                                    element.val(date);
                                }
                            });
                        }
                    };
                }
                Datetimepicker = __decorate([
                    Common.Directive('directives.common', 'datetimePicker')
                ], Datetimepicker);
                return Datetimepicker;
            }());
            Directives.Datetimepicker = Datetimepicker;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var Colorpicker = /** @class */ (function () {
                function Colorpicker() {
                    return {
                        restrict: 'A',
                        require: 'ngModel',
                        scope: {
                            'model': '=ngModel'
                        },
                        link: function ($scope, element, attributes, ngModel) {
                            var updateModel = function (color) {
                                ngModel.$setViewValue(color);
                                ngModel.$render();
                                $scope.$applyAsync();
                            };
                            var defaultOptions = {
                                change: function (event, ui) {
                                    updateModel(ui.color.toCSS());
                                },
                                clear: function () {
                                    updateModel('');
                                }
                            };
                            var userOptions = JSON.parse(attributes.colorPicker || "{}");
                            var mergedOptions = angular.merge({}, defaultOptions, userOptions);
                            element.wpColorPicker(mergedOptions);
                            element.wpColorPicker('color', $scope.model);
                            $scope.$watch('model', function (color, oldColor) {
                                if (color != oldColor) {
                                    element.wpColorPicker('color', color);
                                }
                            });
                        }
                    };
                }
                Colorpicker = __decorate([
                    Common.Directive('directives.common', 'colorPicker')
                ], Colorpicker);
                return Colorpicker;
            }());
            Directives.Colorpicker = Colorpicker;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var TinyMCE = /** @class */ (function () {
                function TinyMCE($sce, $rootScope, $timeout, $compile) {
                    return {
                        require: ['ngModel'],
                        restrict: 'E',
                        scope: {
                            'model': '=ngModel',
                            'uid': '@'
                        },
                        link: function ($scope, element, attributes, ctrls) {
                            var tinymceElement;
                            var debouncedUpdate;
                            var editor;
                            var updateView;
                            var uniqueId;
                            var template;
                            var id;
                            var settings;
                            if (!window['tinymce']) {
                                return;
                            }
                            updateView = function (editor) {
                                $scope.model = editor.getContent().trim();
                                if (!$rootScope.$$phase) {
                                    $scope.$digest();
                                }
                            };
                            debouncedUpdate = (function (debouncedUpdateDelay) {
                                var debouncedUpdateTimer;
                                return function (editorInstance) {
                                    $timeout.cancel(debouncedUpdateTimer);
                                    debouncedUpdateTimer = $timeout(function () {
                                        return (function (editorInstance) {
                                            if (editorInstance.isDirty()) {
                                                var bookmark = window['tinymce'].activeEditor.selection.getBookmark();
                                                editorInstance.save();
                                                window['tinymce'].activeEditor.selection.moveToBookmark(bookmark);
                                                updateView(editorInstance);
                                            }
                                        })(editorInstance);
                                    }, debouncedUpdateDelay);
                                };
                            })(400);
                            template = window['TinyMCETemplate'] || '';
                            uniqueId = Date.now() + Math.floor(Math.random() * 30);
                            id = "tinymce-field-" + uniqueId;
                            settings = angular.copy(window['tinyMCEPreInit']['mceInit']['tinymce-field']);
                            settings.selector = "#" + id;
                            settings.cache_suffix = "wp-mce-" + uniqueId;
                            settings.body_class = settings.body_class.replace('tinymce-field', id);
                            settings.init_instance_callback = function (editor) {
                                editor.on('ExecCommand change NodeChange ObjectResized', function () {
                                    debouncedUpdate(editor);
                                });
                                $scope.$watch('uid', function () {
                                    try {
                                        editor.setContent($scope.model || '');
                                    }
                                    catch (ex) {
                                        if (!(ex instanceof TypeError)) {
                                            console.error(ex);
                                        }
                                    }
                                });
                                try {
                                    editor.setContent($scope.model || '');
                                }
                                catch (ex) {
                                    if (!(ex instanceof TypeError)) {
                                        console.error(ex);
                                    }
                                }
                                if (window['switchEditors']) {
                                    window['switchEditors'].go(id, 'html');
                                }
                            };
                            template = template
                                .replace(/name="tinymce\-textarea\-name"/g, "name=\"" + id + "\" ng-model=\"model\"")
                                .replace(/tinymce\-field/g, id);
                            tinymceElement = $compile(template)($scope);
                            element.append(tinymceElement);
                            window['tinyMCEPreInit'].mceInit[id] = settings;
                            window['tinyMCEPreInit']['qtInit'][id] = angular.copy(window['tinyMCEPreInit']['qtInit']['tinymce-field']);
                            window['tinyMCEPreInit']['qtInit'][id].id = id;
                            window['tinymce'].init(settings);
                            window['quicktags']({ id: id, buttons: window['tinyMCEPreInit']['qtInit'][id].buttons });
                            window['QTags']._buttonsInit();
                        }
                    };
                }
                ;
                TinyMCE = __decorate([
                    Common.Directive('directives.common', 'tinymce')
                ], TinyMCE);
                return TinyMCE;
            }());
            Directives.TinyMCE = TinyMCE;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var CopyToClipboard = /** @class */ (function () {
                function CopyToClipboard() {
                    var copyToClipboard = function (text) {
                        if (window['clipboardData'] && window['clipboardData'].setData) {
                            return window['clipboardData'].setData("Text", text);
                        }
                        else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
                            var textarea = document.createElement("textarea");
                            textarea.textContent = text;
                            textarea.style.position = "fixed";
                            document.body.appendChild(textarea);
                            textarea.select();
                            try {
                                return document.execCommand("copy");
                            }
                            catch (ex) {
                                prompt('', text);
                                return false;
                            }
                            finally {
                                document.body.removeChild(textarea);
                            }
                        }
                    };
                    return {
                        restrict: 'A',
                        link: function ($scope, element, attributes) {
                            element.on('click', function () {
                                var originalHTML = element.html();
                                copyToClipboard(attributes['copyToClipboard']);
                                element.html(attributes['copyToClipboardDone'] || '<span class="dashicons dashicons-yes"></span>');
                                setTimeout(function () {
                                    element.html(originalHTML);
                                }, 1000);
                            });
                        }
                    };
                }
                CopyToClipboard = __decorate([
                    Common.Directive('directives.common', 'copyToClipboard')
                ], CopyToClipboard);
                return CopyToClipboard;
            }());
            Directives.CopyToClipboard = CopyToClipboard;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Filters;
        (function (Filters) {
            var Slug = /** @class */ (function () {
                function Slug() {
                    return function (input, separator) {
                        if (separator === void 0) { separator = '-'; }
                        return Slug_1.filter(input, separator);
                    };
                }
                Slug_1 = Slug;
                Slug.filter = function (name, separator, keepEdge) {
                    if (keepEdge === void 0) { keepEdge = false; }
                    if (name && name.toString) {
                        return name
                            .toString()
                            .toLowerCase()
                            .replace(/\s+/g, separator) // Replace spaces with -
                            .replace(new RegExp(("[\\" + Slug_1.specialChars.join('\\') + "]+").replace("\\" + separator, ''), 'g'), '') // Remove all non-word chars
                            .replace(new RegExp("\\" + separator + "\\" + separator + "+", 'g'), separator) // Replace multiple - with single -
                            .replace(keepEdge ? null : new RegExp("^\\" + separator + "+"), '') // Trim - from start of text
                            .replace(keepEdge ? null : new RegExp("\\" + separator + "+$"), ''); // Trim - from end of text
                    }
                };
                Slug.$inject = [];
                Slug.specialChars = ["!", "@", "#", "$", "%", "^", "&", "*", "<", ">", ":", ".", ";", ",", "!", "?", "§", "¨", "£", "-", "_", "(", ")", "{", "}", "[", "]", "=", "+", "|", "~", "`", "'", "°", '"', "/", "\\"];
                Slug = Slug_1 = __decorate([
                    Common.Filter('filters.common', 'slug')
                ], Slug);
                return Slug;
                var Slug_1;
            }());
            Filters.Slug = Slug;
        })(Filters = Common.Filters || (Common.Filters = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
///<reference path="../providers/settings.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Filters;
        (function (Filters) {
            var I18n = /** @class */ (function () {
                function I18n(SettingsService) {
                    return function (input, separator) {
                        if (separator === void 0) { separator = '-'; }
                        return I18n_1.filter(input, separator, SettingsService.i18n);
                    };
                }
                I18n_1 = I18n;
                I18n.filter = function (name, separator, expressions) {
                    if (name && name.toString) {
                        return expressions[name] || name;
                    }
                };
                I18n = I18n_1 = __decorate([
                    Common.Filter('filters.common', 'i18n')
                ], I18n);
                return I18n;
                var I18n_1;
            }());
            Filters.I18n = I18n;
        })(Filters = Common.Filters || (Common.Filters = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var Chart = /** @class */ (function () {
                function Chart() {
                    return {
                        restrict: 'E',
                        require: 'ngModel',
                        scope: {
                            'model': '=ngModel'
                        },
                        template: '<canvas></canvas>',
                        link: function ($scope, element, attributes, ngModel) {
                            var chartInstance;
                            if (!window['Chart']) {
                                throw new Error('Chart.js library is required for charts.');
                            }
                            var defaultOptions = {
                                type: attributes.type || 'line',
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        yAxes: [{
                                                ticks: {
                                                    beginAtZero: true
                                                }
                                            }],
                                        xAxes: [{
                                                ticks: {
                                                    beginAtZero: true
                                                }
                                            }]
                                    },
                                    animation: {
                                        animateScale: true,
                                        animateRotate: true
                                    }
                                },
                                data: $scope.model || { datasets: [] },
                            };
                            if (defaultOptions.type === 'doughnut') {
                                delete defaultOptions.options.scales;
                            }
                            var userOptions = JSON.parse(attributes.options || "{}");
                            var mergedOptions = angular.merge({}, defaultOptions, userOptions);
                            chartInstance = new window['Chart'](element.find('canvas'), mergedOptions);
                            $scope.$watch('model', function (newValue, oldValue, scope) {
                                if (newValue === oldValue) {
                                    return;
                                }
                                chartInstance.data = angular.copy(newValue);
                                chartInstance.update();
                            }, true);
                        }
                    };
                }
                Chart = __decorate([
                    Common.Directive('directives.common', 'chart')
                ], Chart);
                return Chart;
            }());
            Directives.Chart = Chart;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
///<reference path="../../common/helpers.ts"/>
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Providers;
        (function (Providers) {
            var ColorsService = /** @class */ (function () {
                function ColorsService() {
                    this.colors = ["#f44336", "#e91e63", "#9c27b0", "#673ab7", "#3f51b5", "#2196f3", "#03a9f4", "#00bcd4", "#009688", "#4caf50", "#8bc34a", "#cddc39", "#ffeb3b", "#ffc107", "#ff9800", "#ff5722", "#795548", "#9e9e9e", "#607d8b", "#ffb900", "#e74856", "#0078d7", "#0099bc", "#7a7574", "#767676", "#ff8c00", "#e81123", "#0063b1", "#2d7d9a", "#5d5a58", "#4c4a48", "#f7630c", "#ea005e", "#8e8cd8", "#00b7c3", "#68768a", "#69797e", "#ca5010", "#c30052", "#6b69d6", "#038387", "#515c6b", "#4a5459", "#da3b01", "#e3008c", "#8764b8", "#00b294", "#567c73", "#647c64", "#ef6950", "#bf0077", "#744da9", "#018574", "#486860", "#525e54", "#d13438", "#c239b3", "#b146c2", "#00cc6a", "#498205", "#847545", "#ff4343", "#9a0089", "#881798", "#10893e", "#107c10", "#7e735f", "#a4c400", "#60a917", "#008a00", "#00aba9", "#1ba1e2", "#0050ef", "#6a00ff", "#aa00ff", "#f472d0", "#d80073", "#a20025", "#e51400", "#fa6800", "#f0a30a", "#e3c800", "#825a2c", "#6d8764", "#647687", "#76608a", "#a0522d"];
                }
                ColorsService.prototype.getRandomColors = function (count) {
                    this.colors = Common.shuffle(this.colors);
                    return this.colors.slice(0, count > this.colors.length ? this.colors.length : count);
                };
                ColorsService = __decorate([
                    Common.Service('services.common')
                ], ColorsService);
                return ColorsService;
            }());
            Providers.ColorsService = ColorsService;
        })(Providers = Common.Providers || (Common.Providers = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
///<reference path="../../common/decorators.ts"/>
///<reference path="../../common/configs/Global.ts" />
///<reference path="../../common/configs/Http.ts" />
var TotalCore;
(function (TotalCore) {
    var Common;
    (function (Common) {
        var Directives;
        (function (Directives) {
            var ClickTracker = /** @class */ (function () {
                function ClickTracker($resource, prefix, ajaxEndpoint) {
                    var resource = $resource(ajaxEndpoint, {}, {
                        track: { method: 'POST', params: { action: prefix + "_tracking_features" } },
                    });
                    return {
                        restrict: 'A',
                        link: function ($scope, element, attributes) {
                            var data = $scope.$eval(attributes.track);
                            element.on('click', function () {
                                resource.track(data);
                            });
                        }
                    };
                }
                ClickTracker = __decorate([
                    Common.Directive('directives.common', 'track')
                ], ClickTracker);
                return ClickTracker;
            }());
            Directives.ClickTracker = ClickTracker;
        })(Directives = Common.Directives || (Common.Directives = {}));
    })(Common = TotalCore.Common || (TotalCore.Common = {}));
})(TotalCore || (TotalCore = {}));
var TotalCore;
(function (TotalCore) {
    var Customizer;
    (function (Customizer) {
        var Components;
        (function (Components) {
            var Component = TotalCore.Common.Component;
            var CustomizerComponent = /** @class */ (function () {
                function CustomizerComponent($scope, $compile, $templateCache, $http, $sce, $element, $q, SettingsService) {
                    this.$scope = $scope;
                    this.$compile = $compile;
                    this.$templateCache = $templateCache;
                    this.$http = $http;
                    this.$sce = $sce;
                    this.$element = $element;
                    this.$q = $q;
                    this.SettingsService = SettingsService;
                    this.device = 'laptop';
                    this.devices = {
                        smartphone: {
                            width: 431,
                            height: 877,
                            canvas: {
                                width: 375,
                                height: 667,
                                top: 105,
                                right: 402,
                                bottom: 772,
                                left: 27,
                            }
                        },
                        tablet: {
                            width: 875,
                            height: 1253,
                            canvas: {
                                width: 768,
                                height: 1024,
                                top: 114,
                                right: 820,
                                bottom: 1138,
                                left: 52,
                            }
                        },
                    };
                    this.preview = {
                        screen: '',
                    };
                    this.settings = {};
                    this.tab = [];
                    this.settings = SettingsService.settings.design || this.settings;
                    this.iframe = $element.find('iframe');
                    if (!this.SettingsService.templates[this.getTemplate()]) {
                        this.setTemplate(this.SettingsService.defaults.design.template);
                    }
                    window['jQuery'](window).resize(function () {
                        $scope.$applyAsync();
                    });
                }
                CustomizerComponent.prototype.$onInit = function () {
                    this.preparePreview();
                };
                CustomizerComponent.prototype.changeTemplateTo = function (template, $event) {
                    $event.stopImmediatePropagation();
                    this.setTemplate(template.id);
                    this.popToRoot();
                    this.preparePreview();
                };
                CustomizerComponent.prototype.escape = function (content) {
                    return this.$sce.trustAsHtml(content);
                };
                CustomizerComponent.prototype.getActiveTab = function () {
                    return this.tab[this.tab.length - 1];
                };
                CustomizerComponent.prototype.getActiveTabBreadcrumb = function () {
                    return this.tab
                        .map(function (tab) {
                        return tab.label;
                    })
                        .join(' / ');
                };
                CustomizerComponent.prototype.getCurrentTemplate = function (field) {
                    var template = this.SettingsService.templates[this.getTemplate()];
                    return field ? template[field] : template;
                };
                CustomizerComponent.prototype.getCurrentTemplateDefaults = function () {
                    if (this.getCurrentTemplate('defaults')) {
                        return this.$http.get(this.prefixUrl(this.getCurrentTemplate('defaults')), { cache: true }).then(function (response) { return response.data; });
                    }
                    return this.$q.resolve({});
                };
                CustomizerComponent.prototype.getCurrentTemplatePreviewContentId = function () {
                    return this.getCurrentTemplate('preview') ? this.prefixUrl(this.getCurrentTemplate('preview')) : null;
                };
                CustomizerComponent.prototype.getCurrentTemplatePreviewCssId = function () {
                    return this.getCurrentTemplate('stylesheet') ? this.prefixUrl(this.getCurrentTemplate('stylesheet')) : null;
                };
                CustomizerComponent.prototype.getCurrentTemplateSettingsId = function () {
                    return this.getCurrentTemplate('settings') ? this.prefixUrl(this.getCurrentTemplate('settings')) : null;
                };
                CustomizerComponent.prototype.getDevice = function () {
                    return this.device;
                };
                CustomizerComponent.prototype.getDeviceScaleAttributes = function () {
                    if (this.device === 'laptop') {
                        return {};
                    }
                    var scale = this.$element.find('iframe').parent().outerHeight() / this.devices[this.device].height;
                    return {
                        transform: "scale(" + scale + ")",
                        marginTop: this.devices[this.device].canvas.top * scale,
                    };
                };
                CustomizerComponent.prototype.getScreen = function () {
                    return this.preview.screen;
                };
                CustomizerComponent.prototype.getTemplate = function () {
                    return this.settings.template;
                };
                CustomizerComponent.prototype.getTemplates = function () {
                    return this.SettingsService.templates;
                };
                CustomizerComponent.prototype.hasActiveTab = function (tab) {
                    if (tab) {
                        for (var _i = 0, _a = this.tab; _i < _a.length; _i++) {
                            var item = _a[_i];
                            if (item.id === tab) {
                                return true;
                            }
                        }
                        return false;
                    }
                    else {
                        return this.tab.length > 0;
                    }
                };
                CustomizerComponent.prototype.hasActiveTabAfter = function (tab) {
                    return this.hasActiveTab(tab) && this.getActiveTab().id !== tab;
                };
                CustomizerComponent.prototype.isActiveTab = function (tab) {
                    if (this.tab.length > 0) {
                        return this.tab[this.tab.length - 1].id === tab;
                    }
                    return false;
                };
                CustomizerComponent.prototype.isDevice = function (device) {
                    return this.device === device;
                };
                CustomizerComponent.prototype.isScreen = function (screen) {
                    return this.preview.screen === screen;
                };
                CustomizerComponent.prototype.isTemplate = function (template) {
                    return this.settings.template === template;
                };
                CustomizerComponent.prototype.popActiveTab = function () {
                    this.tab.pop();
                };
                CustomizerComponent.prototype.popToRoot = function () {
                    this.tab = [];
                };
                CustomizerComponent.prototype.preparePreview = function () {
                    var _this = this;
                    var headTemplate = this.$templateCache.get('customizer-preview-head-template');
                    var bodyTemplate = this.$templateCache.get('customizer-preview-body-template');
                    this.compiledHeadTemplate = this.$compile(headTemplate, null);
                    this.compiledBodyTemplate = this.$compile(bodyTemplate, null);
                    this.getCurrentTemplateDefaults()
                        .then(function (defaults) {
                        _this.settings.custom = angular.extend({}, defaults, _this.settings.custom);
                        _this.$scope.custom = _this.settings.custom;
                        angular.forEach(_this.settings, function (value, key) {
                            _this.$scope[key] = value;
                        });
                    });
                    this.iframe.on('load', function () {
                        var html = _this.iframe.contents();
                        _this.compiledHeadTemplate(_this.$scope, function (content) {
                            html.find('head').append(content);
                        });
                        _this.compiledBodyTemplate(_this.$scope, function (content) {
                            html.find('body').html(content);
                        });
                        _this.iframe.trigger('resize');
                    });
                    this.iframe.attr('src', '');
                };
                CustomizerComponent.prototype.resetActiveTab = function () {
                    this.tab = [];
                };
                CustomizerComponent.prototype.resetToDefaults = function (confirmBefore) {
                    var _this = this;
                    if (confirmBefore === void 0) { confirmBefore = false; }
                    if (confirmBefore && !confirm('Are you sure?')) {
                        return;
                    }
                    this.getCurrentTemplateDefaults()
                        .then(function (defaults) {
                        _this.settings.custom = angular.extend({}, _this.settings.custom, defaults);
                        _this.$scope.custom = _this.settings.custom;
                    });
                    if (confirmBefore) {
                        alert('Done');
                    }
                };
                CustomizerComponent.prototype.setActiveTab = function (tab, label) {
                    this.tab.push({ id: tab, label: label });
                };
                CustomizerComponent.prototype.setDevice = function (device) {
                    this.device = device;
                };
                CustomizerComponent.prototype.setScreen = function (screen) {
                    this.preview.screen = screen;
                };
                CustomizerComponent.prototype.setTemplate = function (template) {
                    this.settings.template = template;
                    this.resetToDefaults();
                };
                CustomizerComponent.prototype.prefixUrl = function (url) {
                    if (url.match(/^(https?:)?\/\//g)) {
                        return url;
                    }
                    return "" + this.getCurrentTemplate('url') + url;
                };
                CustomizerComponent = __decorate([
                    Component('components.customizer', {
                        templateUrl: 'customizer-component-template',
                        bindings: {}
                    })
                ], CustomizerComponent);
                return CustomizerComponent;
            }());
        })(Components = Customizer.Components || (Customizer.Components = {}));
    })(Customizer = TotalCore.Customizer || (TotalCore.Customizer = {}));
})(TotalCore || (TotalCore = {}));
var TotalCore;
(function (TotalCore) {
    var Customizer;
    (function (Customizer) {
        var Components;
        (function (Components) {
            var Component = TotalCore.Common.Component;
            var CustomizerControl = /** @class */ (function () {
                function CustomizerControl() {
                    this.type = 'text';
                }
                CustomizerControl.prototype.getTemplate = function () {
                    return "customizer-control-" + this.type + "-template";
                };
                CustomizerControl = __decorate([
                    Component('components.customizer', {
                        templateUrl: 'customizer-control-component-template',
                        bindings: {
                            type: '@',
                            label: '@',
                            help: '@',
                            options: '<',
                            ngModel: '=',
                        },
                        transclude: true,
                    })
                ], CustomizerControl);
                return CustomizerControl;
            }());
        })(Components = Customizer.Components || (Customizer.Components = {}));
    })(Customizer = TotalCore.Customizer || (TotalCore.Customizer = {}));
})(TotalCore || (TotalCore = {}));
var TotalCore;
(function (TotalCore) {
    var Customizer;
    (function (Customizer) {
        var Components;
        (function (Components) {
            var Component = TotalCore.Common.Component;
            var CustomizerTabs = /** @class */ (function () {
                function CustomizerTabs() {
                }
                CustomizerTabs.prototype.getTarget = function () {
                    return this.$content ? this.$content.getTarget() : null;
                };
                CustomizerTabs = __decorate([
                    Component('components.customizer', {
                        templateUrl: 'customizer-tabs-component-template',
                        bindings: {
                            target: '@',
                        },
                        require: {
                            $customizer: '^customizer',
                            $content: '?^^customizerTabContent',
                        },
                        transclude: true,
                    })
                ], CustomizerTabs);
                return CustomizerTabs;
            }());
        })(Components = Customizer.Components || (Customizer.Components = {}));
    })(Customizer = TotalCore.Customizer || (TotalCore.Customizer = {}));
})(TotalCore || (TotalCore = {}));
var TotalCore;
(function (TotalCore) {
    var Customizer;
    (function (Customizer) {
        var Components;
        (function (Components) {
            var Component = TotalCore.Common.Component;
            var CustomizerTab = /** @class */ (function () {
                function CustomizerTab() {
                }
                CustomizerTab.prototype.getTarget = function () {
                    return [this.$content ? this.$content.getTarget() : null, this.target].filter(Boolean).join('.');
                };
                CustomizerTab = __decorate([
                    Component('components.customizer', {
                        templateUrl: 'customizer-tab-component-template',
                        bindings: {
                            target: '@',
                        },
                        require: {
                            $customizer: '^customizer',
                            $content: '?^^customizerTabContent',
                        },
                        transclude: true,
                    })
                ], CustomizerTab);
                return CustomizerTab;
            }());
        })(Components = Customizer.Components || (Customizer.Components = {}));
    })(Customizer = TotalCore.Customizer || (TotalCore.Customizer = {}));
})(TotalCore || (TotalCore = {}));
var TotalCore;
(function (TotalCore) {
    var Customizer;
    (function (Customizer) {
        var Components;
        (function (Components) {
            var Component = TotalCore.Common.Component;
            var CustomizerTabContent = /** @class */ (function () {
                function CustomizerTabContent() {
                }
                CustomizerTabContent.prototype.getTarget = function () {
                    return [this.$content ? this.$content.getTarget() : null, this.name].filter(Boolean).join('.');
                };
                CustomizerTabContent = __decorate([
                    Component('components.customizer', {
                        templateUrl: 'customizer-tab-content-component-template',
                        bindings: {
                            name: '@',
                        },
                        require: {
                            $customizer: '^customizer',
                            $content: '?^^customizerTabContent',
                        },
                        transclude: true,
                    })
                ], CustomizerTabContent);
                return CustomizerTabContent;
            }());
        })(Components = Customizer.Components || (Customizer.Components = {}));
    })(Customizer = TotalCore.Customizer || (TotalCore.Customizer = {}));
})(TotalCore || (TotalCore = {}));
var TotalPoll;
(function (TotalPoll) {
    var Service = TotalCore.Common.Service;
    var EXTRACT_TYPE = TotalCore.Common.EXTRACT_TYPE;
    var extract = TotalCore.Common.extract;
    var RepositoryService = /** @class */ (function () {
        function RepositoryService($resource, $httpParamSerializerJQLike, prefix, ajaxEndpoint, pollId, ColorsService) {
            this.$httpParamSerializerJQLike = $httpParamSerializerJQLike;
            this.prefix = prefix;
            this.ajaxEndpoint = ajaxEndpoint;
            this.pollId = pollId;
            this.ColorsService = ColorsService;
            this.resource = $resource(ajaxEndpoint, {}, {
                metrics: { method: 'GET', cache: true, params: { poll: pollId }, isArray: false },
                polls: { method: 'GET', cache: true, isArray: true },
            });
        }
        RepositoryService.prototype.getMetrics = function (filters) {
            var _this = this;
            if (filters === void 0) { filters = {}; }
            return this.resource
                .metrics(angular.extend({}, { action: 'totalpoll_insights_metrics' }, filters))
                .$promise
                .then(function (response) {
                try {
                    var metrics_1 = { questions: {}, day: {}, month: {}, year: {}, os: {}, browser: {} };
                    // Choices
                    metrics_1.questions = response.questions;
                    angular.forEach(response.period, function (item) {
                        var date = item.period.split('-');
                        var year = date[0];
                        var month = date[1];
                        metrics_1.day[item.period] = Number(item.votes);
                        metrics_1.month[year + "-" + month] = (metrics_1.month[year + "-" + month] || 0) + Number(item.votes) || 0;
                        metrics_1.year[year] = (metrics_1.year[year] || 0) + Number(item.votes);
                    });
                    angular.forEach(response.platforms, function (item) {
                        metrics_1.browser[item.browser] = (metrics_1.browser[item.browser] || 0) + Number(item.votes);
                        metrics_1.os[item.os] = (metrics_1.os[item.os] || 0) + Number(item.votes);
                    });
                    angular.forEach(metrics_1, function (value, key) {
                        var color = _this.ColorsService.getRandomColors(1)[0];
                        if (key === 'questions') {
                            angular.forEach(value, function (question, uid) {
                                metrics_1[uid] = {
                                    labels: extract(question['choices'], EXTRACT_TYPE.Keys),
                                    options: {
                                        options: {
                                            legend: { position: "left", align: "center" },
                                            title: { display: false, "text": question['content'], position: "left", fontSize: 16 }
                                        },
                                    },
                                    datasets: [{
                                            label: 'Votes',
                                            data: extract(question['choices'], EXTRACT_TYPE.Values),
                                            backgroundColor: color,
                                            borderColor: color,
                                            fill: false
                                        }]
                                };
                                metrics_1[uid].datasets[0].backgroundColor = _this.ColorsService.getRandomColors(metrics_1[uid].labels.length);
                                delete metrics_1[uid].datasets[0].borderColor;
                            });
                        }
                        else {
                            metrics_1[key] = {
                                labels: extract(metrics_1[key], EXTRACT_TYPE.Keys),
                                datasets: [{
                                        label: 'Votes',
                                        data: extract(metrics_1[key], EXTRACT_TYPE.Values),
                                        backgroundColor: color,
                                        borderColor: color,
                                        fill: false
                                    }]
                            };
                        }
                        if (key === 'browser' || key === 'os') {
                            metrics_1[key].datasets[0].backgroundColor = _this.ColorsService.getRandomColors(metrics_1[key].labels.length);
                            delete metrics_1[key].datasets[0].borderColor;
                        }
                    });
                    return metrics_1;
                }
                catch (e) {
                    console.error(e);
                }
            });
        };
        RepositoryService.prototype.getPolls = function () {
            return this.resource.polls({ action: 'totalpoll_insights_polls' }).$promise;
        };
        RepositoryService.prototype.getDownload = function (format, filters) {
            if (filters === void 0) { filters = {}; }
            var iframe = document.createElement('iframe');
            var query = this.$httpParamSerializerJQLike(angular.extend({}, {
                action: this.prefix + "_insights_download",
                poll: this.pollId,
                format: format
            }, filters));
            iframe.src = this.ajaxEndpoint + "&" + query;
            iframe.width = '0';
            iframe.height = '0';
            document.body.appendChild(iframe);
        };
        RepositoryService = __decorate([
            Service('services.totalpoll')
        ], RepositoryService);
        return RepositoryService;
    }());
    TotalPoll.RepositoryService = RepositoryService;
})(TotalPoll || (TotalPoll = {}));
///<reference path="../../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/chart.ts" />
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var InsightsSummaryComponent = /** @class */ (function () {
        function InsightsSummaryComponent(RepositoryService, $location, pollId) {
            this.RepositoryService = RepositoryService;
            this.$location = $location;
            this.pollId = pollId;
            this.filters = {};
            this.metrics = {};
            this.processing = true;
            this.filters.poll = pollId || null;
        }
        InsightsSummaryComponent.prototype.$onInit = function () {
            if (this.filters.poll) {
                this.loadMetrics();
            }
        };
        InsightsSummaryComponent.prototype.isProcessing = function () {
            return this.processing;
        };
        InsightsSummaryComponent.prototype.loadMetrics = function () {
            var _this = this;
            if (!this.filters.poll) {
                this.metrics = {};
                return false;
            }
            this.processing = true;
            this.RepositoryService
                .getMetrics(this.filters)
                .then(function (response) {
                _this.processing = false;
                _this.metrics = response;
                // @ts-ignore
                angular.forEach(_this.metrics.questions, function (question, uid) {
                    try {
                        _this.metrics[uid]['options'] = {
                            options: {
                                legend: { position: "bottom", align: "start", labels: { boxWidth: 16, padding: 8 } },
                                title: { display: false, "text": question['content'], position: "top", fontSize: 12 }
                            }
                        };
                    }
                    catch (e) {
                        console.log(e);
                    }
                });
            })
                .catch(function (response) {
                if (confirm("Something went wrong in the last operation (" + (response.status || '') + " " + (response.statusText || 'Unknown error') + "). Retry?")) {
                    _this.loadMetrics();
                }
            });
        };
        InsightsSummaryComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'insights-summary-component-template',
                bindings: {}
            })
            // @ts-ignore
        ], InsightsSummaryComponent);
        return InsightsSummaryComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
///<reference path="../../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/decorators.ts" />
///<reference path="../../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/settings.ts" />
var TotalPoll;
(function (TotalPoll) {
    var Controller = TotalCore.Common.Controller;
    var EditorCtrl = /** @class */ (function () {
        function EditorCtrl($rootScope, SettingsService, $location, TabService) {
            this.$rootScope = $rootScope;
            this.SettingsService = SettingsService;
            this.$location = $location;
            this.TabService = TabService;
            this.information = this.SettingsService.information;
            this.languages = this.SettingsService.languages;
            this.modules = this.SettingsService.modules;
            this.settings = this.SettingsService.settings;
            this.presets = this.SettingsService.presets;
            var urlParams = this.$location.search();
            if (!this.settings.expressions || angular.isArray(this.settings.expressions)) {
                this.settings.expressions = {};
            }
            $rootScope.settings = this.settings;
            $rootScope.information = this.information;
            $rootScope.modules = this.modules;
            $rootScope.languages = this.languages;
            $rootScope.$applyAsync(function () {
                if (!urlParams.tab) {
                    TabService.set('editor', 'questions');
                }
            });
        }
        EditorCtrl.prototype.prepareExpression = function (languageCode, rawExpression) {
            if (!this.settings.expressions[languageCode]) {
                this.settings.expressions[languageCode] = {};
            }
            if (!this.settings.expressions[languageCode][rawExpression]) {
                this.settings.expressions[languageCode][rawExpression] = { 'translations': [] };
            }
            return this.settings.expressions[languageCode][rawExpression];
        };
        EditorCtrl.prototype.setTimeout = function (timeout) {
            this.$rootScope.settings.vote.frequency.timeout = parseInt(timeout, 10);
        };
        EditorCtrl.prototype.isCustomTimeout = function () {
            return !(this.$rootScope.settings.vote.frequency.timeout in this.presets.timeout);
        };
        EditorCtrl = __decorate([
            Controller('controllers.totalpoll')
            // @ts-ignore
        ], EditorCtrl);
        return EditorCtrl;
    }());
    TotalPoll.EditorCtrl = EditorCtrl;
})(TotalPoll || (TotalPoll = {}));
///<reference path="../../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/decorators.ts" />
///<reference path="../../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/settings.ts" />
var TotalPoll;
(function (TotalPoll) {
    var Controller = TotalCore.Common.Controller;
    var PreviewCtrl = /** @class */ (function () {
        function PreviewCtrl($scope, $sce, SettingsService) {
            this.$scope = $scope;
            this.$sce = $sce;
            this.SettingsService = SettingsService;
            this.preview = {
                screen: 'vote',
                currentQuestion: 0,
                checked: {}
            };
            this.settings = this.SettingsService.settings;
        }
        PreviewCtrl.prototype.escape = function (content) {
            return this.$sce.trustAsHtml(content);
        };
        PreviewCtrl.prototype.getInputType = function (question) {
            return question.settings.selection.maximum > 1 ? 'checkbox' : 'radio';
        };
        PreviewCtrl.prototype.getPercentageWidthFor = function (question, choice) {
            question.votesOverride = 0;
            angular.forEach(question.choices, function (choice) {
                question.votesOverride += choice.votesOverride || 0;
            });
            return (choice.votesOverride / question.votesOverride) * 100 + "%";
        };
        PreviewCtrl.prototype.getScreen = function () {
            return this.preview.screen;
        };
        PreviewCtrl.prototype.getSlide = function () {
            return this.preview.currentQuestion;
        };
        PreviewCtrl.prototype.hasQuestion = function (index) {
            return Boolean(this.SettingsService.settings.questions[index]);
        };
        PreviewCtrl.prototype.isBackButtonVisible = function () {
            return this.isScreen('results') && !this.isNextButtonVisible();
        };
        PreviewCtrl.prototype.isChoiceSelected = function (question, choice) {
            return this.preview.checked[question.uid] && this.preview.checked[question.uid].indexOf(choice.uid) != -1;
        };
        PreviewCtrl.prototype.isNextButtonVisible = function () {
            return this.isSlider() && this.hasQuestion(this.getSlide() + 1);
        };
        PreviewCtrl.prototype.isPreviousButtonVisible = function () {
            return this.isSlider() && this.hasQuestion(this.getSlide() - 1);
        };
        PreviewCtrl.prototype.isQuestion = function (index) {
            return this.preview.currentQuestion === index;
        };
        PreviewCtrl.prototype.isQuestionVisible = function (index) {
            if (!this.hasQuestion(this.getSlide())) {
                this.setSlide(0);
            }
            return !this.isSlider() || this.isQuestion(index);
        };
        PreviewCtrl.prototype.isResultsButtonVisible = function () {
            return this.isScreen('vote') && this.SettingsService.settings.results.visibility == 'all' && !this.isNextButtonVisible();
        };
        PreviewCtrl.prototype.isScreen = function (screen) {
            return this.preview.screen === screen;
        };
        PreviewCtrl.prototype.isSlider = function () {
            return this.SettingsService.settings.design.behaviours.slider;
        };
        PreviewCtrl.prototype.isVoteButtonVisible = function () {
            return this.isScreen('vote') && !this.isNextButtonVisible();
        };
        PreviewCtrl.prototype.previewBack = function () {
            this.setSlide(0);
            this.setScreen('vote');
        };
        PreviewCtrl.prototype.previewNext = function () {
            var next = this.getSlide() + 1;
            if (this.hasQuestion(next)) {
                this.setSlide(next);
            }
        };
        PreviewCtrl.prototype.previewPrevious = function () {
            var previous = this.getSlide() - 1;
            if (this.hasQuestion(previous)) {
                this.setSlide(previous);
            }
        };
        PreviewCtrl.prototype.previewResults = function () {
            this.setSlide(0);
            this.setScreen('results');
        };
        PreviewCtrl.prototype.previewVote = function () {
            this.setSlide(0);
            this.setScreen('results');
        };
        PreviewCtrl.prototype.setScreen = function (screen) {
            this.preview.screen = screen;
        };
        PreviewCtrl.prototype.setSlide = function (index) {
            this.preview.currentQuestion = index;
        };
        PreviewCtrl.prototype.toggleChoice = function (question, choice) {
            var multiple = question.settings.selection.maximum > 1;
            if (!this.preview.checked[question.uid]) {
                this.preview.checked[question.uid] = [];
            }
            if (this.isChoiceSelected(question, choice)) {
                this.preview.checked[question.uid].splice(this.preview.checked[question.uid].indexOf(choice.uid), 1);
            }
            else {
                if (multiple) {
                    this.preview.checked[question.uid].push(choice.uid);
                }
                else {
                    this.preview.checked[question.uid] = [choice.uid];
                }
            }
        };
        PreviewCtrl = __decorate([
            Controller('controllers.totalpoll')
        ], PreviewCtrl);
        return PreviewCtrl;
    }());
    TotalPoll.PreviewCtrl = PreviewCtrl;
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Controller = TotalCore.Common.Controller;
    var SidebarIntegrationCtrl = /** @class */ (function () {
        function SidebarIntegrationCtrl(ajaxEndpoint, SettingsService) {
            this.ajaxEndpoint = ajaxEndpoint;
            this.SettingsService = SettingsService;
        }
        SidebarIntegrationCtrl.prototype.addWidgetToSidebar = function () {
            var _this = this;
            this.sidebar.inserted = true;
            jQuery
                .post(this.ajaxEndpoint, {
                action: 'totalpoll_polls_add_to_sidebar',
                poll: this.SettingsService.settings.id,
                sidebar: this.sidebar.id
            })
                .then(function (response) { return _this.sidebar.inserted = response.success || false; })
                .fail(function () { return _this.sidebar.inserted = false; });
        };
        SidebarIntegrationCtrl = __decorate([
            Controller('controllers.totalpoll')
        ], SidebarIntegrationCtrl);
        return SidebarIntegrationCtrl;
    }());
    TotalPoll.SidebarIntegrationCtrl = SidebarIntegrationCtrl;
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Controller = TotalCore.Common.Controller;
    var NotificationsCtrl = /** @class */ (function () {
        function NotificationsCtrl($rootScope, SettingsService) {
            this.$rootScope = $rootScope;
            this.SettingsService = SettingsService;
            this.pushCompleted = false;
        }
        NotificationsCtrl.prototype.setupPushService = function () {
            var _this = this;
            
        };
        ;
        NotificationsCtrl = __decorate([
            Controller('controllers.totalpoll')
        ], NotificationsCtrl);
        return NotificationsCtrl;
    }());
    TotalPoll.NotificationsCtrl = NotificationsCtrl;
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    // @ts-ignore
    var Controller = TotalCore.Common.Controller;
    var TranslationsCtrl = /** @class */ (function () {
        function TranslationsCtrl($scope) {
            this.$scope = $scope;
            this.language = null;
            this.placeholders = {};
            this.expressions = { original: window['TotalPollExpressions'] || {} };
            $scope.$watch('$ctrl.language', this.translationAware.bind(this));
            $scope.$watch('$root.settings.fields', this.translationAware.bind(this), true);
        }
        TranslationsCtrl.prototype.isCurrentLanguage = function (language) {
            return this.language.code === language.code;
        };
        TranslationsCtrl.prototype.setLanguage = function (language) {
            this.language = language;
        };
        TranslationsCtrl.prototype.translationAware = function () {
            var _this = this;
            if (this.language !== null) {
                this.$scope.settings.fields.forEach(function (field) {
                    if (field.options == undefined)
                        return;
                    if (field.translations == undefined) {
                        field.translations = {};
                    }
                    if (field.translations[_this.language.code] == undefined) {
                        field.translations[_this.language.code] = { options: {} };
                    }
                    var translated = {};
                    _this.parseOptions(field.options).forEach(function (item) {
                        _this.placeholders[field.uid + '-' + item[0]] = item[1];
                        translated[item[0]] = field.translations[_this.language.code].options[item[0]] || item[1];
                    });
                    field.translations[_this.language.code].options = translated;
                });
            }
        };
        TranslationsCtrl.prototype.parseOptions = function (options) {
            return options
                .split(/\n/g).map(function (option) { return option.split(':', 2).map(function (item) { return item.trim(); }); })
                .filter(function (item) { return item.toString().trim().length > 0; });
        };
        TranslationsCtrl = __decorate([
            Controller('controllers.totalpoll')
            // @ts-ignore
        ], TranslationsCtrl);
        return TranslationsCtrl;
    }());
    TotalPoll.TranslationsCtrl = TranslationsCtrl;
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Controller = TotalCore.Common.Controller;
    var RepeaterCtrl = /** @class */ (function () {
        function RepeaterCtrl() {
            this.items = [];
        }
        RepeaterCtrl.prototype.addItem = function (item) {
            if (item === void 0) { item = {}; }
            this.items.push(item);
        };
        RepeaterCtrl.prototype.deleteItem = function (index) {
            this.items.splice(index, 1);
        };
        RepeaterCtrl.prototype.moveDown = function (index) {
            this.moveUp(index + 1);
        };
        RepeaterCtrl.prototype.moveUp = function (index) {
            if (index === 0 || index === this.items.length) {
                return;
            }
            this.items.splice(index - 1, 0, this.items[index]);
            this.items.splice(index + 1, 1);
        };
        RepeaterCtrl = __decorate([
            Controller('controllers.totalpoll')
        ], RepeaterCtrl);
        return RepeaterCtrl;
    }());
    TotalPoll.RepeaterCtrl = RepeaterCtrl;
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var QuestionComponent = /** @class */ (function () {
        function QuestionComponent($rootScope, $scope) {
            this.$rootScope = $rootScope;
            this.$scope = $scope;
        }
        QuestionComponent.prototype.$onInit = function () {
        };
        QuestionComponent.prototype.checkSettings = function () {
            this.item.settings.selection.minimum = Math.abs(parseInt(this.item.settings.selection.minimum)) || 0;
            this.item.settings.selection.maximum = Math.abs(parseInt(this.item.settings.selection.maximum)) || 1;
            if (this.item.choices.length) {
                if (this.item.settings.selection.minimum > this.item.choices.length) {
                    this.item.settings.selection.minimum = this.item.choices.length;
                }
                if (this.item.settings.selection.maximum > this.item.choices.length) {
                    this.item.settings.selection.maximum = this.item.choices.length;
                }
            }
            if (this.item.settings.selection.minimum > this.item.settings.selection.maximum) {
                this.item.settings.selection.minimum = this.item.settings.selection.maximum;
            }
        };
        QuestionComponent.prototype.prefix = function (content, separator) {
            if (separator === void 0) { separator = '-'; }
            return "" + content + separator + this.item.uid;
        };
        QuestionComponent.prototype.suffix = function (content, separator) {
            if (separator === void 0) { separator = '-'; }
            return "" + this.item.uid + separator + content;
        };
        QuestionComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'question-component-template',
                bindings: {
                    item: '=',
                    index: '=',
                    onDelete: '&',
                }
            })
        ], QuestionComponent);
        return QuestionComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var QuestionsComponent = /** @class */ (function () {
        function QuestionsComponent(UniqueIdService, $sce) {
            this.UniqueIdService = UniqueIdService;
            this.$sce = $sce;
            this.bulkInsert = false;
            this.bulkQuestions = '';
            this.bulkChoices = '';
            this.bulkQuestionsAndChoices = [];
            this.bulkSeperator = '\\n\\n';
            this.previewQuestion = false;
        }
        QuestionsComponent.prototype.$onChanges = function () {
            if (!this.currentQuestion) {
                if (this.items.length === 0) {
                    this.addQuestion();
                }
                this.setCurrentQuestion(this.items.length - 1);
            }
        };
        QuestionsComponent.prototype.addQuestion = function () {
            this.items.push({
                uid: this.UniqueIdService.generate(),
                content: '',
                settings: {
                    selection: {
                        minimum: 1,
                        maximum: 1,
                    }
                },
                choices: []
            });
            this.setCurrentQuestion(this.items.length - 1);
        };
        QuestionsComponent.prototype.escape = function (content) {
            return this.$sce.trustAsHtml(String(content).replace(/<[^>]+>/gm, '').slice(0, 100).concat('...'));
        };
        QuestionsComponent.prototype.showBulkInsert = function () {
            this.bulkInsert = true;
        };
        QuestionsComponent.prototype.hideBulkInsert = function () {
            this.bulkInsert = false;
        };
        QuestionsComponent.prototype.parseBulkInsert = function () {
            var _this = this;
            this.bulkQuestionsAndChoices = [];
            var questions = this.bulkQuestions.split(this.bulkSeperator.replaceAll('\\n', "\n"));
            var genericChoices = this.bulkChoices.split("\n");
            for (var _i = 0, questions_1 = questions; _i < questions_1.length; _i++) {
                var question = questions_1[_i];
                var parts = question.split("\n");
                var questionContent = parts.filter(function (part) { return part.charAt(0) !== '*' && part.trim(); }).join('\n');
                if (questionContent) {
                    this.bulkQuestionsAndChoices.push({
                        uid: this.UniqueIdService.generate(),
                        content: questionContent,
                        settings: {
                            selection: {
                                minimum: 1,
                                maximum: 1,
                            }
                        },
                        choices: parts.filter(function (part) { return part.charAt(0) === '*'; })
                            .map(function (part) {
                            return {
                                uid: _this.UniqueIdService.generate(),
                                type: 'text',
                                label: part.slice(1).trim(),
                                votes: 0,
                                votesOverride: 0,
                                collapsed: false,
                                visibility: true
                            };
                        }).concat(genericChoices.map(function (genericChoice) {
                            return {
                                uid: _this.UniqueIdService.generate(),
                                type: 'text',
                                label: genericChoice.trim(),
                                votes: 0,
                                votesOverride: 0,
                                collapsed: false,
                                visibility: true
                            };
                        })).filter(function (choice) { return choice && choice.label; })
                    });
                }
            }
        };
        QuestionsComponent.prototype.processBulkInsert = function () {
            this.hideBulkInsert();
            (_a = this.items).push.apply(_a, this.bulkQuestionsAndChoices);
            this.setCurrentQuestion(this.items.length - 1);
            var _a;
        };
        QuestionsComponent.prototype.deleteQuestion = function (questionIndex, confirmed, resetPosition) {
            if (confirmed === void 0) { confirmed = false; }
            if (resetPosition === void 0) { resetPosition = true; }
            if (confirmed || confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items.splice(questionIndex, 1);
                if (resetPosition) {
                    questionIndex = questionIndex > 0 ? questionIndex - 1 : questionIndex;
                    this.setCurrentQuestion(questionIndex);
                }
            }
        };
        QuestionsComponent.prototype.getCurrentQuestion = function () {
            return this.currentQuestion;
        };
        QuestionsComponent.prototype.isCurrentQuestion = function (uid) {
            return this.currentQuestion && this.currentQuestion.uid === uid;
        };
        QuestionsComponent.prototype.setCurrentQuestion = function (index) {
            this.currentQuestion = this.items[index];
        };
        QuestionsComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'questions-component-template',
                bindings: {
                    items: '=',
                }
            })
        ], QuestionsComponent);
        return QuestionsComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var Slug = TotalCore.Common.Filters.Slug;
    var FieldComponent = /** @class */ (function () {
        function FieldComponent(MediaUploadService, EmbedService, $sce) {
            this.MediaUploadService = MediaUploadService;
            this.EmbedService = EmbedService;
            this.$sce = $sce;
            this.processing = false;
        }
        FieldComponent.prototype.$onInit = function () {
            if (angular.isArray(this.item.validations)) {
                this.item.validations = {};
            }
            if (angular.isArray(this.item.attributes)) {
                this.item.attributes = {};
            }
        };
        FieldComponent.prototype.generateName = function () {
            if (!this.item.name) {
                this.item.name = Slug.filter(this.item.label, '_');
            }
        };
        FieldComponent.prototype.isCollapsed = function () {
            return this.item.collapsed;
        };
        FieldComponent.prototype.prefix = function (content, separator) {
            if (separator === void 0) { separator = '-'; }
            return "" + this.item.uid + separator + content;
        };
        FieldComponent.prototype.suffix = function (content, separator) {
            if (separator === void 0) { separator = '-'; }
            return "" + content + separator + this.item.uid;
        };
        FieldComponent.prototype.toggleCollapsed = function () {
            this.item.collapsed = !this.item.collapsed;
        };
        FieldComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'field-component-template',
                bindings: {
                    item: '=',
                    index: '=',
                    onDelete: '&',
                }
            })
        ], FieldComponent);
        return FieldComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var FieldsComponent = /** @class */ (function () {
        function FieldsComponent($rootScope, UniqueIdService) {
            this.$rootScope = $rootScope;
            this.UniqueIdService = UniqueIdService;
            this.bulkContent = '';
            this.bulkInput = false;
            this.presets = [];
            this.presets.push({
                pattern: /name/gi,
                type: 'text',
            });
            this.presets.push({
                pattern: /mail/gi,
                type: 'text',
                validations: { filled: { enabled: true }, email: { enabled: true } }
            });
            
        }
        FieldsComponent.prototype.$onInit = function () {
        };
        FieldsComponent.prototype.collapseFields = function () {
            this.setCollapsedForAll(true);
        };
        FieldsComponent.prototype.deleteField = function (fieldIndex, confirmed) {
            if (confirmed === void 0) { confirmed = false; }
            if (confirmed || confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items.splice(fieldIndex, 1);
            }
        };
        FieldsComponent.prototype.deleteFields = function () {
            if (confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items = [];
            }
        };
        FieldsComponent.prototype.expandFields = function () {
            this.setCollapsedForAll(false);
        };
        FieldsComponent.prototype.insertBulkFields = function () {
            var _this = this;
            
        };
        FieldsComponent.prototype.insertField = function (args) {
            this.collapseFields();
            args = angular.extend({}, {
                uid: this.UniqueIdService.generate(),
                type: 'text',
                label: 'Field',
                defaultValue: '',
                options: '',
                collapsed: false,
                validations: {},
                attributes: {},
                template: '',
            }, args);
            this.items.push(args);
        };
        FieldsComponent.prototype.insertFieldFromPreset = function (field) {
            var args = {
                type: 'text',
                label: field.replace(/\W/g, ' ').trim(),
                name: field.replace(/\W/g, '').toLowerCase(),
                validations: { filled: { enabled: /\*/g.test(field) } },
            };
            if (args.name !== '') {
                for (var _i = 0, _a = this.presets; _i < _a.length; _i++) {
                    var preset = _a[_i];
                    if (args.name.match(preset.pattern)) {
                        args = angular.extend({}, args, preset);
                        delete args.pattern;
                        break;
                    }
                }
                this.insertField(args);
            }
        };
        // COLLAPSING
        FieldsComponent.prototype.setCollapsedForAll = function (collapsed) {
            this.items.forEach(function (item) {
                item.collapsed = collapsed;
            });
        };
        FieldsComponent.prototype.toggleBulkInput = function () {
            
        };
        FieldsComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'fields-component-template',
                bindings: {
                    items: '=',
                }
            })
        ], FieldsComponent);
        return FieldsComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var ChoiceComponent = /** @class */ (function () {
        function ChoiceComponent(MediaUploadService, EmbedService, $sce, $scope) {
            this.MediaUploadService = MediaUploadService;
            this.EmbedService = EmbedService;
            this.$sce = $sce;
            this.$scope = $scope;
            this.processing = false;
            this.questions = this.$scope.$parent.$parent.$parent.$parent.$parent.$ctrl.items;
            this.choiceQuestion = this.$scope.$parent.$parent.$parent.$ctrl.item;
        }
        ChoiceComponent.prototype.convertTo = function (type) {
            this.item.type = type;
        };
        ChoiceComponent.prototype.moveToQuestion = function (questionIndex) {
            var _this = this;
            this.$scope.$applyAsync(function () {
                _this.$scope.$parent.$parent.$parent.$parent.$parent.$ctrl.items[questionIndex].choices.push(_this.item);
                _this.$scope.$parent.$parent.$ctrl.deleteChoice(_this.index, true);
                _this.$scope.$parent.$parent.$parent.$parent.$parent.$apply();
                _this.$scope.$parent.$parent.$apply();
            });
        };
        ChoiceComponent.prototype.$onInit = function () {
        };
        ChoiceComponent.prototype.discover = function (url) {
            var _this = this;
            
        };
        ChoiceComponent.prototype.dismissEmbed = function () {
            this.embed = null;
        };
        ChoiceComponent.prototype.escape = function (content) {
            return this.$sce.trustAsHtml(content);
        };
        ChoiceComponent.prototype.importEmbed = function () {
            this.item.label = this.embed.title;
            this.item[this.item.type] = {
                full: this.embed.url,
                thumbnail: this.embed.thumbnail_url,
                html: this.embed.html
            };
            this.embed = null;
        };
        ChoiceComponent.prototype.isCollapsed = function () {
            return this.item.collapsed;
        };
        ChoiceComponent.prototype.isVisible = function () {
            return this.item.visibility;
        };
        ChoiceComponent.prototype.prefix = function (content, separator) {
            if (separator === void 0) { separator = '-'; }
            return "" + this.item.uid + separator + content;
        };
        ChoiceComponent.prototype.suffix = function (content, separator) {
            if (separator === void 0) { separator = '-'; }
            return "" + content + separator + this.item.uid;
        };
        ChoiceComponent.prototype.toggleCollapsed = function () {
            this.item.collapsed = !this.item.collapsed;
        };
        ChoiceComponent.prototype.toggleVisibility = function () {
            this.item.visibility = !this.item.visibility;
        };
        ChoiceComponent.prototype.upload = function (context, type) {
            var _this = this;
            if (context === void 0) { context = 'full'; }
            if (type === void 0) { type = this.item.type; }
            
        };
        ChoiceComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'choice-component-template',
                bindings: {
                    item: '=',
                    index: '=',
                    onDelete: '&',
                    onConvert: '&',
                    onOverrideVotes: '&',
                }
            })
        ], ChoiceComponent);
        return ChoiceComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var shuffle = TotalCore.Common.shuffle;
    var ChoicesComponent = /** @class */ (function () {
        function ChoicesComponent(MediaUploadService, $scope, $element, UniqueIdService) {
            this.MediaUploadService = MediaUploadService;
            this.$scope = $scope;
            this.$element = $element;
            this.UniqueIdService = UniqueIdService;
            this.bulkContent = '';
            this.bulkInput = false;
            this.droppable = false;
            this.filterList = false;
            this.overrideVotes = false;
            this.types = {
                text: true,
                image: true,
                video: true,
                audio: true,
                html: true,
            };
        }
        ChoicesComponent.prototype.$onInit = function () {
            var _this = this;
            setTimeout(function () { return _this.handleDrop(); }, 3000);
        };
        ChoicesComponent.prototype.collapseChoices = function () {
            this.setCollapsedForAll(true);
        };
        ChoicesComponent.prototype.confirmOverride = function ($event) {
            if (this.overrideVotes) {
                return;
            }
            if (confirm(window['TotalPollI18n']['ATTENTION! Overriding votes is not reversible. Are you sure you want to override votes?'])) {
                this.overrideVotes = true;
            }
            else {
                $event.preventDefault();
                $event.target.blur();
            }
        };
        // CHOICES
        ChoicesComponent.prototype.deleteChoice = function (choiceIndex, confirmed) {
            if (confirmed === void 0) { confirmed = false; }
            if (confirmed || confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items.splice(choiceIndex, 1);
            }
        };
        ChoicesComponent.prototype.deleteChoices = function () {
            if (confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items = [];
            }
        };
        ChoicesComponent.prototype.expandChoices = function () {
            this.setCollapsedForAll(false);
        };
        ChoicesComponent.prototype.filterByType = function () {
            var _this = this;
            return function (choice) {
                return _this.isTypeActive(choice.type);
            };
        };
        // INSERTION
        ChoicesComponent.prototype.insertBulkChoices = function () {
            var _this = this;
            
        };
        ChoicesComponent.prototype.insertChoice = function (args) {
            this.collapseChoices();
            args = angular.extend({}, {
                uid: this.UniqueIdService.generate(),
                type: 'text',
                label: "Choice #" + (this.items.length + 1),
                votes: 0,
                votesOverride: 0,
                collapsed: false,
                visibility: true
            }, args);
            this.items.push(args);
            this.types[args.type] = true;
        };
        ChoicesComponent.prototype.isTypeActive = function (type) {
            return Boolean(this.types[type]);
        };
        ChoicesComponent.prototype.randomVotes = function () {
            if (confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items.forEach(function (item) {
                    item.votesOverride = Math.floor(Math.random() * (100 - 1 + 1)) + 1;
                });
            }
        };
        // VOTES
        ChoicesComponent.prototype.resetVotes = function () {
            if (confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items.forEach(function (item) {
                    item.votesOverride = 0;
                });
            }
        };
        // COLLAPSING
        ChoicesComponent.prototype.setCollapsedForAll = function (collapsed) {
            this.items.forEach(function (item) {
                item.collapsed = collapsed;
            });
        };
        ChoicesComponent.prototype.shuffleChoices = function () {
            if (confirm(window['TotalPollI18n']['Are you sure?'])) {
                this.items = shuffle(this.items);
            }
        };
        // TOGGLE
        ChoicesComponent.prototype.toggleBulkInput = function () {
            
        };
        ChoicesComponent.prototype.toggleFilterList = function () {
            
        };
        ChoicesComponent.prototype.toggleType = function (type) {
            this.types[type] = !this.isTypeActive(type);
        };
        ChoicesComponent.prototype.handleDrop = function () {
            var _this = this;
            var cancelDefault = function (event) {
                event.preventDefault();
                event.stopPropagation();
            };
            var setDroppable = function (droppable) {
                _this.$scope.$applyAsync(function () { return _this.droppable = droppable; });
            };
            this.media = this.MediaUploadService.customFrame({
                frame: 'post',
                state: 'insert',
                multiple: true
            });
            this.media.open().on('uploader:ready', function () { return _this.mediaUploader = _this.media.uploader.uploader.uploader; }).close();
            this.$element.on('drag', function (event) { return cancelDefault(event); });
            this.$element.on('dragleave', function (event) { return cancelDefault(event) && setDroppable(false); });
            this.$element.on('dragover', function (event) {
                cancelDefault(event);
                event.originalEvent.dataTransfer.dropEffect = 'copy';
                if (event.originalEvent.dataTransfer.types.indexOf('application/x-dnd') === -1) {
                    setDroppable(true);
                }
            });
            this.$element.on('drop', function (event) {
                cancelDefault(event);
                angular.forEach(event.originalEvent.dataTransfer.items, function (item) {
                    if (item.type === 'text/plain') {
                        item.getAsString(function (content) {
                            var choices = content.trim().split(/\r|\n/).filter(Boolean);
                            angular.forEach(choices, function (choice) { return _this.insertChoice({ type: 'text', label: choice.trim() }); });
                        });
                    }
                    else if (item.type === 'text/html') {
                        
                    }
                    else if (['image', 'video', 'audio'].indexOf(item.type.split('/').shift()) != -1 && item.kind === 'file') {
                        
                    }
                });
                setDroppable(false);
                _this.collapseChoices();
                return false;
            });
            
        };
        ChoicesComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'choices-component-template',
                bindings: {
                    items: '=',
                }
            })
        ], ChoicesComponent);
        return ChoicesComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var ProgressiveTextareaComponent = /** @class */ (function () {
        function ProgressiveTextareaComponent() {
            this.mode = 'simple';
        }
        ProgressiveTextareaComponent.prototype.isAdvanced = function () {
            return this.mode === 'advanced';
        };
        ProgressiveTextareaComponent.prototype.isSimple = function () {
            return this.mode === 'simple';
        };
        ProgressiveTextareaComponent.prototype.switchToAdvanced = function () {
            this.mode = 'advanced';
        };
        ProgressiveTextareaComponent.prototype.switchToSimple = function () {
            this.mode = 'simple';
        };
        ProgressiveTextareaComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'progressive-textarea-template',
                bindings: {
                    model: '=ngModel',
                    rows: '@',
                    uid: '@',
                }
            })
        ], ProgressiveTextareaComponent);
        return ProgressiveTextareaComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var FeedbackCollectorComponent = /** @class */ (function () {
        function FeedbackCollectorComponent($scope) {
            var _this = this;
            this.$scope = $scope;
            this.visible = false;
            this.score = 0;
            this.email = '';
            this.comment = '';
            this.STATE_KEY = 'totalpoll-marketing';
            this._state = angular.merge({
                feedback: {
                    collected: false,
                    lastRequestAt: null,
                }
            }, JSON.parse(localStorage.getItem(this.STATE_KEY)) || {});
            if (this.shouldDisplayFeedbackForm()) {
                setTimeout(function () {
                    _this.$scope.$applyAsync(function () {
                        _this.visible = true;
                    });
                }, 1000);
            }
        }
        FeedbackCollectorComponent.prototype.isScore = function (score) {
            return this.score == score;
        };
        FeedbackCollectorComponent.prototype.setScore = function (score) {
            return this.score = score;
        };
        FeedbackCollectorComponent.prototype.isCommentNeeded = function () {
            return this.score > 0 && this.score < 7;
        };
        Object.defineProperty(FeedbackCollectorComponent.prototype, "state", {
            get: function () {
                return this._state;
            },
            set: function (state) {
                localStorage.setItem(this.STATE_KEY, JSON.stringify(state));
            },
            enumerable: true,
            configurable: true
        });
        FeedbackCollectorComponent.prototype.refreshState = function () {
            this.state = this.state;
            return this.state;
        };
        FeedbackCollectorComponent.prototype.shouldDisplayFeedbackForm = function () {
            if (this.state.feedback.lastRequestAt === null) {
                this.state.feedback.lastRequestAt = Date.now();
                this.refreshState();
            }
            return !this.state.feedback.collected &&
                (Date.now() - this.state.feedback.lastRequestAt) > (7 * 24 * 60 * 60 * 1000);
        };
        FeedbackCollectorComponent.prototype.markFeedbackAsCollected = function () {
            wp.ajax.send({
                url: window['totalpollAjaxURL'],
                data: {
                    action: 'totalpoll_nps',
                    nps: {
                        score: this.score,
                        email: this.email,
                        comment: this.comment
                    }
                },
            });
            this.visible = false;
            this.state.feedback.collected = true;
            this.state.feedback.lastRequestAt = Date.now();
            this.refreshState();
        };
        FeedbackCollectorComponent.prototype.postponeFeedback = function () {
            this.visible = false;
            this.state.feedback.lastRequestAt = Date.now();
            this.refreshState();
        };
        FeedbackCollectorComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'feedback-collector-component-template',
                bindings: {}
            })
        ], FeedbackCollectorComponent);
        return FeedbackCollectorComponent;
    }());
})(TotalPoll || (TotalPoll = {}));
///<reference path="../../../../build/typings/index.d.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/decorators.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/helpers.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/configs/global.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/configs/http.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/settings.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/media-upload.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/embed.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/uid.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/tab.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/datetimepicker.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/colorpicker.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/tinymce.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/copy-to-clipboard.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/filters/slug.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/filters/i18n.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/chart.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/providers/colors.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/common/directives/click-tracker.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/customizer/components/customizer.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/customizer/components/customizer-control.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/customizer/components/customizer-tabs.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/customizer/components/customizer-tab.ts" />
///<reference path="../../../../vendor/misqtech/totalsuite-totalcore/assets/scripts/customizer/components/customizer-tab-content.ts" />
///<reference path="../insights/providers/repository.ts" />
///<reference path="components/insights.ts" />
///<reference path="controllers/editor.ts" />
///<reference path="controllers/preview.ts" />
///<reference path="controllers/sidebar-integration.ts" />
///<reference path="controllers/notifications.ts" />
///<reference path="controllers/translations.ts" />
///<reference path="controllers/repeater.ts" />
///<reference path="components/question.ts" />
///<reference path="components/questions.ts" />
///<reference path="components/field.ts" />
///<reference path="components/fields.ts" />
///<reference path="components/choice.ts" />
///<reference path="components/choices.ts" />
///<reference path="components/progressive-textarea.ts" />
///<reference path="components/feedback-collector.ts" />
var TotalPoll;
(function (TotalPoll) {
    var GlobalConfig = TotalCore.Common.Configs.GlobalConfig;
    var HttpConfig = TotalCore.Common.Configs.HttpConfig;
    TotalPoll.pollEditor = angular
        .module('poll-editor', [
        'ngResource',
        'dndLists',
        'services.common',
        'directives.common',
        'filters.common',
        'services.totalpoll',
        'components.customizer',
        'components.totalpoll',
        'controllers.totalpoll',
    ])
        .config(GlobalConfig)
        .config(HttpConfig)
        .value('ajaxEndpoint', window['totalpollAjaxURL'] || window['ajaxurl'] || '/wp-admin/admin-ajax.php')
        .value('namespace', 'TotalPoll')
        .value('prefix', 'totalpoll')
        .value('pollId', window['TotalPollInsights']['pollId'] || null);
    angular.element(document).ready(function () {
        angular.bootstrap(document.getElementById('totalpoll-insights'), ['poll-editor']);
    });
})(TotalPoll || (TotalPoll = {}));

//# sourceMappingURL=maps/poll-editor.js.map
