<?php
$priority_order = match ($sort_priority) {
    "" => "",
    "1" => "priority ASC, ",
    "2" => "priority DESC, ",
    default => "",
};
$price_order = match ($sort_price) {
    "" => "",
    "1" => "price * 1 ASC, ",
    "2" => "price * 1 DESC, ",
    default => "",
};
?>