$(document).ready(function() {
    window.addEventListener("popstate", function(){
        window.location.reload();
    });
    let searchParams;
    // paginate arrow click
    $(document.body).on("click", ".paginate-arrow", function(e) {
        $items_list = $type == "wisher" || $type == "buyer" ? $(".items-list.main") : $(".results-table");
        e.preventDefault();
        let pageno, id, key;
        searchParams = new URLSearchParams(window.location.search);
        if(searchParams.has("pageno")){
            pageno = searchParams.get("pageno");
        }else{
            pageno = 1;
        }
        let new_pageno;
        if($(this).hasClass("paginate-first")){
            new_pageno = "1";
        }else if($(this).hasClass("paginate-previous")){
            new_pageno = parseInt(pageno) - 1;
        }else if($(this).hasClass("paginate-next")){
            new_pageno = parseInt(pageno) + 1;
        }else if($(this).hasClass("paginate-last")){
            new_pageno = parseInt($(".last-page").first().text());
            console.log(new_pageno);
        }

        $.ajax({
            type: "POST",
            url: "includes/ajax/page-change.php?"+$key_url+"type="+$type,
            data: {
                new_page: new_pageno,
            },
            success: function(html) {
                $items_list.html(html);
                $(".items-list-title")[0].scrollIntoView();
                window.history.pushState({}, "", window.location);
                searchParams = new URLSearchParams(window.location.search);
                if(searchParams.has("id")){
                    id = searchParams.get("id");
                    window.history.replaceState(null, null, "?id="+id+"&pageno="+new_pageno);
                }else if(searchParams.has("key")){
                    key = searchParams.get("key");
                    window.history.replaceState(null, null, "?key="+key+"&pageno="+new_pageno);
                }else{
                    window.history.replaceState(null, null, "?pageno="+new_pageno);
                }
                $(".page-number").text(new_pageno);
                if(new_pageno != "1"){
                    $(".paginate-first, .paginate-previous").removeClass("disabled");
                }else{
                    $(".paginate-first, .paginate-previous").addClass("disabled");
                }
                if(new_pageno != parseInt($(".last-page").first().text())){
                    $(".paginate-next, .paginate-last").removeClass("disabled");
                }else{
                    $(".paginate-next, .paginate-last").addClass("disabled");
                }
            }
        });
        $.ajax({
            type: "POST",
            url: "includes/ajax/count-showing.php?"+$key_url+"type="+$type,
            data: {
                new_page: new_pageno,
            },
            success: function(html) {
                $(".count-showing").text(html);
            }
        });
    });

});