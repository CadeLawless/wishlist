<?php

namespace Helpers;

class PopupManager {
    private array $messages = [];

    public function __construct(array $popupNames) {
        $this->collectMessages($popupNames);
    }

    private function collectMessages(array $popupNames): void {
        foreach($popupNames as $name){
            $messageKey = $name . "Message";
            if(isset($_SESSION[$name], $_SESSION[$messageKey])){
                $this->messages[] = $_SESSION[$messageKey];
                unset($_SESSION[$name], $_SESSION[$messageKey]);
            }
        }
    }

    public function hasMessages(): bool {
        return !empty($this->messages);
    }

    public function renderPopups(): void {
        foreach($this->messages as $msg){
            echo "
            <div class='popup-container'>
                <div class='popup active'>
                    <div class='close-button-container'>";
                    require("C:/inetpub/wwwroot/web_apps/laker-images/laker/laker-cms-images/popup-close.php");
                    echo "
                    </div>
                    <div class='popup-content'>
                        <p>$msg</p>
                    </div>
                </div>
            </div>";
        }
    }

    public function getMessages(): array {
        return $this->messages;
    }
}
?>