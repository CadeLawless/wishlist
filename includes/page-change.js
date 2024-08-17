$(document).ready(function() {
    let searchParams;
    $items_list = $(".items-list");
    // paginate arrow click
    $(".paginate-arrow").on("click", function(e) {
        e.preventDefault();
        let pageno, id;
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
            new_pageno = $(".last-page").text();
        }

        $.ajax({
            //AJAX type is "Post".
            type: "POST",
            //Data will be sent to "ajax.php".
            url: "includes/ajax/page-change.php",
            //Data, that will be sent to "ajax.php".
            data: {
                //Assigning value of "name" into "search" variable.
                new_page: new_pageno,
            },
            //If result found, this funtion will be called.
            success: function(html) {
                //Assigning result to "display" div in "search.php" file.
                $items_list.html(html);
                searchParams = new URLSearchParams(window.location.search);
                if(searchParams.has("id")){
                    id = searchParams.get("id");
                    window.history.replaceState(null, null, "?id="+id+"&pageno="+new_pageno);
                }else{
                    window.history.replaceState(null, null, "?pageno="+new_pageno);
                }
                $(".page-number").text(new_pageno);
                if(new_pageno != "1"){
                    $(".paginate-first, .paginate-previous").removeClass("disabled");
                }else{
                    $(".paginate-first, .paginate-previous").addClass("disabled");
                }
                if(new_pageno != parseInt($(".last-page").text())){
                    $(".paginate-next, .paginate-last").removeClass("disabled");
                }else{
                    $(".paginate-next, .paginate-last").addClass("disabled");
                }
            }
        });
        $.ajax({
            //AJAX type is "Post".
            type: "POST",
            //Data will be sent to "ajax.php".
            url: "includes/ajax/count-showing.php",
            //Data, that will be sent to "ajax.php".
            data: {
                //Assigning value of "name" into "search" variable.
                new_page: new_pageno,
            },
            //If result found, this funtion will be called.
            success: function(html) {
                //Assigning result to "display" div in "search.php" file.
                $(".count-showing").text(html);
            }
        });
    });

});