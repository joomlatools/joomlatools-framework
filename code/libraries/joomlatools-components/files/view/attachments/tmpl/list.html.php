<?
/**
 * Nooku Platform - http://www.nooku.org/platform
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/nooku/nooku-platform for the canonical source repository
 */
?>

<!-- Only existing entities are attachable -->
<? if (!$entity->isNew()): ?>
    <ktml:script src="media://koowa/com_files/js/attachments.js"/>
    <ktml:style src="media://koowa/com_files/css/files.css"/>

    <?= helper('behavior.modal') ?>

    <script>
        kQuery(function($) {
            Attachments = Attachments.getInstance(
                {
                    url: "<?= route('', true, false) ?>",
                    template: '#attachment-template',
                    selector: '#attachment-template',
                    csrf_token: <?= json_encode(object('user')->getSession()->getToken()) ?>
                }
            );

            Attachments.bind('before.attach', function(event, context)  {setContext(context)});
            Attachments.bind('before.detach', function(event, context) {setContext(context)});

            var setContext = function(context) {
                context.data.attachment = context.attachment;
            };

            initPopup = function()
            {
                $('.attachments__image > a').magnificPopup({
                    type: 'image',
                    gallery: {
                        enabled: true
                    }
                });
            }

            renderAttachment = function (attachment) {

                var url = "<?= route('view=file&routed=1&name={name}', true, false) ?>";

                attachment.url = Attachments.replace(url, {name: attachment.name});

                var output = Attachments.render(attachment);

                output = $(output).appendTo($('.attachments'));
            };

            var attachments = <?= json_encode(array_values($entity->getAttachments()->toArray())) ?>;

            $.each(attachments, function (idx, attachment) {
                renderAttachment(attachment);
            });

            initPopup();
        });
    </script>

    <div class="attachments"></div>

    <!-- Attachment template begin -->
    <textarea style="display: none" id="attachment-template">
        [% if (file.type == 'image') { %]
            <div id="[%=Attachments.escape(name)%]" class="attachments__image [% if (thumbnail) { %]attachments__image--thumbnail[% } %]">
                <a href="[%=url%]">
                    [% if (thumbnail) { %]
                        <img src="[%=thumbnail%]"/>
                    [% } else { %]
                        <span class="k-icon-document-image k-icon--size-xlarge"></span>
                        <div class="attachments__caption">
                            [%=name%]
                        </div>
                    [% } %]
                </a>
            </div>
        [% } else { %]
         <div id="[%=Attachments.escape(name)%]" class="attachments__file">
             <a href="[%=url%]">
                 <span class="k-icon-document-default k-icon--size-xlarge"></span>
                 <div class="attachments__caption">
                     [%=name%]
                 </div>
             </a>
         </div>
        [% } %]
    </textarea>
    <!-- Attachment template end -->

    <? if (isset($manage) && $manage == true): ?>

        <script>
            kQuery(function($) {
                attachmentsCallback = function(app)
                {
                    app.grid.addEvent('afterAttachAttachment', function(data)
                    {
                        var attachment = data.attachment;

                        if (attachment.entity)  {
                            renderAttachment(attachment.entity);
                        } else {
                            var url = "<?= route('view=attachments&name={name}&format=json', true, false) ?>";

                            $.ajax({
                                url: Attachments.replace(url, {name: attachment.name}),
                                success: function (data) {
                                    renderAttachment(data.entities.pop());
                                }
                            });
                        }

                        initPopup();
                    });

                    app.grid.addEvent('afterDetachAttachment', function(data)
                    {
                        $('div[id="' + Attachments.escape(data.node.name) + '"]').remove();
                        initPopup();
                    });
                }
            });
        </script>

        <div class="attachments__select">
            <?= helper('com:files.attachments.manage', array('entity' => $entity)) ?>
        </div>

    <? endif ?>

<? endif ?>
