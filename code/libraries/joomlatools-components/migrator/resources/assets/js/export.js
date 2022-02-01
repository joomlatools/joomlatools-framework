
(function($) {

if (typeof this.JoomlatoolsMigrator === 'undefined') {
    this.JoomlatoolsMigrator = {};
}

var import_in_process = false;

window.onbeforeunload = function(e) {
    if (import_in_process) {
        return 'Navigating away from this page may result in a broken site. Are you sure you want to continue?';
    }
};

$(function() {
    var showStep = function(step) {
            $('.migrator__wrapper').hide();
            $('.migrator--step'+step).show();

            $('.migrator__steps__list__item').removeClass('item--active');
            $('.migrator__steps__list__item:nth-child('+step+')').addClass('item--active');
        },
        showError = function(response)
        {
            var message = "There's been an error while executing the export job";

            if (response.error) {
                message = response.error;
            }
            else if (response.errors)  {
                message = response.errors[0].message;
            }

            $('.migrator_alert').fadeOut('fast', function() {
                $(this).html(message).fadeIn('fast');
            });
            $('.bar').removeClass('bar-success').addClass('bar-danger')
                .parent().removeClass('active');
        },
        download = function()
        {
            $('#progress-bar-package').css('width', '50%');

            var callback = function ()
            {
                $('#progress-bar-package').css('width', '100%').addClass('bar-success')
                    .parent().removeClass('active');

                setTimeout(function ()
                {
                    showStep(5);
                    setTimeout(function () {
                        window.location = JoomlatoolsMigrator.export_url;
                    }, 3000);
                }, 1000);
            };

            var extension = $('#extension').val(),
                url = JoomlatoolsMigrator.base_url+'&extension='+extension,
                config = {
                    data: {
                        _action: 'package'
                    }
                };

            ajax(url, config).done(callback);
        },
        ajax = function(url, config)
        {
            if (!config.method) {
                config.method = 'POST';
            }

            if (!config.data) {
                config.data = {};
            }

            if (!config.timeout) {
                config.timeout = 30000;
            }

            config.data.csrf_token = JoomlatoolsMigrator.token;

            var error = function(data) {
                var response;

                try {
                    response = $.parseJSON(data.responseText);
                } catch (error) {
                    response = {};
                }

                showError(response);
            };

            return $.ajax(url, config).fail(error);
        },
        jobs = JoomlatoolsMigrator.exporters,
        processQueue = function(queue)
        {
            if (queue.length)
            {
                var job = queue.shift();

                var config = jobs[job];

                var container = $('<div class="migrator__content" style="display: none;"></div>').appendTo($('.migrator--step3'))
                    .append('<h3>' + config.label + '</h3>');

                var progress_bar = $('<div class="bar" style="width: 0%"></div>');

                container.show();

                // Automatically scroll down.
                $('html,body').animate({scrollTop: container.offset().top});

                $('<div class="progress progress-striped active"></div>').appendTo(container)
                    .append(progress_bar);

                var extension = $('#extension').val(),
                    url = JoomlatoolsMigrator.base_url+'&extension='+extension;

                url += '&job='+job;

                if (config.chunkable)
                {
                    var chunker = new JoomlatoolsMigrator.Chunker({
                        url: url,
                        request: {
                            type: 'post',
                            data: {
                                _action: 'run',
                                csrf_token: JoomlatoolsMigrator.token
                            }
                        }
                    }).bind('processUpdate', function(e, data) {
                            progress_bar.css('width', data.percentage + '%');
                        }).bind('processFailed', function(e, data) {
                            showError(data);
                        }).bind('processComplete', function(e, data) {
                            progress_bar.css('width', '100%').addClass('bar-success')
                                .parent().removeClass('active');

                            processQueue(queue);
                        }).start();
                }
                else
                {
                    progress_bar.css('width', '50%');

                    ajax(url, {
                        data: {
                            _action: 'run'
                        }
                    }).done(function() {
                        progress_bar.css('width', '100%').addClass('bar-success')
                            .parent().removeClass('active');

                        processQueue(queue);
                    });
                }
            }
            else
            {
                // If queue is empty, proceed to download.
                setTimeout(function () {
                    showStep(4);
                    download();
                }, 1000);
            }
        };

    $('#export-btn').click(function()
    {
        $(this).attr('disabled', 'disabled');

        showStep(2);

        $('#progress-bar-cleanup').css('width', '50%');

        var callback = function()
        {
            var extension = $('#extension').val();

            jobs = jobs[extension];

            var queue = [];

            $.map(jobs, function(value, index) {
                queue.push(index);
            });

            $('#progress-bar-cleanup').css('width', '100%').addClass('bar-success')
                .parent().removeClass('active');

            setTimeout(function()
            {
                showStep(3);
                processQueue(queue);
            }, 1000);
        };

        ajax(JoomlatoolsMigrator.base_url, {
            data: {
                '_action': 'cleanup'
            }
        }).done(callback);
    });
});

})(kQuery);