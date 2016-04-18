<div id="llms-login-fields">
    <?php llms_get_template( 'global/form-login-inner.php' ); ?>
    <input type="hidden" name="llms-login" value="1">
    <a href="#" class="llms-toggle" data-parent="llms-login-fields" data-target="llms-register-fields">Register</a>
</div>
<div id="llms-register-fields" style="display: none;">
    <?php llms_get_template( 'global/form-registration-inner.php' ); ?>
    <input type="hidden" name="llms-registration" value="1">
    <a href="#" class="llms-toggle" data-parent="llms-register-fields" data-target="llms-login-fields">Login</a>
</div>

<script type="text/javascript">
    (function($) {
        $('#llms-register-fields input').attr('disabled', true);

        $('.llms-toggle').on('click', function(e) {
            e.preventDefault();

            var parent = $(e.currentTarget).data('parent');
            var target = $(e.currentTarget).data('target');

            $('#' + parent).slideUp();
            $('#' + target).slideDown();

            $('#' + target + ' input').removeAttr('disabled');
            $('#' + parent + ' input').attr('disabled', true);
        })
    })(jQuery);
</script>
