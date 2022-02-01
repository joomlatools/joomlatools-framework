
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
    var uploader = new plupload.Uploader({
            runtimes : 'html5,html4',
            browse_button : 'pickfiles',
            dragdrop: true,
            container : 'migrator-container',
            max_file_size : JoomlatoolsMigrator.max_file_size+'b',
            url: JoomlatoolsMigrator.base_url,
            urlstream_upload: true, // required for flash
            multi_selection: false,
            multipart_params: {
                _action: 'upload',
                csrf_token: JoomlatoolsMigrator.token
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            filters : [
                {title : "Zip files", extensions : "zip"}
            ]
        }),
        showStep = function(step) {
            $('.migrator__wrapper').hide();
            $('.migrator--step'+step).show();

            $('.migrator__steps__list__item').removeClass('item--active');
            $('.migrator__steps__list__item:nth-child('+step+')').addClass('item--active');
        },
        showError = function(response)
        {
            var message = response;

            if (typeof response === 'object')
            {
                if (response.errors && response.errors.length) {
                    message = response.errors[0].message;
                }
                else if (response.error) {
                    message = response.error;
                }
                else {
                    message = response.message;
                }
            }

            import_in_process = false;

            $('.migrator_alert').fadeOut('fast', function() {
                $(this).html(message).fadeIn('fast');
            });
            $('.bar').removeClass('bar-success').addClass('bar-danger')
                .parent().removeClass('active');
        },
        updateProgress = function(progress_bar, percent) {
            progress_bar.css('width', percent + '%');

            if (percent == '100') {
                progress_bar.addClass('bar-success')
                    .parent().removeClass('active');
            }

            if (percent == '0') {
                progress_bar.removeClass('bar-success')
                    .parent().addClass('active');
            }
        },
        uploader_progress = $('#progress-bar-upload'),
        task_progress = null,
        jobs = null,
        extension = null,
        fileUploaded = function(uploader, file, response) {
            var json;

            try {
                json = $.parseJSON(response.response) || {};
            } catch (error) {
                showError('Invalid response');
                return;
            }

            if (json.error) {
                var error = json.error.length ? json.error[0].message : 'Unknown error';
                showError(error);
                return;
            }

            updateProgress(uploader_progress, '100');

            jobs = json.jobs || {};
            extension = json.extension;

            var queue = [];

            $.map(jobs, function(value, index) {
                queue.push(index);
            });

            processQueue(queue);
        },
        processQueue = function(queue) {


            if (queue.length)
            {
                var job    = queue.shift();
                var config = jobs[job];

                var el = $('<div id="task" class="migrator__content" style="display: none"></div>');
                el.append('<h3>' + config.label + '</h3>');
                el.append('<div class="progress progress-striped active">' +
                    '<div class="bar" style="width: 0" id="progress-bar-task-' + job + '"></div></div>');

                el.appendTo('.migrator--step2');

                el.show();

                $('html,body').animate({scrollTop: el.offset().top});

                task_progress = $('#progress-bar-task-' + job );

                var url = JoomlatoolsMigrator.base_url+'&extension='+extension+'&job='+job;

                if (config.chunkable)
                {
                    var chunker = new JoomlatoolsMigrator.Chunker({
                        url: url,
                        init_offset: config.init_offset ? config.init_offset : 0,
                        request: {
                            type: 'post',
                            data: {
                                _action: 'run',
                                csrf_token: JoomlatoolsMigrator.token
                            }
                        }
                    }).bind('processUpdate', function(e, data) {
                            updateProgress(task_progress, data.percentage);
                        }).bind('processFailed', function(e, data) {
                            showError(data);
                        }).bind('processComplete', function(e, data) {
                            updateProgress(task_progress, 100);
                            processQueue(queue);
                        }).start();
                }
                else
                {
                    updateProgress(task_progress, 10);

                    $.ajax({
                        url: url,
                        type: 'post',
                        error: function(data, textStatus) {
                            var response;

                            try {
                                response = $.parseJSON(data.responseText);
                            } catch (error) {
                                response = {};
                            }

                            showError(response);
                        },
                        success: function(data, textStatus)  {
                            updateProgress(task_progress, 100);
                            processQueue(queue);
                        },
                        data: {
                            _action: 'run',
                            csrf_token: JoomlatoolsMigrator.token
                        }
                    });
                }
            }
            else
            {
                showStep(3);

                import_in_process = false;
            }
        };

    uploader.init();

    uploader.bind('FilesAdded', function(uploader, files) {
        import_in_process = true;

        $('#pickfiles').css('display', 'none');

        showStep(2);

        uploader.start();
    });
    uploader.bind('UploadProgress', function(uploader, file) {
        updateProgress(uploader_progress, file.percent);
    });
    uploader.bind('Error', function(uploader, error) {
        var response;

        try {
            response = $.parseJSON(error.response);
        } catch (error) {
            response = {};
        }

        showError(response);
    });
    uploader.bind('FileUploaded', fileUploaded);
});

})(kQuery);