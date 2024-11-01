<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="options.php">
    <?php
    settings_fields("section-wrmr"); ?>
    <?php
    if(isset($_GET["page"]) && !empty($_GET["page"])) {
        $page = sanitize_text_field($_GET["page"]);
        if($page == "wr-settings") {
            if(isset($_GET["tab"]) && !empty($_GET["tab"])) {
                $tab = sanitize_text_field($_GET["tab"]);
            }
        }
    }
    ?>
    <ul class="nav nav-pills wiremo-tabs-options">
        <li class="<?php echo 'active'; ?>"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wr-settings&tab=general' ) ); ?>"><?php echo __("General","wiremo"); ?></a></li>
    </ul>
    <?php if( isset($_GET["settings-updated"]) ) { ?>
        <div id="message" class="updated inline">
            <p><strong><?php echo __("Your settings have been saved.","wiremo"); ?></strong></p>
        </div>
    <?php } ?>
    <div class="tab-content">
        <div id="general" class="tab-pane fade <?php echo ' in active show'; ?>">
            <?php do_settings_sections("wrmr-general-options"); ?>
        </div>
    </div>
    <?php
    submit_button();
    ?>
</form>



