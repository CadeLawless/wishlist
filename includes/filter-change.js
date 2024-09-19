$(document).ready(function() {
    $items_list_sub_container = $(".items-list-sub-container");
    $urlSearchParams = new URLSearchParams(window.location.search);
    // filters changed event function
    function filter_change(){
        $sort_price = $("#sort-price").val();
        $sort_priority = $("#sort-priority").val();
        $.ajax({
            type: "POST",
            url: "includes/ajax/filter-change-"+$type+".php?"+$urlSearchParams.toString(),
            data: {
                sort_price: $sort_price,
                sort_priority: $sort_priority,
            },
            success: function(html) {
                $items_list_sub_container.html(html);
                $url = new URL(location.href);
                $url.searchParams.set("pageno", "1");
                window.history.replaceState(null, null, $url);
            }
        });
    }

    $(".select-filter").on("change", function(){
        filter_change();
    });
});