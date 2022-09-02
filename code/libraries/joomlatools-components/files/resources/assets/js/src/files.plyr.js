/**
 * @package     Joomlatools Framework Files
 * @copyright   Copyright (C) 2020 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

var Files = Files || {};

(function($) {

/**
 * Makes elements into Plyr
 *
 * @example new Files.Plyr('.plyr', { controls: ["play", "download"]});
 * @extends Koowa.Class
 */
Files.Plyr = Koowa.Class.extend({
    options: {
        selector: 'video, audio',
        controls: [
            'play-large',   // The large play button in the center
            'play',         // Play/pause playback
            'progress',     // The progress bar and scrubber for playback and buffering
            'current-time', // The current time of playback
            'mute',         // Toggle mute
            'volume',       // Volume control
            'fullscreen'    // Toggle fullscreen
        ],
        download: false
    },

    initialize: function(options){
        this.setOptions(options);

        var remoteControls = Array.from(this.options.controls);

        if (this.options.download) {
            this.options.controls.push('download');
        }

        var selector = this.options.selector;

        delete this.options.selector;
        delete this.options.download;

        Plyr.setup(selector, this.options);

        // Do not use download button for remote videos
        Plyr.setup('[data-plyr-provider]', {...options, controls: remoteControls})
    }
});

var recorded_plays = [];

$(document).on('playing', function(event) {
    if (typeof event.detail !== 'undefined' && typeof event.detail.plyr !== 'undefined') {
        var plyr = event.detail.plyr;

        // If they've played over 3 seconds, then consider it played
        // This is the same timing convention used by Facebook, Instagram, and Twitter
        setTimeout(function() {
            if (!plyr.paused) {
                var media = $(plyr.media);

                var category = media.data('category');
                var action = 'Play ' + plyr.type;

                var title = media.data('title') || '';
                var id = parseInt(media.data('media-id'), 10) || 0;

                if (recorded_plays.indexOf(title) === -1) {
                    recorded_plays.push(title);

                    if (typeof window.GoogleAnalyticsObject !== 'undefined' && typeof window[window.GoogleAnalyticsObject] !== 'undefined') {
                        window[window.GoogleAnalyticsObject]('send', 'event', category, action, title, id);
                    }
                    else if (typeof _gaq !== 'undefined' && typeof _gat !== 'undefined') {
                        if (_gat._getTrackers().length) {
                            _gaq.push(function () {
                                var tracker = _gat._getTrackers()[0];
                                tracker._trackEvent(category, action, title, id);
                            });
                        }
                    }
                }
            }
        }, 3000);
    }
});

})(kQuery);