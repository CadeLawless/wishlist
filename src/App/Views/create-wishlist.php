<?php
require(__DIR__ . "/includes/header.php");
require(__DIR__ . "/includes/nav.php");

$formFields = $formValidation->getExtractableFormFields();
extract($formFields);
$siteImageFolder = "$homeDir/wishlist1/public/assets/images/site-images";
?>

<div id="container">
<div>
    <div class="form-container">
        <h1>New Wish List</h1>
        <form method="POST" action="">
            <?php $formValidation->printErrorMessage(); ?>
            <div class="flex form-flex">
                <?php $wishlist_type->printFormField(); ?>
                <div class="large-input">
                    <label for="theme">Theme:</label><br />
                    <a style="margin-bottom: 10px;" class="choose-theme-button button primary popup-button<?php if($wishlist_type->value == "") echo " disabled"; ?>" href="#">Choose a theme...<span class="inline-popup<?php if($wishlist_type->value != "") echo " hidden"; ?>">Please select a type</span></a>
                    <div class='popup-container first hidden'>
                        <div class='popup fullscreen theme-popup-container'>
                        <img class='background-theme desktop-background hidden' src="" />
                        <img class='background-theme mobile-background hidden' src="" />
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                <?php require("$siteImageFolder/menu-close.php"); ?>
                                </a>
                            </div>
                            <div class="theme-content">
                                <h2 class="theme-header background-header" style="margin-top: 0;">Choose a Background</h2>
                                <div class='popup-content choose-theme-popup'>
                                    <div class="theme-list"></div>
                                </div>
                            </div>
                            <div class="gift-wrap-content hidden">
                                <p style="padding-left: calc(5% - 10px); text-align: left;"><a class="button accent back-to" href="#">Back to Backgrounds</a></p>
                                <h2 class="theme-header gift-wrap-header" style="margin-top: 0;">
                                    <div>Choose a Gift Wrap<a class='button primary continue-button' href='#'>Continue</a>
                                    </div>
                                </h2>
                                <div class='popup-content no-margin-top'>
                                    <div class='theme-header' style="margin: 0 0 15px; width: auto;">Gift wraps will be displayed over any purchased item images. You will never see the gift wraps. Only the people who purchase items off your wish list will see them.</div>
                                    <div class="theme-dropdown background-dropdown">
                                        <strong>Background:</strong>
                                        <div class="image-dropdown background" style="margin-bottom: 10px;">
                                            <div class="selected-option">
                                                <span class="value"></span>
                                                <span class="preview-image desktop-image"></span>
                                                <span class="preview-image mobile-image"></span>
                                                <span class="popup-plus"><?php require("$siteImageFolder/icons/plus.php"); ?></span>
                                            </div>
                                            <div class="options hidden">
                                                <div class='close-container options-close'>
                                                    <a href='#' class='close-button'>
                                                    <?php require("$siteImageFolder/menu-close.php"); ?>
                                                    </a>
                                                </div>
                                                <div class="options-content"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="theme-dropdown gift-wrap-dropdown">
                                        <strong>Gift Wrap:</strong>
                                        <div class="image-dropdown gift-wrap">
                                            <div class="selected-option">
                                                <span class="value"></span>
                                                <span class="preview-image"></span>
                                                <span class="popup-plus"><?php require("$siteImageFolder/icons/plus.php"); ?></span>
                                            </div>
                                            <div class="options hidden">
                                                <div class='close-container options-close'>
                                                    <a href='#' class='close-button'>
                                                    <?php require("$siteImageFolder/menu-close.php"); ?>
                                                    </a>
                                                </div>
                                                <div class="options-content"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $item->writeTemplateItems(wrapID: $theme_gift_wrap_id->value); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="theme-results">
                        <div class="theme-background-display desktop-background-display"></div>
                        <div class="theme-background-display mobile-background-display"></div>
                        <div class="theme-gift-wrap-display"></div>
                    </div>
                    <?php
                    $theme_background_id->printFormField(inputOnly: true);
                    $theme_gift_wrap_id->printFormField(inputOnly: true);
                    ?>
                </div>
                <?php
                $wishlist_name->printFormField();
                $formValidation->printSubmitButton(value: 'Create');
                ?>
            </div>
        </form>
    </div>
</div>

<?php require(__DIR__ . "/includes/footer.php"); ?>

<script src="/wishlist1/public/assets/js/popup.js"></script>
<script src="/wishlist1/public/assets/js/choose-theme.js"></script>
<?php require("$homeDir/wishlist1/public/assets/js/create-wishlist-js.php"); ?>