(function( $ ){

    var deleteIds = [];

    $(document).ready(function () {

        var changeNotSaved = false;
        var codesAddedSinceLastSave = 0;

        $('#llms_voucher_add_codes').click(function (e) {
            e.preventDefault();

            var qty = $('#llms_voucher_add_quantity').val();
            var uses = $('#llms_voucher_add_uses').val();
            var html = '';

            changeNotSaved = true;

            if ($.isNumeric(qty) && $.isNumeric(uses)) {
                if (parseInt(qty) > 0 && parseInt(uses) > 0) {

                    if(qty > 50) {
                        alert("You can only generate 50 rows at a time");
                        return;
                    }

                    codesAddedSinceLastSave += parseInt(qty);

                    if(codesAddedSinceLastSave > 50) {
                        alert("Please save before adding any more codes, limit is 50 at a time");
                        codesAddedSinceLastSave -= parseInt(qty);
                        return;
                    }

                    for (var i = 1; i <= parseInt(qty); i++) {
                        html += '<tr>' +
                            '<td></td>' +
                            '<td>' +
                            '<input type="text" maxlength="20" placeholder="Code" value="' + randomizeCode() + '" name="llms_voucher_code[]">' +
                            '<input type="hidden" name="llms_voucher_code_id[]" value="0">' +
                            '</td>' +
                            '<td><span>0 / </span><input type="text" placeholder="Uses" value="' + uses + '" class="llms-voucher-uses" name="llms_voucher_uses[]"></td>' +
                            '<td><a href="#" class="llms-voucher-delete">' + delete_icon + '</a></td>' +
                            '</tr>';
                    }
                }
            }

            $('#llms_voucher_tbody').append(html);

            bindDeleteVoucherCode();
        });

        bindDeleteVoucherCode();

        $('#llms_voucher_tbody input').change(function() {
            changeNotSaved = true;
        });

        $( "#post" ).on( 'submit', function() {
            if($('#publish').attr('name') === 'publish') {
                $('<input />').attr('type', 'hidden')
                    .attr('name', "publish")
                    .attr('value', "true")
                    .appendTo('#post');
            }
            return true;
        } );

        window.onbeforeunload = function() {
            return changeNotSaved ? "If you leave this page you will lose your unsaved changes." : null;
        };

        $('input[type=submit]').click(function (e) {
            var unique_values = {};
            var duplicate = false;
            $('input[name="llms_voucher_code[]"]').each(function() {
                var val = $(this).val()
                if ( ! unique_values[val] ) {
                    unique_values[val] = true;
                } else {
                    $(this).css('background-color', 'rgba(226, 96, 73, 0.6)');
                    duplicate = true;
                }
            });
            if(duplicate) {
                alert('Please make sure that there are no duplicate voucher codes.');
                return false;
            }

            //if course or membership is not selected, don't allow user to save
            if(!($('#_llms_voucher_courses').val() || $('#_llms_voucher_membership').val())) {
                alert('Please select course or membership before saving.');
                return false;
            }

            changeNotSaved = false;
            check_voucher_duplicate();
            return false;
        });

        function bindDeleteVoucherCode() {
            $('.llms-voucher-delete').unbind('click');
            $('.llms-voucher-delete').click(function (e) {
                e.preventDefault();

                var t = $(this);
                var old = t.data('id');

                changeNotSaved = true;

                if (old) {
                    deleteIds.push(old);

                    $('#delete_ids').val(deleteIds.join(','));
                } else {
                    codesAddedSinceLastSave--;
                }

                // remove html block
                t.closest('tr').remove();
            });
        }
    });
    function randomizeCode() {
        var text = '';
        var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for (var i = 0; i < 12; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }

    /**
     * Check for voucher duplicates in other posts.
     */
    function check_voucher_duplicate() {

        var codes = get_codes_from_inputs();

        var data = {action: 'check_voucher_duplicate', 'postId' : jQuery('#post_ID').val(), 'codes' : codes };

        var ajax = new Ajax('post', data, false);
        ajax.check_voucher_duplicate();
    }

    function get_codes_from_inputs() {
        var codes = [];
        $('input[name="llms_voucher_code[]"]').each(function() {
            codes.push($(this).val());
        });

        return codes;
    }

})(jQuery);

function llms_on_voucher_duplicate (results) {
    if(results.length) {
        for(var i = 0; i < results.length; i++ ) {
            jQuery('input[value="' + results[i].code + '"]').css('background-color', 'rgba(226, 96, 73, 0.6)');
        }
        alert('Please make sure that there are no duplicate voucher codes.');
    } else {
        jQuery( "#post" ).submit();
    }
}
