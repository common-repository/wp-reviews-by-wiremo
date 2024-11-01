<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$wrmr_current_user_id = get_current_user_id();
$wrmr_user_info = get_userdata($wrmr_current_user_id);
if(!empty($wrmr_user_info->first_name)) {
    $wrmr_name = $wrmr_user_info->first_name;
}
else {
    $wrmr_name = "";
}
if(!empty($wrmr_user_info->last_name)) {
    $wrmr_surname = $wrmr_user_info->last_name;
}
else {
    $wrmr_surname = "";
}
if(!empty($wrmr_user_info->user_email)) {
    $wrmr_email = $wrmr_user_info->user_email;
}
$wrmr_step = "step2";
(strlen($wrmr_name) == 0 || strlen($wrmr_surname) == 0) ? $wrmr_step = "step3" : $wrmr_step = "step2";
?>
<div class="load-account-box"></div>
<div class="step1">
    <table class="form-table">
        <tbody>
        <tr>
            <th><?php echo __("Start using Wiremo","wiremo"); ?></th>
            <td>
                <button class="wrmr-account btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i>  Loading"><?php echo __("Connect your Wiremo account","wiremo"); ?>
                </button>
            </td>
        </tr>
        <tr>
            <th><?php echo __("I don’t have a Wiremo account","wiremo"); ?></th>
            <td>
                <button data-step="<?php echo $wrmr_step; ?>" class="wrmr-register-step btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i>  Loading"><?php echo __("Create Wiremo account","wiremo"); ?>
                </button>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="step2">
    <table class="form-table">
        <tbody>
        <tr>
            <th class="wrmr-user-info" colspan="2">
                <p>Hey <?php echo $wrmr_name; ?>,<br>
                    Wiremo is a customer reviews <strong>service</strong> - the reviews are stored on Wiremo’s servers.<br>
                    To use the plugin we need to connect your account to <a target="_blank" href="https://wiremo.co/">Wiremo.co</a> Dashboard.</p>
            </th>
        </tr>
        <tr>
            <th><?php echo __("First name","wiremo"); ?></th>
            <td>
                <input readonly class="wrmr-first-name" name="first-name" type="text" value="<?php echo $wrmr_name; ?>">
            </td>
        </tr>
        <tr>
            <th><?php echo __("Last name","wiremo"); ?></th>
            <td>
                <input readonly class="wrmr-last-name" name="last-name" type="text" value="<?php echo $wrmr_surname; ?>">
            </td>
        </tr>
        <tr>
            <th><?php echo __("Email","wiremo"); ?></th>
            <td>
                <input readonly class="wrmr-email" name="email" type="email" value="<?php echo $wrmr_email; ?>">
            </td>
        </tr>
        <tr>
            <th class="wrmr-user-info" colspan="2">
                <p>Can we use your WordPress account details to register you in Wiremo Dashboard?</p>
            </th>
        </tr>
        <tr>
            <th colspan="2" class="wrmr-register-options">
                <button data-action="step2" class="wrmr-register-account btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i>  Loading"><?php echo __("Yes, use this details ","wiremo"); ?>
                </button>
                <button class="wrmr-register-back btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i>  Loading"><?php echo __("No, I would like to change","wiremo"); ?>
                </button>
                <p><?php echo __("or","wiremo"); ?> <a class="wrmr-connect-account" href="#"><?php echo __("I already have an account","wiremo") ?></a></p>
            </th>
        </tr>
        </tbody>
    </table>
</div>
<div class="step3">
    <table class="form-table">
        <form method="POST" action="">
            <tbody>
            <tr>
                <th class="wrmr-user-info" colspan="2">
                    <p>Hey <?php echo $wrmr_name; ?>,<br>
                        Wiremo is a customer reviews <strong>service</strong> - the reviews are stored on Wiremo’s servers.<br>
                        To use the plugin we need to connect your account to <a target="_blank" href="https://wiremo.co/">Wiremo.co</a> Dashboard.</p>
                </th>
            </tr>
            <tr>
                <th><?php echo __("First name","wiremo"); ?></th>
                <td>
                    <input class="wrmr-first-name" name="first-name" type="text" value="<?php echo $wrmr_name; ?>">
                </td>
            </tr>
            <tr>
                <th><?php echo __("Last name","wiremo"); ?></th>
                <td>
                    <input class="wrmr-last-name" name="last-name" type="text" value="<?php echo $wrmr_surname; ?>">
                </td>
            </tr>
            <tr>
                <th><?php echo __("Email","wiremo"); ?></th>
                <td>
                    <input class="wrmr-email" name="email" type="email" value="<?php echo $wrmr_email; ?>">
                </td>
            </tr>
            <tr>
                <th class="wrmr-register-options" colspan="2">
                    <button data-action="step3" class="wrmr-register-account btn btn-primary"
                            data-loading-text="<i class='fa fa-spinner fa-spin'></i>  Loading"><?php echo __("Sign UP","wiremo"); ?>
                    </button>
                    <p><?php echo __("or","wiremo"); ?> <a class="wrmr-connect-account" href="#"><?php echo __("I already have an account","wiremo") ?></a></p>
                </th>
            </tr>
            </tbody>
        </form>
    </table>
</div>