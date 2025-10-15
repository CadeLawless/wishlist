<?php
use App\Helpers\ThemePopupHelper;
?>
<div>
    <div class="form-container">
        <h1>New Wish List</h1>
        <form method="POST" action="/wishlist/">
            <?php echo $error_msg ?? ""; ?>
            <div class="flex form-flex">
                <div class="large-input">
                    <label for="wishlist_type">Type:<br/></label>
                    <select required name="wishlist_type" id="wishlist_type">
                        <option value="" disabled <?php if(($wishlist_type ?? '') == "") echo "selected"; ?>>Select an option</option>
                        <option value="Birthday" <?php if(($wishlist_type ?? '') == "Birthday") echo "selected"; ?>>Birthday</option>
                        <option value="Christmas" <?php if(($wishlist_type ?? '') == "Christmas") echo "selected"; ?>>Christmas</option>
                    </select>
                </div>
                <div class="large-input">
                    <label for="theme">Theme:</label><br />
                    <a style="margin-bottom: 10px;" class="choose-theme-button button primary popup-button<?php if(($wishlist_type ?? '') == "") echo " disabled"; ?>" href="#">Choose a theme...<span class="inline-popup<?php if(($wishlist_type ?? '') != "") echo " hidden"; ?>">Please select a type</span></a>
                    <?php
                    // Include theme popup functionality using MVC helper
                    echo ThemePopupHelper::renderThemePopup("birthday");
                    echo ThemePopupHelper::renderThemePopup("christmas");
                    ?>
                    <div class="theme-results">
                        <div class="theme-background-display desktop-background-display"></div>
                        <div class="theme-background-display mobile-background-display"></div>
                        <div class="theme-gift-wrap-display"></div>
                    </div>
                    <input type="hidden" id="theme_background_id" name="theme_background_id" value="<?php echo $theme_background_id ?? ''; ?>" />
                    <input type="hidden" id="theme_gift_wrap_id" name="theme_gift_wrap_id" value="<?php echo $theme_gift_wrap_id ?? ''; ?>" />
                </div>
                <div class="large-input">
                    <label for="wishlist_name">Name:<br/></label>
                    <input required type="text" id="wishlist_name" autocapitalize="words" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name ?? ''); ?>" />
                </div>
                <div class="large-input">
                    <p class="center"><input type="submit" class="button text" name="submit_button" id="submitButton" value="Create" /></p>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    #body {
        padding-top: 84px;
    }
    .form-container {
        margin: clamp(20px, 4vw, 60px) auto 30px;
        background-color: var(--background-darker);
        max-width: 500px;
    }
</style>

<script src="public/js/popups.js"></script>
<script src="public/js/choose-theme.js"></script>
<script>
    let name_input = document.querySelector("#wishlist_name");
    name_input.addEventListener("focus", function(){
        this.select();
    });

    let type_select = document.querySelector("#wishlist_type");
    type_select.addEventListener("change", function(){
        let current_year = new Date().getFullYear();
        if(this.value == "Birthday"){
            name_input.value = "<?php echo $user['name']; ?>'s " + current_year + " Birthday Wish List";
            $(".popup-container.birthday").insertBefore(".popup-container.christmas");
        }else if(this.value == "Christmas"){
            name_input.value = "<?php echo $user['name']; ?>'s " + current_year + " Christmas Wish List";
            $(".popup-container.christmas").insertBefore(".popup-container.birthday");
        }
        if(this.value != ""){
            document.querySelector(".choose-theme-button").classList.remove("disabled");
        }else{
            document.querySelector(".choose-theme-button").classList.add("disabled");
        }
    });

    let submit_button = document.querySelector("#submitButton");
    // on submit, disable submit so user cannot press submit twice
    document.querySelector("form").addEventListener("submit", function(e){
        setTimeout( () => {
            submit_button.setAttribute("disabled", "");
            submit_button.value = "Creating...";
            submit_button.style.cursor = "default";
        });
    });
</script>