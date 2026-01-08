jQuery(document).ready(function($) {
    
    // Initialize DataTables for Authors table
    if ($('#authors-table').length) {
        $('#authors-table').DataTable({
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

    // Initialize DataTables for Publishers table
    if ($('#publishers-table').length) {
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