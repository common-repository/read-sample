jQuery(document).ready(function($) {
    
    // Media uploader for image gallery
    var gallery_frame;
    $(document).on('click', '.upload_image_gallery_button', function(e) {
        e.preventDefault();

        // If the media frame already exists, reopen it.
        if (gallery_frame) {
            gallery_frame.open();
            return;
        }

        // Create a new media frame
        gallery_frame = wp.media({
            title: $(this).data('media-title'),
            button: {
                text: $(this).data('media-button-text')
            },
            multiple: true // Set to true to allow multiple files
        });

        // When multiple files are selected, run this callback
        gallery_frame.on('select', function() {
            var attachments = gallery_frame.state().get('selection').map(function(attachment) {
                attachment = attachment.toJSON();
                return attachment.url;
            });
            $('#_sample_images').val(attachments.join(',')); // Set the selected image URLs (comma-separated) to the input field
        });

        // Finally, open the modal
        gallery_frame.open();
    });

    // For color picker
    $('.rs-color-picker').wpColorPicker();
});

