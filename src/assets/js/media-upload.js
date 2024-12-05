jQuery(document).ready(function ($) {
    // Gérer le clic sur le bouton "Télécharger"
    $('.rp-upload-button').on('click', function (e) {
        e.preventDefault();

        var targetInput = $($(this).data('target'));
        var previewImage = $('#preview-' + targetInput.attr('id'));
        var previewTitle = $('#title-' +targetInput.attr('id'));
        console.log('#title-' + targetInput.attr('id'))
        // Ouvrir la bibliothèque média WordPress
        var file_frame = wp.media({
            title: 'Choisir une image',
            button: { text: 'Utiliser cette image' },
            multiple: false
        });

        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            console.log(attachment)
            console.log(targetInput)
            console.log(previewImage)
            targetInput.val(attachment.url);
            previewImage.attr('src', attachment.url);
            previewTitle.text( attachment.filename)
        });

        file_frame.open();
    });

    // Gérer le clic sur le bouton "Supprimer"
    $('.rp-remove-button').on('click', function (e) {
        e.preventDefault();

        var targetInput = $($(this).data('target'));
        var previewImage = $('#preview-' + targetInput.attr('id'));
        var previewTitle = $('#title-' + targetInput.attr('id'));

        targetInput.val('');
        previewImage.attr('src', '');
        previewTitle.text('');
    });
});
