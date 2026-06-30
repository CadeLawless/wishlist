function isTruncated(el) {
    /* var $c = el
           .clone()
           .css({display: 'inline', width: 'auto', visibility: 'hidden'})
           .appendTo('body');
    var cloneWidth = $c.width;
    $c.remove();
    return cloneWidth > $element.width(); */
    return el.scrollWidth > el.clientWidth;
}

function checkEllipsis() {
  $(".item-container").each(function () {
    const $container = $(this);

    // TITLE CHECK
    const $title = $container.find("h3");
    const titleTruncated = $title.length && isTruncated($title[0]);
    $container.find(".see-more-link.title-link").toggle(titleTruncated);

    // NOTES CHECK
    const $notes = $container.find(".line.notes-line");
    const notesTruncated = $notes.length && isTruncated($notes[0]);
    $container.find(".see-more-link.notes-link").toggle(notesTruncated);
  });
}

// run after page load
$(document).ready(function () {
  $(window).on("load", checkEllipsis);

  // re-check on resize
  $(window).on("resize", checkEllipsis);

  $(document.body).on("click", ".see-more-link", function(e) {
    e.preventDefault();
    $link = $(this);
    $overflowEl = $link.parent().find("h3");
    if ($overflowEl.length == 0) $overflowEl = $link.parent();
    if ($link.text().includes("View")) {
        $link.text($link.text().replace("View", "Hide"));
        $overflowEl.addClass("view-full");
    } else {
        $link.text($link.text().replace("Hide", "View"));
        $overflowEl.removeClass("view-full");
    }
  });
});