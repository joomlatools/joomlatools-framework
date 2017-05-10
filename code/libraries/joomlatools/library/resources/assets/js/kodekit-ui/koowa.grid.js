/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

if (typeof Koowa === 'undefined') {
    Koowa = {};
}


(function($){
/**
 * Grid class
 */
Koowa.Grid = Koowa.Class.extend({
    initialize: function(element){
        var self = this;

        this.element    = $(element);
        this.form       = this.element.is('form') ? this.element : this.element.closest('form');
        this.checkall   = this.element.find('.k-js-grid-checkall');
        this.checkboxes = this.element.find('.k-js-grid-checkbox').filter(function(i, checkbox) {
            return !$(checkbox).prop('disabled');
        });

        if(!this.checkboxes.length) {
            this.checkall.prop('disabled', true);
        }

        this.checkall.on('change.koowa', function(event, ignore){
            if(!ignore) {
                self.checkAll($(this).prop('checked'));
            }
        });

        this.checkboxes.on('change.koowa', function(event, ignore){
            if(!ignore) {
                self.setCheckAll();
            }
        });

        this.setScopebar();
        this.setTableHeaders();
        this.setTableRows();
    },
    setScopebar: function() {
        var self = this;
        $('.k-js-filter-container', this.form).scopebar({
            beforeSubmitForm: function () {
                self.uncheckAll();
            }
        });
    },
    setTableHeaders: function() {
        //Make the table headers "clickable" and make checkall work
        this.form.on('click.koowa', 'th', function(event) {
            var $target = $(event.target);
            var link     = $target.find('a');

            if (link.length) {
                //Run this check on click, so that progressive enhancements isn't bulldozed
                if(link.prop('href')) {
                    window.location.href = link.prop('href');
                } else {
                    link.trigger('click', event);
                }
            }
            else {
                var checkall = $target.find('.k-js-grid-checkall');

                if (checkall.length) {
                    checkall.prop('checked', checkall.is(':checked') ? false : true).trigger('change');
                }
            }
        });
    },
    setTableRows: function() {
        // Trigger checkbox when the user clicks anywhere in the row
        this.form.on('click.koowa', 'tr', function(event) {
            var target = $(event.target);

            if(target.is('[type=radio], [type=checkbox], a[href], span.footable-toggle')) {
                return;
            }

            var tr = target.is('tr') ? target : target.parents('tr'),
                checkbox = tr.find('.k-js-grid-checkbox');

            if(tr.data('readonly') == true || !checkbox.length) {
                return;
            }

            if (checkbox.length) {
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            }
        });

        // Checkbox should add selected and selected-multiple classes to the row
        $('.k-js-grid-checkbox').on('change.koowa', function(event) {
            var selected,
                target = $(event.target),
                tr     = target.parents('tr'),
                parent = tr.parent();

            if (target.is('[type=radio]')) {
                parent.find('.k-is-selected').removeClass('k-is-selected');
            }

            $(this).prop('checked') ? tr.addClass('k-is-selected') : tr.removeClass('k-is-selected');

            selected = parent.find('.k-is-selected').length;

            if(selected > 1) {
                parent.addClass('k-has-selected-multiple').removeClass('k-has-selected-single')
            } else if (selected === 1) {
                parent.removeClass('k-has-selected-multiple').addClass('k-has-selected-single');
            } else {
                parent.removeClass('k-has-selected-multiple').removeClass('k-has-selected-single');
            }
        }).trigger('change', true);
    },
    checkAll: function(value){
        var changed = this.checkboxes.filter(function(i, checkbox){
            return $(checkbox).prop('checked') !== value;
        });

        this.checkboxes.prop('checked', value);
        changed.trigger('change', true);
    },
    uncheckAll: function() {
        this.checkAll(false);
    },
    setCheckAll: function(){
        var total = this.checkboxes.filter(function(i, checkbox){
            return $(checkbox).prop('checked') !== false;
        }).length;

        this.checkall.prop('checked', this.checkboxes.length === total);
        this.checkall.trigger('change', true);
    }
});

/**
 * Find all selected checkboxes' ids in the grid
 *
 * @param   {string|object|null} [context]   A DOM Element, Document, or jQuery to use as context
 * @return  array           The items' ids
 */
Koowa.Grid.getAllSelected = function(context) {
    return $('.k-js-grid-checkbox:checked', context);
};

/**
 * Get a query string for selected checkboxes
 *
 * @param   {string|object|null} [context]   A DOM Element, Document, or jQuery to use as context
 * @return  array           The items' ids
 */
Koowa.Grid.getIdQuery = function(context) {
    return decodeURIComponent(this.getAllSelected(context).serialize());
};

$(function() {
    $('.k-js-grid').each(function(i, el) {
        new Koowa.Grid($(el));
    })
});


})(window.kQuery);