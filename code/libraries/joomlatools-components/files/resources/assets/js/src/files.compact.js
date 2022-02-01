/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files) Files = {};
Files.Compact = {};

Files.Compact.App = new Class({
	Extends: Files.App,
	Implements: [Events, Options],
    cookie: null,
	options: {
        persistent: false,
		types: ['file', 'image'],
		editor: null,
		preview: 'files-preview',
        state: {
            defaults: {
                limit: 0,
                offset: 0
            }
        },
		grid: {
			layout: 'compact',
			batch_delete: false
		},
		history: {
			enabled: false
		},
        uploader_dialog: false,
        folder_dialog: false,
        copy_dialog: false,
        move_dialog: false
	},

	initialize: function(options) {
		this.parent(options);

		this.editor = this.options.editor;
		this.preview = document.id(this.options.preview);

	},
	setPaginator: function() {
	},
	setGrid: function() {
		var opts = this.options.grid;
		var that = this;
        Object.append(opts, {
			'onClickImage': function(e) {
				var target = document.id(e.target),
				    node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

				node.getParent().getChildren().removeClass('active');
				node.addClass('active');
				var row = node.retrieve('row');
				var copy = Object.append({}, row);
				copy.template = 'details_image';

				that.preview.empty();

				copy.render('compact').inject(that.preview);

				that.preview.getElement('img').set('src', copy.image).setStyle('display', 'block');
			},
			'onClickFile': function(e) {
				var target = document.id(e.target),
			   		node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

				node.getParent().getChildren().removeClass('active');
				node.addClass('active');
				var row = node.retrieve('row');
				var copy = Object.append({}, row);
				copy.template = 'details_file';

				that.preview.empty();

				copy.render('compact').inject(that.preview);
			},
			onAfterRender: function() {
				this.setState(that.state.data);
			},
			onSetState: function(state) {
				this.state.set(state);

				this.navigate();
			}.bind(this)
		});
		this.grid = new Files.Grid(this.options.grid.element, opts);
	}
});
