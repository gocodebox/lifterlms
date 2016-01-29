var $ = jQuery;
var deleteIds = [];

$(document).ready(function () {

    $('#llms_voucher_add_codes').click(function (e) {
        e.preventDefault();

        var qty = $('#llms_voucher_add_quantity').val();
        var uses = $('#llms_voucher_add_uses').val();
        var html = '';

        if ($.isNumeric(qty) && $.isNumeric(uses)) {
            if (parseInt(qty) > 0) {
                for (var i = 1; i <= parseInt(qty); i++) {
                    html += '<tr>' +
                        '<td></td>' +
                        '<td>' +
                        '<input type="text" placeholder="Code" value="' + randomizeCode() + '" name="llms_voucher_code[]">' +
                        '<input type="hidden" name="llms_voucher_code_id[]" value="0">' +
                        '</td>' +
                        '<td><span>0 / </span><input type="text" placeholder="Uses" value="' + uses + '" class="llms-voucher-uses" name="llms_voucher_uses[]"></td>' +
                        '<td><a href="#" class="llms-voucher-delete">' + deleteIcon + '</a></td>' +
                        '</tr>';
                }
            }
        }

        $('#llms_voucher_tbody').append(html);

        bindDeleteVoucherCode();
    });

    bindDeleteVoucherCode();
});

function randomizeCode() {
    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < 12; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function bindDeleteVoucherCode() {
    $('.llms-voucher-delete').click(function (e) {
        e.preventDefault();

        var t = $(this);
        var old = t.data('id');

        if (old) {
            deleteIds.push(old);

            $('#delete_ids').val(deleteIds.join(','));
        }

        // remove html block
        t.closest('tr').remove();
    });
}