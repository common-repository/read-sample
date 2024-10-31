jQuery(document).ready(function($) {
    $('.read-sample-btn').click(function(e) {
        e.preventDefault(); // Prevent page reload
        
        var productId = $(this).data('product-id');
        
        $.ajax({
            url: read_sample_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'read_sample_load_content',
                product_id: productId,
                nonce: read_sample_ajax.nonce // Add nonce for security
            },
            success: function(response) {
                $('#sample-content').html(response);
                $('#read-sample-popup').fadeIn();
            },
            error: function() {
                alert('Failed to load sample content.');
            }
        });
    });

    $('.close-popup').click(function() {
        $('#read-sample-popup').fadeOut();
    });
});
