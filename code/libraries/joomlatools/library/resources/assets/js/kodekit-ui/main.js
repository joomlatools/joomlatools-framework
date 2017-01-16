(function($) {

    $(document).ready(function () {

        // Variables
        var $footable = $('.k-js-responsive-table'),
            $sidebarToggle = $('.k-js-sidebar-toggle-item'),
            $scopebar = $('.k-js-scopebar'),
            resizeTimer,
            resizeClass = 'k-is-resizing';

        // Sidebar
        if ($('.k-js-title-bar, .k-js-toolbar').length && $('.k-js-wrapper').length && $('.k-js-content').length)
        {
            var toggle_button = '<div class="k-off-canvas-menu-toggle-holder"><button class="k-off-canvas-menu-toggle" type="button">' +
                    '<span class="k-toggle-button-bar1"></span>' +
                    '<span class="k-toggle-button-bar2"></span>' +
                    '<span class="k-toggle-button-bar3"></span>' +
                    '</button></div>',
                sidebar_left  = $('.k-js-sidebar-left'),
                sidebar_right = $('.k-js-sidebar-right');

            function addOffCanvasButton(element, position) {
                // Variables
                var kContainer = '.k-ui-container',
                    container = element.closest(kContainer),
                    titlebar = container.find('.k-js-title-bar'),
                    toolbar = container.find('.k-js-toolbar'),
                    wrapper = container.find('.k-js-wrapper'),
                    content = container.find('.k-js-content'),
                    component = container.find('.k-js-component'),
                    toggle = container.find('.k-off-canvas-menu-toggle--' + position),
                    $toggle = $(toggle_button),
                    $toggleButton = null,
                    transitionElements = content;

                // Add proper class to toggle buttons
                $toggle.addClass('k-off-canvas-menu-toggle-holder--' + position).children('button').addClass('k-off-canvas-menu-toggle--' + position);

                // Add toggle buttons
                if (toggle.length === 0) {
                    if ( position == 'left' ) {
                        if ( titlebar.length) {
                            titlebar.prepend($toggle);
                        } else if (toolbar.length) {
                            toolbar.prepend($toggle);
                        }
                    } else if ( position == 'right') {
                        if ( toolbar.length) {
                            toolbar.append($toggle);
                        } else if (titlebar.length) {
                            titlebar.append($toggle);
                        }
                        transitionElements = component;
                    }

                    $toggleButton = $('.k-off-canvas-menu-toggle--' + position);

                    // Initialize the offcanvas plugin
                    element.offCanvasMenu({
                        menuToggle: $toggleButton,
                        wrapper: wrapper,
                        container: content,
                        position: position,
                        transitionElements: transitionElements
                    });
                }
            }

            if (sidebar_left.length) {
                // Add button for left sidebar
                $.each(sidebar_left, function() {
                    addOffCanvasButton($(this), 'left');
                });

                var sidebarLeftTree = $('.k-tree'),
                    sidebarLeftList = $('.k-list');

                if ( ( sidebarLeftTree.length || sidebarLeftList.length ) ) {
                    sidebarLeftTree.on('click', '.jqtree-title', function() {
                        if ( $('.k-js-wrapper').hasClass('k-is-opened-left') ) {
                            $('.k-off-canvas-menu-toggle--left').trigger('click');
                        }
                    });
                    sidebarLeftList.on('click', 'a', function() {
                        if ( $('.k-js-wrapper').hasClass('k-is-opened-left') ) {
                            $('.k-off-canvas-menu-toggle--left').trigger('click');
                        }
                    });
                }
            }

            if (sidebar_right.length) {
                // Add button for right sidebar
                $.each(sidebar_right, function() {
                    addOffCanvasButton($(this), 'right');
                });


                // Open right sidebar on selecting items in table
                // Only apply to actual `<a>` elements
                $('.k-table-container table').on('click', 'a', function(event) {
                    // stopPropagation for all links except for those with `.navigate` class
                    if ( !$(this).hasClass('navigate') ) {
                        event.stopPropagation();
                    }
                    // Only apply if parent is a `<td>` (so not a `<th>`)
                    if ($(this).parents('td').length > 0) {
                        $('.k-off-canvas-menu-toggle--right').trigger('click');
                    }
                });
            }
        }

        // Footable
        $footable.footable({
            toggleSelector: '.footable-toggle',
            breakpoints: {
                phone: 400,
                tablet: 600,
                desktop: 800
            }
        });

        // Add class to body when resizing so we can add styling to the page
        $(window).on('resize', function() {
            $('body').addClass(resizeClass);

            // Remove the class when resize is done
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                $('body').removeClass(resizeClass);
            }, 200);
        });

        // Filter and search toggle buttons in the scopebar
        if ( $scopebar.length ) {

            $.each($scopebar, function() {

                var $this = $(this),
                    $scopebarFilters = $this.find('.k-scopebar__item--filters'),
                    $scopebarSearch = $this.find('.k-scopebar__item--search'),
                    scopebarToggleClass = '.k-scopebar__item--toggle-buttons',
                    scopebarToggleButtonContainer = '<div class="k-scopebar__item k-scopebar__item--toggle-buttons"></div>';

                if ( !$this.find(scopebarToggleClass).length ) {
                    $this.prepend(scopebarToggleButtonContainer);
                }
                var toggleButtons = $this.find(scopebarToggleClass);

                if ( $scopebarFilters.length && !$this.find('.k-toggle-scopebar-filters').length ) {
                    toggleButtons.prepend('<button type="button" class="k-scopebar__button k-toggle-scopebar-filters k-js-toggle-filters">' +
                        '<span class="k-icon-filter" aria-hidden="true">' +
                        '<span class="k-visually-hidden">Filters toggle</span>' +
                        '<div class="k-js-filter-count k-scopebar__item-label k-scopebar__item-label--numberless"></div>' +
                        '</button>');
                }

                if ( $scopebarSearch.length && !$this.find('.k-toggle-scopebar-search').length ) {

                    toggleButtons.prepend('<button type="button" class="k-scopebar__button k-toggle-scopebar-search k-js-toggle-search">' +
                        '<span class="k-icon-magnifying-glass" aria-hidden="true">' +
                        '<span class="k-visually-hidden">Search toggle</span>' +
                        '<div class="k-js-search-count k-scopebar__item-label k-scopebar__item-label--numberless" style="display: none"></div>' +
                        '</button>');

                    if (toggleButtons.siblings('.k-scopebar__item--search').find('.k-search__field').val()) {
                        $('.k-js-search-count').show();
                    }
                }
            });

            // Toggle search
            $('.k-js-toggle-filters').on('click', function() {
                $(this).parent().siblings('.k-scopebar__item--filters').slideToggle('fast');
            });

            $('.k-js-toggle-search').on('click', function() {
                $(this).parent().siblings('.k-scopebar__item--search').slideToggle('fast');
            });
        }

        // Select2
        $('.k-js-select2').select2({
            theme: "bootstrap"
        });

        // Datepicker
        $('.k-js-datepicker').datepicker();

        // Magnific
        $('.k-js-image-modal').magnificPopup({type:'image'});
        $('.k-js-inline-modal').magnificPopup({type:'inline'});
        $('.k-js-iframe-modal').magnificPopup({type:'iframe'});

        // Tooltips
        $('.k-js-tooltip').ktooltip({
            animation: true,
            placement: 'top',
            delay: { show: 200, hide: 50 },
            container: '.k-ui-container'
        });

        // Sidebar block toggle (e.g. quick filters)
        if ( $sidebarToggle.length ) {
            var toggle = $('<div class="k-sidebar-item__toggle"><span class="k-visually-hidden">Toggle</span></div>');

            $sidebarToggle.addClass('k-sidebar-item--toggle').find('.k-sidebar-item__header').append(toggle);

            $sidebarToggle.on('click', '.k-sidebar-item__toggle', function(event) {
                $(this).toggleClass('k-is-active').parent().next().slideToggle(180);
            });
        }

        // Konami
        new Konami(function() {
            $('html, .k-ui-container').css({
                'font-family': 'Comic Sans MS',
                'font-size': '20px',
                'line-height': '30px'
            }).addClass('konami');
        });

        // Styleguide tree
        new Koowa.Tree('#k-jqtree', {
            "data": [
                {"label":"Main category","id":4},
                {"label":"Sub category 1","id":5,"parent":4},
                {"label":"Sub category 2","id":6,"parent":4},
                {"label":"Deeper category","id":7,"parent":6},
                {"label":"Sub category 3","id":8,"parent":4}
            ]
        });
    });

})(kQuery);
