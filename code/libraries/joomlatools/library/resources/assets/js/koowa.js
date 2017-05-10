/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

if(!Koowa) {
    /** @namespace */
    var Koowa = {};
}

(function($) {

$(function() {
    $('.k-js-submittable').on('click.koowa', function(event){
        event.preventDefault();

        new Koowa.Form($(event.target).data('config')).submit();
    });

    $('.k-js-grid-controller').each(function() {
        new Koowa.Controller.Grid({
            form: this
        });
    });

    $('.k-js-form-controller').each(function() {
        new Koowa.Controller.Form({
            form: this
        });
    });
});

if (!Koowa.Translator) {
    Koowa.Translator = Koowa.Class.extend({
        translations: {},
        translate: function(string, parameters) {
            if (typeof this.translations[string.toLowerCase()] !== 'undefined') {
                string = this.translations[string.toLowerCase()];
            }

            if (typeof parameters === 'object' && parameters !== null) {
                for (var key in parameters) {
                    if (parameters.hasOwnProperty(key)) {
                        // Escape for regular expression
                        var pattern = '{'+key+'}'.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");

                        string  = string.replace(new RegExp(pattern, 'g'), parameters[key]);
                    }
                }
            }

            return string;
        },
        loadTranslations: function(object) {
            for (var string in object) {
                if (object.hasOwnProperty(string)) {
                    this.translations[string.toLowerCase()] = object[string];
                }
            }

            return this;
        }
    });

    Koowa.translator = new Koowa.Translator();
    Koowa.translate = Koowa.translator.translate.bind(Koowa.translator);
}

/**
 * Creates a 'virtual form'
 *
 * @param   {object} config Configuration object. Accepted keys: method, url, params, element
 * @example new Koowa.Form({url:'foo=bar&id=1', params:{field1:'val1', field2...}}).submit();
 * @extends Koowa.Class
 */
Koowa.Form = Koowa.Class.extend({
    initialize: function(config) {
        this.config = config;
        if(this.config.element) {
            this.form = $(document[this.config.element]);
        }
        else {
            this.form = $('<form/>', {
                name: 'dynamicform',
                method: this.config.method || 'POST',
                action: this.config.url
            });
            $(document.body).append(this.form);
        }
    },
    addField: function(name, value) {
        if ($.isArray(value)) {
            var self = this,
                n;

            if (name.substr(-2) === '[]') {
                name = name.substr(0, name.length-2);
            }

            $.each(value, function(i, v) {
                n = name+'['+i+']';
                self.addField(n, v);
            });
        } else {
            var elem = $('<input/>', {
                name: name,
                value: value,
                type: 'hidden'
            });
            elem.appendTo(this.form);
        }

        return this;
    },

    submit: function() {
        var self = this;

        if (this.config.params) {
            $.each(this.config.params, function(name, value){
                self.addField(name, value);
            });
        }

        this.form.submit();
    }
});

/**
 * Controller class, execute actions complete with command chains
 */
Koowa.Controller = Koowa.Class.extend({
    form: null,
    toolbar: null,
    buttons: null,

    token_name: null,
    token_value: null,
    /**
     * @returns {object}
     */
    getOptions: function() {
        return $.extend(this.supr(), {
            toolbar: '.k-toolbar',
            url: window.location.href
        });
    },
    initialize: function(options){
        var self = this;

        this.supr();
        this.setOptions(options);

        this.form = $(this.options.form);

        this.setOptions(this.form.data());

        if (this.form.prop('action')) {
            this.options.url = this.form.attr('action');
        }

        this.toolbar = $(this.options.toolbar);
        this.form.data('controller', this);

        this.on('execute', function(){
            return self.execute.apply(self, arguments);
        });

        this.token_name = this.form.data('token-name');
        this.token_value = this.form.data('token-value');

        if(this.toolbar) {
            this.setToolbar();
        }
    },
    setToolbar: function() {
        var self = this;

        this.buttons = this.toolbar.find('.toolbar[data-action]');

        this.buttons.each(function() {
            var button = $(this),
                context = {},
                options = button.data(),
                data = options.data;

            if (options.eventAdded) {
                return;
            }

            if (typeof data !== 'object') {
                data = (data && $.type(data) === 'string') ? $.parseJSON(data) : {};
            }

            //Set token data
            if (self.token_name) {
                data[self.token_name] = self.token_value;
            }

            context.validate = options.novalidate !== 'novalidate';
            context.data   = data;
            context.action = options.action;

            button.on('click.koowa', function(event) {
                event.preventDefault();

                context.trigger = button;

                if (!button.hasClass('k-is-disabled')) {
                    var prompt = button.data('prompt');

                    if (prompt && !confirm(prompt)) {
                        return;
                    }

                    self.setOptions(options);
                    self.trigger('execute', [context]);
                }
            });

            button.data('event-added', true);
        });
    },
    execute: function(event, context){
        if (context.action[0]) {
            var action   = context.action[0].toUpperCase() + context.action.substr(1),
                method = '_action' + action;

            if (typeof context.validate === 'undefined') {
                context.validate = true;
            }

            if (this.trigger('before'+action, context)) {
                method = this[method] ? method : '_actionDefault';

                this[method].call(this, context);

                this.trigger('after'+action, context);
            }
        }

        return this;
    },
    on: function(type, fn){
        return this.form.on('koowa:'+type, fn);
    },

    off: function(type, fn){
        return this.form.off('koowa:'+type, fn);
    },

    trigger: function(type, args){
        var event = $.Event('koowa:'+type);
        this.form.trigger(event, args);
        return !event.isDefaultPrevented();
    },

    checkValidity: function(){
        var buttons;

        if (this.buttons) {
            this.trigger('beforeValidate');

            buttons = this.buttons.filter('[data-novalidate!="novalidate"]');

            if (this.trigger('validate')) {
                buttons.removeClass('k-is-disabled');
            } else {
                buttons.addClass('k-is-disabled');
            }

            this.trigger('afterValidate');
        }
    }
});

/**
 * Controller class specialized for grids, extends Koowa.Controller
 *
 * @package     Koowa_Media
 * @subpackage  Javascript
 */
Koowa.Controller.Grid = Koowa.Controller.extend({
    getOptions: function() {
        return $.extend(this.supr(), {
            inputs: '.k-js-grid-checkbox, .k-js-grid-checkall',
            ajaxify: false
        });
    },
    initialize: function(options){
        var thead,
            self = this;

        this.supr(options);

        this.grid = new Koowa.Grid(this.form);

        this.on('validate', this.validate);

        if (this.options.inputs && this.buttons) {
            this.checkValidity();
            this.form.find(this.options.inputs).on('change.koowa', function(event, ignore){
                if (!ignore) {
                    self.checkValidity();
                }
            });
        }

        this.token_name = this.form.data('token-name');
        this.token_value = this.form.data('token-value');

        this.setTableRows();

        // <select> elements in headers and footers are for filters, so they need to submit the form on change
        this.form.find('thead select, tfoot select, .k-pagination select').on('change.koowa', function(){
            // We need to uncheck rows here otherwise only selected rows will be visible after submitting the form
            self.grid.uncheckAll();

            if (self.options.ajaxify) {
                event.preventDefault();

                self.options.transport(self.options.url, self.form.serialize(), 'get');
            }

            self.form.submit();
        });

    },


    setTableRows: function() {
        var self = this,
            checkboxes = this.form.find('tbody tr .k-js-grid-checkbox');

        this.form.find('tbody tr').each(function(){
            var tr = $(this),
                checkbox = tr.find('.k-js-grid-checkbox');

            if(tr.data('readonly') == true || !checkbox.length) {
                return;
            }

            // Set up buttons such as publish/unpublish triggers
            tr.find('[data-action]').each(function() {
                var action = $(this),
                    context = {},
                    data = action.data('data'),
                    options = action.data(),
                    eventType = action.data('event-type');

                if (typeof data !== 'object') {
                    data = (data && $.type(data) === 'string') ? $.parseJSON(data) : {};
                }

                //Set token data
                if(self.token_name) {
                    data[self.token_name] = self.token_value;
                }

                if(!eventType) {
                    eventType = action.is('[type="radio"],[type="checkbox"],select') ? 'change' : 'click';
                }

                context.validate = options.novalidate !== 'novalidate';
                context.data   = data;
                context.action = options.action;

                action.on(eventType+'.k-ui-namespace', function(){
                    checkboxes.prop('checked', '');
                    checkbox.prop('checked', 'checked');
                    checkboxes.trigger('change', true);

                    context.trigger = action;

                    self.setOptions(options);
                    self.trigger('execute', [context]);
                });
            });
        });
    },
    validate: function(){
        return Koowa.Grid.getIdQuery() || false;
    },

    _actionDelete: function(context) {
        context.method = 'delete';

        return this._actionDefault(context);
    },

    _actionDefault: function(context) {
        var idQuery = Koowa.Grid.getIdQuery(),
            append  = this.options.url.match(/\?/) ? '&' : '?',
            options;

        if (context.validate && !this.trigger('validate', [context])) {
            return false;
        }

        options = {
            method:'post',
            url: this.options.url+(idQuery ? append+idQuery : ''),
            params: $.extend({}, {_action: context.action}, context.data)
        };

        if (context.method) {
            options.params._method = context.method;
        }

        new Koowa.Form(options).submit();
    }
});

/**
 * Controller class specialized for forms, extends Koowa.Controller
 */
Koowa.Controller.Form = Koowa.Controller.extend({
    _actionDefault: function(context){
        if (context.validate && !this.trigger('validate', [context])) {
            return false;
        }

        this.form.append($('<input/>', {name: '_action', type: 'hidden', value: context.action}));

        this.trigger('submit', [context]);
        this.form.submit();
    }

});

})(window.kQuery);
