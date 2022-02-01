/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.Grid = new Class({
	Implements: [Events, Options],
	layout: 'icons',
	options: {
		onClickFolder: function (){},
		onClickFile: function (){},
		onClickImage: function (){},
		onDeleteNode: function (){},
		onSwitchLayout: function (){},
		switchers: '.k-js-layout-switcher',
		layout: false,
		spinner_container: '.k-loader-container',
		batch_delete: false,
		icon_size: 150,
		types: null // null for all or array to filter for folder, file and image
	},

	initialize: function(container, options) {
		this.setOptions(options);

		this.spinner_container = kQuery(this.options.spinner_container);

		this.nodes = new Hash();
		this.container = document.id(container);

		if (this.options.switchers) {
			this.options.switchers = document.getElements(this.options.switchers);
		}

		if (this.options.batch_delete) {
			this.options.batch_delete = document.getElement(this.options.batch_delete);
		}

		if (this.options.layout) {
			this.setLayout(this.options.layout);
		}
		this.render();
		this.attachEvents();
	},
	attachEvents: function() {

		var that = this,
			createEvent = function(selector, event_name) {
				that.container.addEvent(selector, function(e) {
					e.stop();
					that.fireEvent(event_name, arguments);
				});
			};
		createEvent('click:relay(.files-folder a.navigate)', 'clickFolder');
		createEvent('click:relay(.files-file a.navigate)', 'clickFile');
		createEvent('click:relay(.files-image a.navigate)', 'clickImage');

		/*
		 * Checkbox events
		 */
		var fireCheck = function(e) {
		    if(e.target.match('a.navigate')) {
		        return;
		    }
			if (e.target.get('tag') == 'input') {
				e.target.setProperty('checked', !e.target.getProperty('checked'));
			}
			var box = e.target.getParent('.files-node-shadow');
			if (!box) {
				box = e.target.match('.files-node') ? e.target :  e.target.getParent('.files-node');
			}

			that.checkNode(box.retrieve('row'));
		};

		this.container.addEvent('click:relay(div.js-select-node)', fireCheck.bind(this));
    	this.container.addEvent('click:relay(input.files-select)', fireCheck.bind(this));

        // Check the box when user clicks on the row
        this.container.addEvent('click', function(event) {
            if (that.layout !== 'details') {
                return;
            }

            var target = event.target;

            if (target.get('tag') === 'a' || target.get('tag') === 'input') {
                return;
            }

            if (target.get('tag') === 'i' && target.hasClass('icon-download')) {
                return;
            }

            if (target.get('tag') === 'span' && target.getParent().get('tag') === 'a') {
            	return;
			}

            var node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

            if(node) {
                row = node.retrieve('row');

                if (row) {
                    that.checkNode(row);
                }
            }
        });

		/*
		 * Delete events
		 */
		var deleteEvt = function(e) {
			if (e.stop) {
				e.stop();
			}

			var box = e.target.getParent('.files-node-shadow');
			if (!box) {
				box = e.target.match('.files-node') ? e.target :  e.target.getParent('.files-node');
			}

			this.erase(box.retrieve('row').path);
		}.bind(this);

		this.container.addEvent('click:relay(.delete-node)', deleteEvt);

		that.addEvent('afterDeleteNodeFail', function(context) {
			var xhr = context.xhr,
				response = JSON.decode(xhr.responseText, true);

			if (response && response.error) {
				alert(response.error);
			}
		});

		if (this.options.batch_delete) {
			var chain = new Chain(),
				chain_call = function() {
					chain.callChain();
				};

			this.addEvent('afterCheckNode', function() {
				var checked = this.container.getElements('input[type=checkbox]:checked');
				this.options.batch_delete.setProperty('disabled', !checked.length);
			}.bind(this));

			this.options.batch_delete.addEvent('click', function(e) {
				e.stop();

				var file_count = 0,
					files = [],
					folder_count = 0,
					folders = [],
					checkboxes = this.container.getElements('input[type=checkbox]:checked.files-select')
					.filter(function(el) {
						if (el.checked) {
							var box = el.getParent('.files-node-shadow') || el.getParent('.files-node'),
								name = box.retrieve('row').name;

							if (el.getParent('.files-node').hasClass('files-folder')) {
								folder_count++;
								folders.push(name)
							} else {
								file_count++;
								files.push(name);
							}
							return true;
						}
					});

				var message = '';
				// special case for single deletes
				if (file_count+folder_count === 1) {
					message = Koowa.translate('You are deleting {item}. Are you sure?', {
                        item: folder_count ? folders[0] : files[0]
                    });
				} else {
					var count = file_count+folder_count,
					    items = Koowa.translate('{count} files and folders');

                    message = Koowa.translate('You are deleting {items}. Are you sure?');

					if (!folder_count && file_count) {
						items = Koowa.translate('{count} files');
					} else if (folder_count && !file_count) {
						items = Koowa.translate('{count} folders');
					}

					items   = items.replace('{count}', count);
					message = message.replace('{items}', items);
				}

				if (!checkboxes.length || !confirm(message)) {
					return false;
				}

				that.addEvent('afterDeleteNode', chain_call);
				that.addEvent('afterDeleteNodeFail', chain_call);

				checkboxes.each(function(el) {
					if (!el.checked) {
						return;
					}
					chain.chain(function() {
						deleteEvt({target: el});
					});
				});
				chain.chain(function() {
					that.removeEvent('afterDeleteNode', chain_call);
					that.removeEvent('afterDeleteNodeFail', chain_call);
					chain.clearChain();
				});
				chain.callChain();
			}.bind(this));
		}

		if (this.options.switchers) {
            this.options.switchers.addEvent('click', function(e) {
                e.stop();
                var layout = this.get('data-layout');
                that.setLayout(layout);
                that._updateSwitchers(layout);
            });
		}

		if (this.options.icon_size) {
			var size = this.options.icon_size;
			this.addEvent('beforeRenderObject', function(context) {
				context.object.icon_size = size;
			});
		}

		this.container.addEvent('click:relay(.k-js-files-sortable)', function(event) {
			var header = event.target.match('th') ? event.target : event.target.getParent('th'),
				state  = {
					sort: header.get('data-name'),
					direction: 'asc'
				};

			if (header.hasClass('k-js-files-sorted')) {
				state.direction = 'desc';
			}

			that.setState(state);

			that.fireEvent('setState', state);
		});

		var input = kQuery('.k-search__field', '#files-canvas'),
			empty_button = kQuery(".k-search__empty"),
			send = function(value) {
				var state = {search: typeof value === 'undefined' ? input.val() : value};

				that.setState(state);
				that.fireEvent('setState', state);

				if (!state.search || state.search === '') {
					empty_button.removeClass("k-is-visible");
				}
			};

		input.blur(function() {
			send();
		})
		.on('keypress', function(event) {
			if (event.which === 13) { // enter key
				send();
				input.blur();
			}
		})
		.on('input', function(event) {
			var v = kQuery(this).val();

			if (v) {
				empty_button.addClass("k-is-visible");
			} else {
				empty_button.removeClass("k-is-visible");
			}

		});

		if (input.val()) {
			empty_button.addClass("k-is-visible");
		}

		kQuery('.k-search__empty', '#files-canvas').click(function() {
			event.preventDefault();

			if (input.val()) {
				input.val('');
				send('');
			}
		});
	},
	setState: function(state) {
		if (typeof state.search !== 'undefined') {
			var search = document.id('files-canvas').getElement('.search_button');
			if (search) {
				search.set('value', state.search);
			}
		}

		var headers = this.container.getElements('.k-js-files-sortable'),
			header  = headers.filter('[data-name="'+state.sort+'"]')[0];

		if (!header) {
			return;
		}

		headers.removeClass('k-js-files-sorted').removeClass('k-js-files-sorted-desc');

		kQuery('.k-js-sort-icon').remove();

		header.addClass('k-js-files-sorted'+(state.direction === 'asc' ? '' : '-desc'));

		var icon = kQuery('<span class="k-js-sort-icon k-icon-sort-'+(state.direction === 'asc' ? 'ascending' : 'descending')+'" />');

		kQuery('th[data-name="'+state.sort+'"]').find('a').append(icon);
	},
	/**
	 * fire_events is used when switching layouts so that client events to
	 * catch the user interactions don't get messed up
	 */
	checkNode: function(row, fire_events) {
		var box = row.element,
		    node = row.element.match('.files-node') ? row.element : row.element.getElement('.files-node'),
			checkbox = box.getElement('input[type=checkbox]')
			;
		if (fire_events !== false) {
			this.fireEvent('beforeCheckNode', {row: row, checkbox: checkbox});
		}

		var old = checkbox.getProperty('checked');

		var card = node.getElement('.k-card');

		if (old) {
			node.removeClass('k-is-selected');

			if (card) card.removeClass('k-is-selected');
		} else {
			node.addClass('k-is-selected');

			if (card) card.addClass('k-is-selected');
		}

		row.checked = !old;
		checkbox.setProperty('checked', !old);

		if (fire_events !== false) {
			this.fireEvent('afterCheckNode', {row: row, checkbox: checkbox});
		}

	},
	erase: function(node) {
		if (typeof node === 'string') {
			node = this.nodes.get(node);
		}
		if (node) {
			this.fireEvent('beforeDeleteNode', {node: node});
			var success = function() {
				if (node.element) {
					node.element.dispose();
				}

				this.nodes.erase(node.path);

				this.fireEvent('afterDeleteNode', {node: node});
			}.bind(this),
				failure = function(xhr) {
					this.fireEvent('afterDeleteNodeFail', {node: node, xhr: xhr});
				}.bind(this);
			node['delete'](success, failure);
		}
	},
	render: function() {
		this.fireEvent('beforeRender');

		this.container.empty();
		this.root = new Files.Grid.Root(this.layout);
		this.container.adopt(this.root.element);

		this.renew();

		this.setFootable();

		this.fireEvent('afterRender');
	},
	setFootable: function() {
        var $footable = kQuery('.k-js-responsive-table');

        if ($footable.length && this.layout === 'details')
        {
			if (!$footable.hasClass('footable'))
			{
				$footable.footable({
					toggleSelector: '.footable-toggle',
					breakpoints: {
						phone: 400,
						tablet: 600,
						desktop: 800
					}
				});
			}
			else $footable.trigger('footable_redraw');
        }
	},
	renderObject: function(object, position) {
		position = position || 'alphabetical';

		this.fireEvent('beforeRenderObject', {object: object, position: position});

		object.element = object.render(this.layout);
		object.element.store('row', object);

		if (position == 'last') {
			this.root.adopt(object.element, 'bottom');
		}
		else if (position == 'first') {
			this.root.adopt(object.element);
		}
		else {
			var index = this.nodes.filter(function(node){
				return node.type == object.type;
			}).getKeys();

			if (index.length === 0) {
				if (object.type === 'folder') {
					var keys = this.nodes.getKeys();
					if (keys.length) {
						// there are files so append it before the first file
						var target = this.nodes.get(keys[0]);
						object.element.inject(target.element, 'before');
					}
					else {
						this.root.adopt(object.element, 'bottom');
					}
				}
				else {
					this.root.adopt(object.element, 'bottom');
				}

			}
			else {
				index.push(object.path);
				index = index.sort();

				var obj_index = index.indexOf(object.path);
				var length = index.length;
				if (obj_index === 0) {
					var target = this.nodes.get(index[1]);
					object.element.inject(target.element, 'before');
				}
				else {
					var target = obj_index+1 === length ? index[length-2] : index[obj_index-1];
					target = this.nodes.get(target);
					object.element.inject(target.element, 'after');
				}
			}
		}

		this.fireEvent('afterRenderObject', {object: object, position: position});

		return object.element;
	},
	getCount: function() {
		return this.nodes.getLength();
	},
	reset: function() {
		this.fireEvent('beforeReset');

		this.nodes.each(function(node) {
			if (node.element) {
				node.element.dispose();
			}
			this.nodes.erase(node.path);
		}.bind(this));

		this.fireEvent('afterReset');
	},
	insert: function(object, position) {
		this.fireEvent('beforeInsertNode', {object: object, position: position});

		if (!this.options.types || this.options.types.contains(object.type)) {
			this.renderObject(object, position);

			this.nodes.set(object.path, object);

			this.fireEvent('afterInsertNode', {node: object, position: position});
		}
	},
	/**
	 * Insert multiple rows, possibly coming from a JSON request
	 */
	insertRows: function(rows) {
		var data = {rows: rows};

		this.fireEvent('beforeInsertRows', data);

        Object.each(data.rows, function(row) {
			var cls = Files[row.type.capitalize()];
			var item = new cls(row);
			this.insert(item, 'last');
		}.bind(this));

		if (this.options.icon_size) {
			this.setIconSize(this.options.icon_size);
		}

		this.fireEvent('afterInsertRows', data);
	},
	renew: function() {
		this.fireEvent('beforeRenew');

		var folders = this.getFolders(),
			files = this.getFiles(),
			that = this,
			renew = function(node) {
				var node = that.nodes.get(node);

				if (node.element) {
					node.element.dispose();
				}
				that.renderObject(node, 'last');

				if (node.checked) {
					that.checkNode(node, false);
				}
			};
		folders.each(renew);
		files.each(renew);

		this.fireEvent('afterRenew');
	},
	setLayout: function(layout) {
		if (layout) {
			this.fireEvent('beforeSetLayout', {layout: layout});

			this.layout = layout;
			if (this.options.switchers) {
                this._updateSwitchers(layout);
			}

			this.fireEvent('afterSetLayout', {layout: layout});

			this.render();
		}

	},
	getFolders: function() {
		return this.nodes.filter(function(node) {
			return node.type === 'folder';
		}).getKeys().sort();
	},
	getFiles: function() {
		return this.nodes.filter(function(node) {
			return node.type === 'file' || node.type == 'image';
		}).getKeys().sort();
	},
	setIconSize: function(size) {
		this.fireEvent('beforeSetIconSize', {size: size});

		this.options.icon_size = size;

		if (this.nodes.getKeys().length && this.layout == 'icons') {
			this.container.getElements('.imgTotal').setStyles({
	            width: size + 'px',
	            height: (size * 0.75) + 'px'
	        });
	        this.container.getElements('.imgOutline .ellipsis').setStyle('width', size + 'px');
		}

    	this.fireEvent('afterSetIconSize', {size: size});
	},
    spin: function(){
		this.spinner_container.removeClass('k-is-hidden');
    },
    unspin: function(){
		this.spinner_container.addClass('k-is-hidden');
		kodekitUI.gallery();
		kodekitUI.sidebarToggle();
    },
    /**
     * Updates the active state on the switchers
     * @param layout   string, current layout
     * @private
     */
    _updateSwitchers: function(layout){
        this.options.switchers.removeClass('k-is-active').filter(function(el) {
            return el.get('data-layout') == layout;
        }).addClass('k-is-active');
    }
});

Files.Grid.Root = new Class({
	Implements: Files.Template,
	template: 'container',
	initialize: function(layout) {
		this.element = this.render(layout);
	},
	adopt: function(element, position) {
		position = position || 'top';
		var parent = this.element;
		if (this.element.get('tag') == 'table') {
			parent = this.element.getElement('tbody');
		}

		if (element.get('tag') === 'tr') {
			var tbody = parent.getElement('tbody');
			if (tbody) {
				parent = tbody;
			}
		}

        //Legacy
        if (!element.injectInside) element.injectInside = element.inject;
		element.injectInside(parent, position);
	}
});
