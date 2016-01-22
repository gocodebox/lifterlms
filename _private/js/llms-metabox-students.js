jQuery(document).ready(function($) {
    jQuery(".add-student-select").select2({
        width: '100%',
        ajax: {
            url: "admin-ajax.php",
            method: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term, // search term
                    page: params.page,
                    action: 'get_students',
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        console.log(item);
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            },
            cache: true,
        }
    });

    jQuery(".remove-student-select").select2({
        width: '100%',
        ajax: {
            url: "admin-ajax.php",
            method: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term, // search term
                    page: params.page,
                    action: 'get_enroled_students',
                    postId: jQuery('#post_ID').val(),
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        console.log(item);
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            },
            cache: true,
        }
    });
});
