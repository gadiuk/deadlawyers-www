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
var TotalPoll;
(function (TotalPoll) {
    var Component = TotalCore.Common.Component;
    var InsightsBrowserComponent = /** @class */ (function () {
        function InsightsBrowserComponent(RepositoryService, $location, pollId) {
            this.RepositoryService = RepositoryService;
            this.$location = $location;
            this.pollId = pollId;
            this.filters = {};
            this.metrics = {};
            this.polls = {};
            this.processing = true;
            var urlParams = $location.search();
            this.filters.poll = Number(urlParams['poll']) || pollId || null;
            this.filters.from = urlParams['from'] || null;
            this.filters.to = urlParams['to'] || null;
        }
        InsightsBrowserComponent.prototype.$onInit = function () {
            this.loadPolls();
            if (this.filters.poll) {
                this.loadMetrics();
            }
        };
        InsightsBrowserComponent.prototype.isProcessing = function () {
            return this.processing;
        };
        InsightsBrowserComponent.prototype.loadMetrics = function () {
            var _this = this;
            if (!this.filters.poll) {
                this.updateUrl();
                this.metrics = {};
                return false;
            }
            this.processing = true;
            this.RepositoryService
                .getMetrics(this.filters)
                .then(function (response) {
                _this.processing = false;
                _this.metrics = response;
                _this.updateUrl();
            })
                .catch(function (response) {
                if (confirm("Something went wrong in the last operation (" + (response.status || '') + " " + (response.statusText || 'Unknown error') + "). Retry?")) {
                    _this.loadMetrics();
                }
            });
        };
        InsightsBrowserComponent.prototype.loadPolls = function () {
            var _this = this;
            this.processing = true;
            this.RepositoryService
                .getPolls()
                .then(function (response) {
                _this.processing = false;
                _this.polls = response;
            })
                .catch(function (response) {
                if (confirm("Something went wrong in the last operation (" + response.status + " " + (response.statusText || 'Unknown error') + "). Retry?")) {
                    _this.loadPolls();
                }
            });
        };
        InsightsBrowserComponent.prototype.resetFilters = function () {
            this.filters.from = null;
            this.filters.to = null;
            this.loadMetrics();
        };
        InsightsBrowserComponent.prototype.updateUrl = function () {
            this.$location.search(angular.extend({}, this.$location.search(), this.filters));
        };
        InsightsBrowserComponent.prototype.canExport = function () {
            return this.polls.length > 0;
        };
        InsightsBrowserComponent.prototype.exportAs = function (format) {
            this.RepositoryService.getDownload(format, this.filters);
        };
        InsightsBrowserComponent = __decorate([
            Component('components.totalpoll', {
                templateUrl: 'insights-browser-component-template',
                bindings: {}
            })
        ], InsightsBrowserComponent);
        return InsightsBrowserComponent;
    }());
})(TotalPoll || (TotalPoll = {}));


//# sourceMappingURL=maps/insights.js.map
