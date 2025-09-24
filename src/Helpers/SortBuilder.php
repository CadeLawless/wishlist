<?php

namespace Helpers;

class SortBuilder {
    private array $sorts = [];

    public function add(string $column, ?string $direction): void {
        if($direction !== "" && $direction !== null){
            $this->sorts[] = $column . " " . ($direction === "1" ? "ASC" : "DESC");
        }
    }

    public function getSorts(): array {
        return $this->sorts;
    }
}
?>