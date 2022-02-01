/**
 * koowa.uploader.js
 *
 * Copyright 2013, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 *
 * Depends:
 *	jquery.ui.widget.js
 *
 */

 /* global jQuery:true */

/**
jQuery UI based implementation of the Plupload API - multi-runtime file uploading API.

To use the widget you must include _jQuery_ and _jQuery UI_ `ui.widget`.
 
@example
	<!-- Instantiating: -->
	<div id="uploader">
		<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
	</div>

	<script>
		$('#uploader').uploader({
			url : '../upload.php',
			filters : [
				{title : "Image files", extensions : "jpg,gif,png"}
			]
		});
	</script>

@example
	// Invoking methods:
	$('#uploader').uploader(options);

	// Display welcome message in the notification area
	$('#uploader').uploader('notify', 'info', "This might be obvious, but you need to click 'Add Files' to add some files.");

@example
	// Subscribing to the events...
	// ... on initialization:
	$('#uploader').uploader({
		...
		viewchanged: function(event, args) {
			// stuff ...
		}
	});
	// ... or after initialization
	$('#uploader').on("viewchanged", function(event, args) {
		// stuff ...
	});

@class UI.Plupload
@constructor
@param {Object} settings For detailed information about each option check documentation.
	@param {String} settings.url URL of the server-side upload handler.
	@param {Number|String} [settings.chunk_size=0] Chunk size in bytes to slice the file into. Shorcuts with b, kb, mb, gb, tb suffixes also supported. `e.g. 204800 or "204800b" or "200kb"`. By default - disabled.
	@param {String} [settings.file_data_name="file"] Name for the file field in Multipart formated message.
	@param {Object} [settings.filters={}] Set of file type filters.
 		@param {Boolean} [settings.filters.image_only=false] Only allow image extensions
		@param {Array} [settings.filters.mime_types=[]] List of file types to accept, each one defined by title and list of extensions. `e.g. {title : "Image files", extensions : "jpg,jpeg,gif,png"}`. Dispatches `plupload.FILE_EXTENSION_ERROR`
		@param {String|Number} [settings.filters.max_file_size=0] Maximum file size that the user can pick, in bytes. Optionally supports b, kb, mb, gb, tb suffixes. `e.g. "10mb" or "1gb"`. By default - not set. Dispatches `plupload.FILE_SIZE_ERROR`.
		@param {Boolean} [settings.filters.prevent_duplicates=false] Do not let duplicates into the queue. Dispatches `plupload.FILE_DUPLICATE_ERROR`.
		@param {Number} [settings.filters.max_file_count=0] Limit the number of files that can reside in the queue at the same time (default is 0 - no limit).
	@param {String} [settings.flash_swf_url] URL of the Flash swf.
	@param {Object} [settings.headers] Custom headers to send with the upload. Hash of name/value pairs.
	@param {Number|String} [settings.max_file_size] Maximum file size that the user can pick, in bytes. Optionally supports b, kb, mb, gb, tb suffixes. `e.g. "10mb" or "1gb"`. By default - not set. Dispatches `plupload.FILE_SIZE_ERROR`.
	@param {Number} [settings.max_retries=0] How many times to retry the chunk or file, before triggering Error event.
	@param {Boolean} [settings.multipart=true] Whether to send file and additional parameters as Multipart formated message.
	@param {Object} [settings.multipart_params] Hash of key/value pairs to send with every file upload.
	@param {Boolean} [settings.multi_selection=true] Enable ability to select multiple files at once in file dialog.
	@param {Boolean} [settings.prevent_duplicates=false] Do not let duplicates into the queue. Dispatches `plupload.FILE_DUPLICATE_ERROR`.
	@param {String|Object} [settings.required_features] Either comma-separated list or hash of required features that chosen runtime should absolutely possess.
	@param {Object} [settings.resize] Enable resizng of images on client-side. Applies to `image/jpeg` and `image/png` only. `e.g. {width : 200, height : 200, quality : 90, crop: true}`
		@param {Number} [settings.resize.width] If image is bigger, it will be resized.
		@param {Number} [settings.resize.height] If image is bigger, it will be resized.
		@param {Number} [settings.resize.quality=90] Compression quality for jpegs (1-100).
		@param {Boolean} [settings.resize.crop=false] Whether to crop images to exact dimensions. By default they will be resized proportionally.
	@param {String} [settings.runtimes="html5,flash,silverlight,html4"] Comma separated list of runtimes, that Plupload will try in turn, moving to the next if previous fails.
	@param {String} [settings.silverlight_xap_url] URL of the Silverlight xap.
	@param {Boolean} [settings.unique_names=false] If true will generate unique filenames for uploaded files.

	@param {Boolean} [settings.autostart=false] Whether to auto start uploading right after file selection.
	@param {Boolean} [settings.dragdrop=true] Enable ability to add file to the queue by drag'n'dropping them from the desktop.
	@param {Boolean} [settings.rename=false] Enable ability to rename files in the queue.
	@param {Object} [settings.buttons] Control the visibility of functional buttons.
		@param {Boolean} [settings.buttons.browse=true] Display browse button.
		@param {Boolean} [settings.buttons.start=true] Display start button.
		@param {Boolean} [settings.buttons.stop=true] Display stop button.
	@param {Boolean} [settings.multiple_queues=true] Re-activate the widget after each upload procedure.
*/
;(function(window, document, plupload, o, $) {

/**
Dispatched when the widget is initialized and ready.

@event ready
@param {plupload.Uploader} uploader Uploader instance sending the event.
*/

/**
Dispatched when file dialog is closed.

@event selected
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {Array} files Array of selected files represented by plupload.File objects
*/

/**
Dispatched when file dialog is closed.

@event removed
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {Array} files Array of removed files represented by plupload.File objects
*/

/**
Dispatched when upload is started.

@event started
@param {plupload.Uploader} uploader Uploader instance sending the event.
*/

/**
Dispatched when upload is stopped.

@event stopped
@param {plupload.Uploader} uploader Uploader instance sending the event.
*/

/**
Dispatched during the upload process.

@event progress
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {plupload.File} file File that is being uploaded (includes loaded and percent properties among others).
	@param {Number} size Total file size in bytes.
	@param {Number} loaded Number of bytes uploaded of the files total size.
	@param {Number} percent Number of percentage uploaded of the file.
*/

/**
Dispatched when file is uploaded.

@event uploaded
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {plupload.File} file File that was uploaded.
	@param {Enum} status Status constant matching the plupload states QUEUED, UPLOADING, FAILED, DONE.
*/

/**
Dispatched when upload of the whole queue is complete.

@event complete
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {Array} files Array of uploaded files represented by plupload.File objects
*/

/**
Dispatched when the view is changed, e.g. from `list` to `thumbs` or vice versa.

@event viewchanged
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {String} type Current view type.
*/

/**
Dispatched when error of some kind is detected.

@event error
@param {plupload.Uploader} uploader Uploader instance sending the event.
@param {String} error Error message.
@param {plupload.File} file File that was uploaded.
	@param {Enum} status Status constant matching the plupload states QUEUED, UPLOADING, FAILED, DONE.
*/

var uploaders = {};

function _(str) {
	return Koowa.translate(str) || str;
}

plupload.translate = function(str) { return _(str)};

$.widget("koowa.uploader", {

	widgetEventPrefix: 'uploader:',

	contents_bak: '',

	template_cache: {},

	options: {
		filters: {
			prevent_duplicates: true,
			image_only: false
		},

		// widget specific
		buttons: {
			browse: true,
			start: true,
			stop: true
		},

		thumb_width: 100,
		thumb_height: 60,

		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		},

		runtimes: 'html5,html4',
		chunking: false,

		multi_selection: true,
		multiple_queues: true, // re-use widget by default
		dragdrop : true,
		drop_element: 'body',
		autostart: false,
		rename: true,

		preinit: {},

		templates: {}
	},

	IMAGE_EXTENSIONS: ['jpg', 'jpeg', 'gif', 'png', 'tiff', 'tif', 'xbm', 'bmp'],

	FILE_COUNT_ERROR: -9001,

	_create: function() {
		var self = this,
			id = this.element.attr('id');
		if (!id) {
			id = plupload.guid();
			this.element.attr('id', id);
		}
		this.id = id;

		// backup the elements initial state
		this.contents_bak = this.element.html();
		this.element.id = this.element.attr('id');
		this.element.addClass('k-upload');

		$('.js-uploader-template').each(function(i, el) {
			var $el = $(el),
				name = $el.data('name');

			if (typeof self.options.templates[name] === 'undefined') {
				self.options.templates[name] = $el;
			}
		});

		var html = this.renderTemplate('content-box');
		html += this.renderTemplate('error-box');
		html += this.renderTemplate('info-box');
		html += this.renderTemplate('progress-bar');

		this.element.html(html);

		var selection = this.options.multi_selection ? 'multiple' : 'single';

		$('.js-content', this.element).html(this.renderTemplate('empty-'+selection));

		// list of files
		this.filelist = $(selection == 'single' ? '.js-content' : '.js-filelist-multiple', this.element)
			.attr({
				id: id + '_filelist',
				unselectable: 'on'
			});

		// buttons
		this.browse_button = $('.js-choose-files', this.element).attr('id', id + '_browse').addClass('disabled');
		this.stop_button = $('.js-stop-upload', this.element).attr('id', id + '_stop');
		this.start_button = $('.js-start-upload', this.element).attr('id', id + '_start')
			.hide();

		this._on({
			'click .js-clear-queue': function(event) {
				event.preventDefault();

				self.clearQueue();
			},
			'click .js-open-info': function(event) {
				event.preventDefault();

				self.element.toggleClass('has-open-info');
			},
			'click .js-remove-file': function(event) {
				event.preventDefault();

				$(this).closest('tr').children('td').slideUp('fast');
			},
			'click .js-close-error': function(event) {
				event.preventDefault();

				self.element.removeClass('has-error');
			}
		});

		this.browse_button.data('caption-original', this.browse_button.text());

		// progressbar
		this.progressbar = $('.bar', this.element);

		if (this.options.container) {
			var container = this.options.container;

			if (typeof container.parameters === 'object') {
				var parameters = container.parameters;

				if (parameters.maximum_size) {
					this.options.maximum_size = parameters.maximum_size;
				}

				if (typeof parameters.allowed_extensions === 'object') {
					this.options.filters.extensions = parameters.allowed_extensions;
				}

				if (parameters.maximum_image_size) {
					this.options.resize = {
						'width': parameters.maximum_image_size,
						'height': parameters.maximum_image_size
					};
				}
			}

			this.options.multipart_params.container = container.slug;
		}

		if (this.options.filters.extensions) {
			var extensions = this.options.filters.extensions;

			if (this.options.filters.image_only) {
				extensions = extensions.filter(function(extension) {
					return self.IMAGE_EXTENSIONS.indexOf(extension) != -1;
				});
			}

			this.options.filters.mime_types = [{
				title: Koowa.translate('All Files'),
				extensions: typeof extensions === 'string' ? extensions : extensions.join(',')
			}];
		}

		if (this.options.chunking) {
			this.options.preinit.Init = this._setChunking;
		}

		if (!this.options.server_limit) {
			this.options.server_limit = $.koowa.uploader.server_limit;
		}

		// initialize uploader instance
		this._initUploader();

		this._setMaxCount(this.options.multi_selection ? 100 : 1);
	},

	_setChunking: function(uploader) {
		var limit = uploader.settings.maximum_size,
			server_limit = uploader.settings.server_limit;

		// Allow chunking
		if (uploader.runtime === 'html5' && uploader.features.chunks) {

			// Leave 1 mb for the rest of the POST data
			var chunk_size = Math.max(1048576, server_limit - 1048576);

			if (chunk_size > 33554432) {
				chunk_size = 33554432; // use 32 mb chunks at the maximum
			}

			uploader.setOption('chunk_size', chunk_size);
		}
		else {
			if (!limit || limit == 0 || (limit > server_limit)) {
				limit = server_limit - 1048576;
			}
		}

		if (limit > 0) {
			uploader.setOption('max_file_size', limit);
		}
	},

	_setMaxCount: function(count) {
		var queue_lock = false;

		this.uploader.bind('QueueChanged', function(uploader) {
			if (queue_lock) {
				return;
			}

			queue_lock = true;
			uploader.splice(0, uploader.files.length-count);
			queue_lock = false;
		});
	},

	_initUploader: function() {
		var self = this
		, id = this.id
		, uploader
		, options = {
			container: id,
			browse_button: id + '_browse'
		}
		;

		if (self.options.dragdrop) {
			if (self.options.drop_element === 'element') {
				self.options.drop_element = self.id;
			} else if (self.options.drop_element === 'body') {
				self.options.drop_element = document.body;
			}

			options.drop_element = self.options.drop_element || document.body;
		}

		this.filelist.on('click', function(e) {
			if ($(e.target).hasClass('js-remove-file')) {
				self.removeFile($(e.target).closest('.js-uploader-file').attr('id'));
				e.preventDefault();

				if (!self.uploader.files.length) {
					self.element.removeClass('has-open-info');
				}
			}
		});

		uploader = this.uploader = uploaders[id] = new plupload.Uploader($.extend(this.options, options));

		// retrieve full normalized set of options
		this.options = uploader.getOption();

		// for backward compatibility
		if (self.options.max_file_count) {
			plupload.extend(uploader.getOption('filters'), {
				max_file_count: self.options.max_file_count
			});
		}

		plupload.addFileFilter('max_file_count', function(maxCount, file, cb) {
			if (maxCount <= this.files.length - (this.total.uploaded + this.total.failed)) {
				self.disable();

				this.trigger('Error', {
					code : self.FILE_COUNT_ERROR,
					message : _("File count error."),
					file : file
				});
				cb(false);
			} else {
				cb(true);
			}
		});


		uploader.bind('Error', function(up, err) {
			var message, details = "";

			message = '<strong>' + err.message + '</strong>';

			switch (err.code) {
				case plupload.FILE_EXTENSION_ERROR:
					details = o.sprintf(_("File: %s"), err.file.name);
					break;

				case plupload.FILE_SIZE_ERROR:
					details = o.sprintf(_("File: %s, size: %d, max file size: %d"), err.file.name,  plupload.formatSize(err.file.size), plupload.formatSize(plupload.parseSize(up.getOption('filters').max_file_size)));
					break;

				case plupload.FILE_DUPLICATE_ERROR:
					details = o.sprintf(_("%s already present in the queue."), err.file.name);
					break;

				case self.FILE_COUNT_ERROR:
					details = o.sprintf(_("Upload element accepts only %d file(s) at a time. Extra files were stripped."), up.getOption('filters').max_file_count || 0);
					break;

				case plupload.IMAGE_FORMAT_ERROR :
					details = _("Image format either wrong or not supported.");
					break;

				case plupload.IMAGE_MEMORY_ERROR :
					details = _("Runtime ran out of available memory.");
					break;

				case plupload.HTTP_ERROR:
					details = _("Upload URL might be wrong or doesn't exist.");
					break;
			}

			message += " <br /><i>" + details + "</i>";

			self._trigger('error', null, { uploader: up, error: err } );

			// do not show UI if no runtime can be initialized
			if (err.code === plupload.INIT_ERROR) {
				setTimeout(function() {
					self.destroy();
				}, 1);
			} else {
				self.notify('error', message);
			}
		});


		uploader.bind('PostInit', function(up) {
			self.element.addClass('is-initialized');

			// all buttons are optional, so they can be disabled and hidden
			if (!self.options.buttons.browse) {
				self._setButtonStatus(self.browse_button, 'disable');
				self.browse_button.hide();
				up.disableBrowse(true);
			} else {
				self._setButtonStatus(self.browse_button, 'enable');
			}

			if (!self.options.buttons.start) {
				self._setButtonStatus(self.start_button, 'disable');
				self.start_button.hide();
			}

			if (!self.options.buttons.stop) {
				self._setButtonStatus(self.stop_button, 'disable');
				self.stop_button.hide();
			}

			if (!self.options.unique_names && self.options.rename) {
				self._enableRenaming();
			}

			if (self.options.dragdrop && up.features.dragdrop && o.Env.os !== 'iOS')
			{
				self.element.addClass('has-dragdrop-support');

				var drop_element = $(self.options.drop_element);

                var getDropHandlers = function (element, modifier) {
                    return {
                        enter: function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            element.addClass(modifier);
                        },
                        leave: function (event) {
                            event.stopPropagation();

                            var e = event.originalEvent,
								target = $(e.target);

							// Firefox
                            if (target.is('.k-uploader-drop-visual > span') || target.is('.k-uploader-drop-visual')
							|| target.is(document.body) || e.target === window.document) {
                                element.removeClass(modifier);
                            }

							// Chrome
							if (e.offsetX < 0 || e.offsetY < 0) {
								if (!e.relatedTarget) {
                                    element.removeClass(modifier);
								}
							}

							// The rest
							if ((e.offsetX === 0 && e.offsetY === 0) || event.type === 'drop') {
                                element.removeClass(modifier);
							}
                        }
                    }
                };

				if (drop_element.is('body'))
				{
					var selection = self.options.multi_selection ? 'multiple' : 'single',
						element   = $($.trim(self.renderTemplate('drop-message-'+selection)));

					drop_element.append(element);

					var handlers = getDropHandlers(element, 'is-active');

                    drop_element.on('drop', handlers.leave);
                    drop_element.on('dragleave', handlers.leave);

                    drop_element.on('dragenter', handlers.enter);
				}
				else
				{
					var handlers = getDropHandlers(drop_element, 'has-drag-hover');

					drop_element.on('drop', handlers.leave);
					drop_element.on('dragleave', handlers.leave);

					drop_element.on('dragenter', handlers.enter);
				}
			}

			self.start_button.click(function(e) {
				e.preventDefault();

				if (!self.start_button.hasClass('disabled')) {
					self.start();
				}
			});

			self.stop_button.click(function(e) {
				self.stop();
				e.preventDefault();
			});

			self._trigger('ready', null, { uploader: up });
		});

		// uploader internal events must run first
		uploader.init();

		uploader.bind('FileFiltered', function(up, file) {
			self._addFiles(file);
		});


		uploader.bind('FilesAdded', function(up, files) {
			self._trigger('selected', null, { uploader: up, files: files } );

			self._trigger('updatelist', null, { filelist: self.filelist });

			if (self.options.autostart) {
				// set a little delay to make sure that QueueChanged triggered by the core has time to complete
				setTimeout(function() {
					self.start();
				}, 10);
			}
		});

		uploader.bind('FilesRemoved', function(up, files) {

			$.each(files, function(i, file) {
				$('#' + file.id).toggle("highlight", function() {
					$(this).remove();
				});
			});

			self._trigger('updatelist', null, { filelist: self.filelist });
			self._trigger('removed', null, { uploader: up, files: files } );
		});

		uploader.bind('BeforeUpload', function (uploader, file) {
			self._trigger('beforeupload', null, { uploader: uploader, file: file });
		});

		uploader.bind('QueueChanged', function() {
			self._handleState();
		});

		uploader.bind('StateChanged', function(up) {
			self._handleState();
			if (plupload.STARTED === up.state) {
				self._trigger('started', null, { uploader: self.uploader });
			} else if (plupload.STOPPED === up.state) {
				self._trigger('stopped', null, { uploader: self.uploader });
			}
		});

		var _handleUploadErrors = function(uploader, file, result) {
			var response = result.response;

			if (response.status === false)
			{
				var error = response.error ? response.error : Koowa.translate('Unknown error');
				self.notify('error', error);

				self.progressbar.removeClass('bar-success').addClass('bar-danger');

				file.error_message = error;
				file.status = plupload.FAILED;

				uploader.total.uploaded -= 1;
				uploader.total.failed += 1;

				uploader.stop();
			}
		};

		uploader.bind('ChunkUploaded', _handleUploadErrors);

		uploader.bind('UploadFile', function(up, file) {
			self._handleFileStatus(file);
		});

		uploader.bind('FileUploaded', function(up, file, result) {
			// strip off <pre>..</pre> tags that might be enclosing the response (happens in HTML4 runtime)
			if (typeof result.response !== 'undefined') {
				try {
					result.response = $.parseJSON(result.response.replace(/^\s*<pre[^>]*>/, '').replace(/<\/pre>\s*$/, ''));
					_handleUploadErrors(up, file, result);
				}
				catch (e) {}
			}

			self._handleFileStatus(file);
			self._trigger('uploaded', null, { uploader: up, file: file, result: result } );
		});

		uploader.bind('UploadProgress', function(up, file) {
			self._handleFileStatus(file);
			self._updateTotalProgress();
			self._trigger('progress', null, { uploader: up, file: file } );
		});

		uploader.bind('UploadComplete', function(up, files) {
			if (!self.progressbar.hasClass('bar-danger')) {
				self.progressbar.addClass('bar-success');
			}

			self._trigger('complete', null, { uploader: up, files: files } );
		});

		$(window).resize(function() {
			var pos1 = $('.moxie-shim').position(),
				pos2 = self.browse_button.position();

			if (pos1 && pos2 && (Math.abs(pos1.left - pos2.left) > 2 || Math.abs(pos1.top - pos2.top) > 2)) {
				uploader.refresh();
			}
		});
	},


	_setOption: function(key, value) {
		var self = this;

		if (key == 'buttons' && typeof(value) == 'object') {
			value = $.extend(self.options.buttons, value);

			if (!value.browse) {
				self.browse_button.hide();
				self._setButtonStatus(self.browse_button, 'disable');
				self.uploader.disableBrowse(true);
			} else {
				self._setButtonStatus(self.browse_button, 'enable');
				self.browse_button.show();
				self.uploader.disableBrowse(false);
			}

			if (!value.start) {
				self._setButtonStatus(self.start_button, 'disable');
				self.start_button.hide();
			} else {
				self._setButtonStatus(self.start_button, 'enable');
				if (!self.options.autostart) {
					self.start_button.show();
				}
			}

			if (!value.stop) {
				self._setButtonStatus(self.stop_button, 'disable');
				self.stop_button.hide();
			} else {
				self._setButtonStatus(self.start_button, 'enable');
				if (!self.options.autostart) {
					self.start_button.show();
				}
			}
		}

		self.uploader.setOption(key, value);
	},


	/**
	Start upload. Triggers `start` event.

	@method start
	*/
	start: function() {
		if (this._trigger('beforestart', null, {uploader: this.uploader, button: this.start_button}) !== false) {
			this.uploader.start();

			this._trigger('afterstart', null, {uploader: this.uploader, button: this.stop_button});
		}
	},


	/**
	Stop upload. Triggers `stop` event.

	@method stop
	*/
	stop: function() {
		if (this._trigger('beforestop', null, {uploader: this.uploader, button: this.stop_button}) !== false) {
			this.uploader.stop();

			this._trigger('afterstop', null, {uploader: this.uploader, button: this.stop_button});
		}
	},


	/**
	Enable browse button.

	@method enable
	*/
	enable: function() {
		this._setButtonStatus(this.browse_button, 'enable');
		this.uploader.disableBrowse(false);
	},


	/**
	Disable browse button.

	@method disable
	*/
	disable: function() {
		this._setButtonStatus(this.browse_button, 'disable');
		this.uploader.disableBrowse(true);
	},


	/**
	Retrieve file by its unique id.

	@method getFile
	@param {String} id Unique id of the file
	@return {plupload.File}
	*/
	getFile: function(id) {
		var file;

		if (typeof id === 'number') {
			file = this.uploader.files[id];
		} else {
			file = this.uploader.getFile(id);
		}
		return file;
	},

	/**
	Return array of files currently in the queue.

	@method getFiles
	@return {Array} Array of files in the queue represented by plupload.File objects
	*/
	getFiles: function() {
		return this.uploader.files;
	},


	/**
	Remove the file from the queue.

	@method removeFile
	@param {plupload.File|String} file File to remove, might be specified directly or by its unique id
	*/
	removeFile: function(file) {
		if (plupload.typeOf(file) === 'string') {
			file = this.getFile(file);
		}
		this.uploader.removeFile(file);
	},


	/**
	Clear the file queue.

	@method clearQueue
	*/
	clearQueue: function() {
		this.uploader.splice();

		this.element.removeClass('has-open-info');
	},


	/**
	Retrieve internal plupload.Uploader object (usually not required).

	@method getUploader
	@return {plupload.Uploader}
	*/
	getUploader: function() {
		return this.uploader;
	},


	/**
	Trigger refresh procedure, specifically browse_button re-measure and re-position operations.
	Might get handy, when UI Widget is placed within the popup, that is constantly hidden and shown
	again - without calling this method after each show operation, dialog trigger might get displaced
	and disfunctional.

	@method refresh
	*/
	refresh: function() {
		this.uploader.refresh();
	},


	/**
	Display a message in notification area.

	@method notify
	@param {Enum} type Type of the message, either `error` or `info`
	@param {String} message The text message to display.
	*/
	notify: function(type, message) {
		this.element.addClass('has-error');
		this.element.find('.js-message-body').html(message);
	},


	/**
	Destroy the widget, the uploader, free associated resources and bring back original html.

	@method destroy
	*/
	destroy: function() {
		// destroy uploader instance
		this.uploader.destroy();

		// restore the elements initial state
		this.element
			.empty()
			.removeClass('k-upload')
			.html(this.contents_bak);
		this.contents_bak = '';

		$.Widget.prototype.destroy.apply(this);
	},


	_handleState: function() {
		var self = this
		, up = this.uploader
		, filesPending = up.files.length - (up.total.uploaded + up.total.failed)
		, maxCount = up.getOption('filters').max_file_count || 0
		;

		if (plupload.STARTED === up.state) {
			this.progressbar.removeClass('bar-danger bar-success').parent().addClass('active is-uploading');

			this._setButtonStatus(this.start_button, 'disable');
			this._setButtonStatus(this.stop_button, 'enable');

			if (!this.options.multiple_queues) {
				this.disable();
			}
		}
		else if (plupload.STOPPED === up.state) {
			self.progressbar.parent().removeClass('active is-uploading');

			this._setButtonStatus(this.stop_button, 'disable');

			if (filesPending) {
				this._setButtonStatus(this.start_button, 'enable');
			} else {
				this._setButtonStatus(this.start_button, 'disable');
			}

			// if max_file_count defined, only that many files can be queued at once
			if (this.options.multiple_queues && maxCount && maxCount > filesPending) {
				this.enable();
			}

			if (!up.files.length) {
				this.browse_button.text(this.browse_button.data('caption-original'));

				this.element.removeClass('has-file');
			} else if (this.options.multi_selection === false) {
				this.browse_button.text(this.browse_button.data('caption-update'));

				this.element.addClass('has-file');
			} else {
				if (!this.options.autostart) {
					this.start_button.show();
				}

				this.element.addClass('has-file');
			}

			this._updateTotalProgress();
		}

		up.refresh();
	},

	_setButtonStatus: function(button, status) {
		if (status === 'enable') {
			button.removeClass('disabled');
		} else {
			button.addClass('disabled');
		}

		// re-position Moxie shim
		this.getUploader().refresh();
	},

	_handleFileStatus: function(file) {
		var $file = $('#' + file.id), klass, text;

		// since this method might be called asynchronously, file row might not yet be rendered
		if (!$file.length) {
			return;
		}

		switch (file.status) {
			case plupload.DONE:
				text = _('done');
				klass = 'is-done';
				break;

			case plupload.FAILED:
				text = _('failed');
				klass = 'is-failed';
				break;

			case plupload.QUEUED:
				text = _('delete');
				klass = 'is-delete';
				break;

			case plupload.UPLOADING:
				text = _('uploading');
				klass = 'is-uploading';

				// @todo robin do we need this?
				// scroll uploading file into the view if its bottom boundary is out of it
				var scroller = $('.plupload_scroll', this.element)
					, scrollTop = scroller.scrollTop()
					, scrollerHeight = scroller.height()
					, rowOffset = $file.position().top + $file.height()
					;

				if (scrollerHeight < rowOffset) {
					scroller.scrollTop(scrollTop + rowOffset - scrollerHeight);
				}

				break;
		}

		$file.find('.js-file-status')
			.removeClass('is-uploading is-in-queue')
			.addClass(klass)
			.text(text);


		if (file.error_message) {
			$file.find('.is-failed').css('cursor', 'pointer').ktooltip({
				title: file.error_message,
				placement: 'right'
			});
		}
	},


	_updateTotalProgress: function() {
		var up = this.uploader, html = '', template;

		// Scroll to end of file list
		//this.filelist[0].scrollTop = this.filelist[0].scrollHeight;

		this.progressbar.css('width', up.total.percent + '%');

		if (up.total.percent == 100) {
			this.progressbar.parent().removeClass('active');
		}

		if (this.options.multi_selection) {
			if (plupload.STARTED === up.state) {
				template = 'uploading';
			}
			else if (up.total.percent == 100) {
				template = 'upload-finished';
			}
			else if (up.files.length) {
				template = 'upload-pending';
			} else {
				template = this.options.multi_selection ? 'empty-multiple' : 'empty-single';
			}

			if (template) {
				html = this.renderTemplate(template, {
					'percent' : up.total.percent,
					'size'    : plupload.formatSize(up.total.size),
					'uploaded': up.total.uploaded,
					'total'   : up.files.length,
					'remaining': (up.files.length - (up.total.uploaded + up.total.failed)),
					'failed'  : up.total.failed,
					'uploader': up
				});
			}

			$('.js-content', this.element).html(html);
		}
	},

	renderTemplate: function(template, data) {
		if (typeof this.template_cache[template] === 'undefined') {
			var source = this.options.templates[template];

			if (source instanceof $) {
				source = source.text();

			}
			else if (typeof source === 'function') {
				source = source();
			}

			this.template_cache[template] = doT.template(source);
		}

		return this.template_cache[template](data);
	},


	_addFiles: function(files) {
		var self = this, html = '';

		if (plupload.typeOf(files) !== 'array') {
			files = [files];
		}

		$.each(files, function(i, file) {
			var template = self.options.multi_selection ? 'multiple-files' : 'single-file';

			html += self.renderTemplate(template, {
				'ext'  : o.Mime.getFileExtension(file.name) || '',
				'size' : file.size ? plupload.formatSize(file.size) : '',
				'name' : file.name,
				'id'   : file.id,
				'file' : file,
				'uploader': self.getUploader()
			});
		});

		if (!self.options.multi_selection) {
			self.filelist.empty();
		}

		self.filelist.append(html);
	},

	_enableRenaming: function() {
		var self = this;

		this.filelist.dblclick(function(e) {
			var nameSpan = $(e.target), nameInput, file, parts, name, ext = "";

			if (!nameSpan.hasClass('plupload_file_name_wrapper')) {
				return;
			}

			// Get file name and split out name and extension
			file = self.uploader.getFile(nameSpan.closest('.plupload_file')[0].id);
			name = file.name;
			parts = /^(.+)(\.[^.]+)$/.exec(name);
			if (parts) {
				name = parts[1];
				ext = parts[2];
			}

			// Display input element
			nameInput = $('<input class="plupload_file_rename" type="text" />').width(nameSpan.width()).insertAfter(nameSpan.hide());
			nameInput.val(name).blur(function() {
				nameSpan.show().parent().scrollLeft(0).end().next().remove();
			}).keydown(function(e) {
				var nameInput = $(this);

				if ($.inArray(e.keyCode, [13, 27]) !== -1) {
					e.preventDefault();

					// Rename file and glue extension back on
					if (e.keyCode === 13) {
						file.name = nameInput.val() + ext;
						nameSpan.html(file.name);
					}
					nameInput.blur();
				}
			})[0].focus();
		});
	}
});

$.koowa.uploader.server_limit = 0;

} (window, document, plupload, mOxie, kQuery));
