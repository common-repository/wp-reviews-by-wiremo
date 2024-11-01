<script>
    var urlOauth = "<?php echo WRMR_URLAPP; ?>/v1/ecommerce/OAuth";
    var popupBlockerChecker = {
        check: function(popup_window){
            var _scope = this;
            if (popup_window) {
                if(/chrome/.test(navigator.userAgent.toLowerCase())){
                    setTimeout(function () {
                        _scope._is_popup_blocked(_scope, popup_window);
                    },200);
                }else{
                    popup_window.onload = function () {
                        _scope._is_popup_blocked(_scope, popup_window);
                    };
                }
            }else{
                _scope._displayError();
            }
        },
        _is_popup_blocked: function(scope, popup_window){
            if ((popup_window.innerHeight > 0)==false){ scope._displayError(); }
        },
        _displayError: function(){
            alert("Popup Blocker is enabled! Please add this site to your exception list.");
        }
    };
    var popup = window.open(urlOauth, "Login in wiremo", "toolbar=0,location=0,directories=0,status=1,menubar=0,titlebar=0,scrollbars=1,resizable=1,width=620,height=620");
    popupBlockerChecker.check(popup);
    function wrmrGoToStepOne() {
        jQuery(".wiremo-container .step2").removeClass("show");
        jQuery(".wiremo-container .step2").addClass("hidden");
        jQuery(".wiremo-container .step3").removeClass("show");
        jQuery(".wiremo-container .step3").addClass("hidden");
        jQuery(".wiremo-container .step1").removeClass("hidden");
        jQuery(".wiremo-container .step1").addClass("show");
    }
    window.addEventListener('message', function (event) {
        const origin = event.origin || event.originalEvent.origin;
        if (origin == '<?php echo WRMR_URLAPP; ?>') {
            var response = JSON.parse(event.data);
            if (response.apiKey) {
                var apiKey = response.apiKey;
                jQuery.post(ajaxurl, {action: 'wrmr_get_site_id', nonce: document.getElementById('noncewrmr_get_site_id').innerText, apiKey: apiKey}, function (data) {
                    jQuery(".wrmr-connect-account").bootstrapBtn('loading');
                    var requestData = JSON.parse(data);
                    if (requestData.success == true) {
                        jQuery.post(ajaxurl, {action: 'wrmr_add_api_key', nonce: document.getElementById('noncewrmr_add_api_key').innerText, apiKey: apiKey}, function () {
                            jQuery(".wrmr-connect-account").bootstrapBtn('loading');
                        });
                        jQuery.post(ajaxurl, {action: 'wrmr_add_register_hook', nonce: document.getElementById('noncewrmr_add_register_hook').innerText, apiKey: apiKey}, function () {
                            location.reload(true);
                        });
                    }
                    else {
                        if(requestData.message) {
                            jQuery(".wrmr-connect-account").bootstrapBtn('reset');
                            jQuery(".wiremo-container .load-account-box").html('<div class="notice notice-error is-dismissible"><p>' + requestData.message +'</p></div>');
                            jQuery(".wiremo-container .load-account-box").removeClass("hidden");
                            jQuery(".wiremo-container .load-account-box").addClass("show");
                            wrmrGoToStepOne();
                            jQuery(document).on("click",".connect-yes-wrmr",function () {
                                var url = "<?php echo home_url(); ?>";
                                jQuery.post(ajaxurl, {action: 'wrmr_validate_site', nonce: document.getElementById('noncewrmr_validate_site').innerText, apiKey: apiKey,url: url}, function (data) {
                                    var validateData = JSON.parse(data);
                                    if(validateData.success == true) {
                                        jQuery.post(ajaxurl, {action: 'wrmr_add_api_key', nonce: document.getElementById('noncewrmr_add_api_key').innerText, apiKey: apiKey}, function () {
                                            jQuery(".wrmr-connect-account").bootstrapBtn('loading');
                                        });
                                        jQuery.post(ajaxurl, {action: 'wrmr_add_register_hook', nonce: document.getElementById('noncewrmr_add_register_hook').innerText, apiKey: apiKey}, function () {
                                            location.reload(true);
                                        });
                                    }
                                    else {
                                        jQuery(".wrmr-connect-account").bootstrapBtn('reset');
                                        jQuery(".wiremo-container .load-account-box").html('<div class="notice notice-error is-dismissible"><p>' + validateData.message +'</p></div>');
                                        jQuery(".wiremo-container .load-account-box").removeClass("hidden");
                                        jQuery(".wiremo-container .load-account-box").addClass("show");
                                        wrmrGoToStepOne();
                                    }
                                });
                                return false;
                            });
                            jQuery(document).on("click",".connect-no-wrmr",function () {
                                jQuery.post(ajaxurl, {action: 'wrmr_no_validate_site', nonce: document.getElementById('noncewrmr_no_validate_site').innerText}, function (data) {
                                    jQuery(".wrmr-connect-account").bootstrapBtn('reset');
                                    jQuery(".wiremo-container .load-account-box").html('<div class="notice notice-error is-dismissible"><p>' + data +'</p></div>');
                                    jQuery(".wiremo-container .load-account-box").removeClass("hidden");
                                    jQuery(".wiremo-container .load-account-box").addClass("show");
                                    wrmrGoToStepOne();
                                });
                                return false;
                            });
                        }
                    }
                });
            }
        }
    });
</script>