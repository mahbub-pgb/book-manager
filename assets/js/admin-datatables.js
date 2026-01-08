jQuery(document).ready(function($) {
    
    // Initialize DataTables for Authors table
    if ($('#authors-table').length) {
        // Check if table has data rows
        var hasData = $('#authors-table tbody tr td').length > 0 && 
                      !$('#authors-table tbody tr td').first().attr('colspan');
        
        if (hasData) {
            $('#authors-table').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[2, 'asc']], // Sort by Name column
                columnDefs: [
                    { 
                        targets: -1, // Actions column
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 0, // ID column
                        width: '60px'
                    },
                    {
                        targets: 1, // Image column
                        width: '80px',
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 2, // Name column
                        width: '200px'
                    }
                ],
                language: {
                    search: "Search Authors:",
                    lengthMenu: "Show _MENU_ authors per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ authors",
                    infoEmpty: "No authors found",
                    infoFiltered: "(filtered from _MAX_ total authors)",
                    zeroRecords: "No matching authors found",
                    emptyTable: "No authors available",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">'
            });
        }
    }

    // Initialize DataTables for Publishers table
    if ($('#publishers-table').length) {
        // Check if table has data rows
        var hasData = $('#publishers-table tbody tr td').length > 0 && 
                      !$('#publishers-table tbody tr td').first().attr('colspan');
        
        if (hasData) {
            $('#publishers-table').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[1, 'asc']], // Sort by Name column
                columnDefs: [
                    { 
                        targets: -1, // Actions column
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 0, // ID column
                        width: '60px'
                    },
                    {
                        targets: 1, // Name column
                        width: '200px'
                    },
                    {
                        targets: 3, // Website column
                        width: '250px'
                    }
                ],
                language: {
                    search: "Search Publishers:",
                    lengthMenu: "Show _MENU_ publishers per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ publishers",
                    infoEmpty: "No publishers found",
                    infoFiltered: "(filtered from _MAX_ total publishers)",
                    zeroRecords: "No matching publishers found",
                    emptyTable: "No publishers available",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">'
            });
        }
    }

    // Optional: Add custom styling for better WordPress integration
    $('.dataTables_wrapper').addClass('wp-clearfix');
    
    // Add WordPress button style to pagination buttons
    $('.dataTables_paginate .paginate_button').each(function() {
        if (!$(this).hasClass('current') && !$(this).hasClass('disabled')) {
            $(this).hover(
                function() { $(this).addClass('hover'); },
                function() { $(this).removeClass('hover'); }
            );
        }
    });
    
});

// Media Uploader for Author Images
jQuery(document).ready(function($) {
    var mediaUploader;
    
    $('#upload_author_image_button').on('click', function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Choose Author Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        
        // When an image is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#author_image_url').val(attachment.url);
            $('#author-image-preview img').attr('src', attachment.url).show();
            $('#remove_author_image_button').show();
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // Remove image button
    $('#remove_author_image_button').on('click', function(e) {
        e.preventDefault();
        $('#author_image_url').val('');
        $('#author-image-preview img').attr('src', '').hide();
        $(this).hide();
    });
});