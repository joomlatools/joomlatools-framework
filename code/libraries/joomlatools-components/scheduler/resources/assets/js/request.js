/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

(function() {
    var url = document.querySelector('script[data-scheduler]').getAttribute('data-scheduler');

    if (url) {
        request(decodeURIComponent(url));
    }

    function ajax(url, callback, data, x) {
        try {
            x = new(this.XMLHttpRequest || ActiveXObject)('MSXML2.XMLHTTP.3.0');
            x.open('POST'/*data ? 'POST' : 'GET'*/, url, 1);
            x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            x.onreadystatechange = function () {
                x.readyState > 3 && callback && callback(x.responseText, x);
            };
            x.send(data);
        } catch (e) {}
    }

    function request(url) {
        ajax(url, function (responseText, xhr) {
            try {
                if (xhr.status == 200) {
                    var response = JSON.parse(responseText);
                    if (typeof response === 'object' && response.continue) {
                        setTimeout(function() { request(url) }, 1000);
                    }
                }
            } catch (e) {}
        });
    }
})();