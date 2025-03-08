<?php

namespace Helpers;

class FormValidation
{
    private array $formFields = [];
    private string $errorList = "";

    public function __construct(
        private string $errorMessageTitle = "The form could not be submitted due to the following errors:"
    ){}

    public function add(FormField $field): void
    {
        $this->formFields[] = $field;
    }

    public function getExtractableFormFields(): array
    {
        $values = [];
        foreach($this->formFields as $field){
            $values[$field->name] = $field;
        }
        return $values;
    }

    public function printFormFields(): void
    {
        if(count($this->formFields) > 0){
            foreach($this->formFields as $field) $field->printFormField();
        }
    }

    public function validateFormFields(): void
    {
        if(count($this->formFields) > 0){
            foreach($this->formFields as $field) $field->validate();
        }
    }

    public function setErrorMessageTitle(string $title): void
    {
        $this->errorMessageTitle = $title;
    }

    public function findErrors(): bool
    {
        if(count($this->formFields) > 0){
            foreach($this->formFields as $field){
                if($field->errors) return true;
            }
        }
        return false;
    }

    private function updateErrorList(): void
    {
        if(count($this->formFields) > 0){
            foreach($this->formFields as $field){
                if(count($field->errorList) > 0){
                    foreach($field->errorList as $errorMessage){
                        $this->errorList .= "<li>$errorMessage</li>";
                    }
                }
            }
        }
    }

    public function printErrorMessage(): void
    {
        $this->updateErrorList();
        if($this->findErrors()){
            echo "
            <div class='submit-error'>
                <strong>$this->errorMessageTitle</strong>
                <ul>
                    $this->errorList
                </ul>
                <p>Please fix the error above and resubmit.</p>
            </div>";
        }
    }

    public function printSubmitButton(string $name = 'submit_button', string $id = 'submitButton', string $value = 'Submit'): void
    {
        echo "
        <div class='large-input'>
            <p class='center'><input type='submit' class='button text' name='$name' id='$id' value='$value' /></p>
        </div>";
    }
}

?>