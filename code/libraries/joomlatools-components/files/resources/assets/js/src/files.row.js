/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.Row = new Class({
	Implements: [Options, Events, Files.Template],
	initialize: function(object, options) {
		this.setOptions(options);

        Object.each(object, function(value, key) {
			this[key] = value;
		}.bind(this));

        if (typeof this.name !== 'string') {
            this.name = '';
        }

		if (!this.path) {
			this.path = (object.folder ? object.folder+'/' : '') + object.name;
		}
		this.identifier = this.path;

		this.filepath = (object.folder ? this.encodePath(object.folder)+'/' : '') + this.encode(object.name);
	},
	encodePath: function(path, encoder) {
		var parts = path.split('/');

		if (!encoder) {
			encoder = this.encode;
		}

		parts = parts.map(function(part) {
			return encoder(part);
		});

		return parts.join('/');
	},
	encode: function(string) {
		return string;
	},
	realpath: function(string) {
		return string;
	}
});

Files.File = new Class({
	Extends: Files.Row,

	type: 'file',
	template: 'file',
	initialize: function(object, options) {
		this.parent(object, options);

		if (Files.app) {
			this.baseurl = Files.app.baseurl;
		}
		
		this.size = new Files.Filesize(this.metadata.size);
		this.filetype = Files.getFileType(this.metadata.extension);

		this.client_cache = false;
	},
	getModifiedDate: function(formatted) {
        if (this.metadata.modified_date) {
            var date = new Date();
            date.setTime(this.metadata.modified_date*1000);
            if (formatted) {
				return date.toLocaleString('default', { year: 'numeric', month: 'short', day: 'numeric' });
            } else {
                return date;
            }
        }

        return null;
	},
	'delete': function(success, failure) {
		this.fireEvent('beforeDeleteRow');

		var that = this,
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'file', folder: that.folder, name: that.name}),
				method: 'post',
				data: {
					'_method': 'delete',
					'csrf_token': Files.token
				},
				onSuccess: function(response) {
					if (typeof success == 'function') {
						success(response);
					}
					that.fireEvent('afterDeleteRow', {status: true, response: response, request: this});
				},
				onFailure: function(xhr) {
					if (xhr.status == 204 || xhr.status == 1223) {
						// Mootools thinks it failed, weird
						return this.onSuccess();
					}

					response = xhr.responseText;
					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}

					that.fireEvent('afterDeleteRow', {status: false, response: response, request: this, xhr: xhr});
				}
			});
		request.send();
	}
});

Files.Image = new Class({
	Extends: Files.File,

	type: 'image',
	template: 'image',
	initialize: function(object, options) {
		this.parent(object, options);

		this.image = this.baseurl+'/'+this.encodePath(this.filepath, this.realpath);

		this.client_cache = false;
	},
	getThumbnail: function(success, failure) {
		var that = this,
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'file', name: that.name, folder: that.folder, thumbnails: Files.app.options.thumbnails}),
				method: 'get',
				onSuccess: function(response, responseText) {
					if (typeof success == 'function') {
						success(response);
					}
				},
				onFailure: function(xhr) {
					response = xhr.responseText;

					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}
				}
			});
		request.send();
	}
});


Files.Folder = new Class({
	Extends: Files.Row,

	type: 'folder',
	template: 'folder',

	'add': function(success, failure, complete) {
		this.fireEvent('beforeAddRow');

		var that = this;
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'folder', name: that.name, folder: Files.app.getPath()}),
				method: 'post',
				data: {
					'_action': 'add',
					'csrf_token': Files.token
				},
				onSuccess: function(response) {
					if (typeof success == 'function') {
						success(response);
					}

					that.fireEvent('afterAddRow', {status: true, response: response, request: this});
				},
				onFailure: function(xhr) {
					response = xhr.responseText;

					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}

					that.fireEvent('afterAddRow', {status: false, response: response, request: this, xhr: xhr});
				},
                onComplete: function(response){
                    if (typeof complete == 'function') {
                        complete(response);
                    }
                }
			});
		request.send();
	},
	'delete': function(success, failure) {
		var that = this,
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'folder', folder: Files.app.getPath(), name: that.name}),
				method: 'post',
				data: {
					'_method': 'delete',
					'csrf_token': Files.token
				},
				onSuccess: function(response) {
					if (typeof success == 'function') {
						success(response);
					}

					that.fireEvent('afterDeleteRow', {status: true, response: response, request: this});
				},
				onFailure: function(xhr) {
					if (xhr.status == 204 || xhr.status == 1223) {
						// Mootools thinks it failed, weird
						return this.onSuccess();
					}
					response = xhr.responseText;

					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}

					that.fireEvent('afterDeleteRow', {status: false, response: response, request: this, xhr: xhr});
				}
			});
		request.send();
	}
});