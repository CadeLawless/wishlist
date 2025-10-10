/**
 * Copy Select Functionality
 * Handles copying items between wishlists
 */

$(document).ready(function() {
    
    // Copy select functionality
    $(".copy-select").on("change", function(e) {
        const $select = $(this);
        const wishlistId = $select.val();
        const copyFrom = $select.attr("id") === "other_wishlist_copy_from" ? "Yes" : "No";
        const baseUrl = $select.data('base-url');

        if (wishlistId && baseUrl) {
            $.ajax({
                type: "POST",
                url: baseUrl + "/items",
                data: {
                    wishlist_id: wishlistId,
                    copy_from: copyFrom,
                },
                success: function(html) {
                    $select.next().removeClass("hidden");
                    $select.next().find(".item-checkboxes").html(html);
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load items:', error);
                    alert('Failed to load items from selected wishlist');
                }
            });
        }
    });
    
});
