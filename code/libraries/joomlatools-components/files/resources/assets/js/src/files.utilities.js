/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.Filesize = function(size) {
    this.size = size;
};

Files.Filesize.prototype.units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

Files.Filesize.prototype.humanize = function() {
    var i = 0,
        size = this.size;

    while (size >= 1024) {
        size /= 1024;
        i++;
    }

    return (i === 0 || size % 1 === 0 ? size : size.toFixed(2)) + ' ' + Koowa.translate(this.units[i]);
};

Files.urlEncoder = function(value)
{
    value = encodeURI(value);

    var replacements = {'\\?': '%3F', '#': '%23'}

    for(var key in replacements)
    {   var regexp = new RegExp(key, 'g');
        value = value.replace(regexp, replacements[key]);
    }

    return value;
};

Files.escape = function(string)
{
	let entityMap = {
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		'"': '&quot;',
		"'": '&#39;',
		"/": '&#x2F;'
	};

	return String(string).replace(/[&<>"'\/]/g, function (s) {return entityMap[s]});
};

Files.FileTypes = {};
Files.FileTypes.map = {
	'audio': ['aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'],
	'video': ['3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv'],
	'image': ['bmp','gif','jpg','jpeg','png','psd','tif','tiff'],
	'document': ['doc','docx','rtf','txt','xls','xlsx','pdf','ppt','pptx','pps','xml'],
	'archive': ['7z','gz','rar','tar','zip']
};

Files.getFileType = function(extension) {
	var type = 'document',
        map = Files.FileTypes.map;

	extension = extension.toLowerCase();

    for (var key in map) {
        if (map.hasOwnProperty(key)) {
            var extensions = map[key];
            if (extensions.indexOf(extension) != -1) {
                type = key;
                break;
            }
        }
    }

	return type;
};
