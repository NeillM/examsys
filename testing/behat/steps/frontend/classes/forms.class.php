<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;

/**
 * Step definitions for interacting with web forms.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait forms
{
    /**
     * Fill in a form field.
     *
     * @Given /^I set the field "([^"]*)" to "([^"]*)"$/
     *
     * @param string $field The name, id or label of the field
     * @param string $value The value the field should be set to
     * @throws \Exception
     */
    public function i_set_field($field, $value)
    {
        $element = $this->find('field', $field);

        if (is_null($element)) {
            $element = $this->find('fieldset', $field);
        }

        if (is_null($element)) {
            throw new \Exception("The form field $field could not be found");
        }

        $formvalue = $value;

        // We will try to change the human readable value into the value the form will use.
        switch ($element->getTagName()) {
            case 'select':
                $formvalue = $this->getSelectValue($element, $value);
                break;
            case 'fieldset':
                [$element, $formvalue] = $this->getFieldsetValue($element, $value);
                break;
        }

        $element->setValue($formvalue);
    }

    /**
     * Gets the value needed to set an element in a field set.
     *
     * This is most likely going to be used to set the value for a radio button,
     * which require that they are set by using the value of their value attribute.
     *
     * @param NodeElement $element A fieldset element
     * @param string $value Label for an element in the field set
     * @return array An array containing the element and the value that it uses
     */
    protected function getFieldsetValue(\Behat\Mink\Element\NodeElement $element, $value): array
    {
        // First try to find an option that contains the value, for some reason the field selector does not work here.
        $labelxpath = '//label[contains(normalize-space(.) , "' . $value . '")]';
        $label = $element->find('xpath', $labelxpath);

        if (is_null($label)) {
            throw new \Exception("$value could not be found");
        }

        $id = $label->getAttribute('for');

        $field = $element->find('css', 'input[id=' . $id . ']');

        if ($field) {
            return [$field, $field->getAttribute('value')];
        }

        // Failing that assume the value is the raw value for the field already.
        return [$element, $value];
    }

    /**
     * Tried to convert the select value into a raw form value.
     *
     * @param NodeElement $element
     * @param mixed $value
     * @return mixed
     */
    protected function getSelectValue(\Behat\Mink\Element\NodeElement $element, $value)
    {
        // First try to find an option that contains the value.
        $xpath = '//option[contains(normalize-space(.) , "' . $value . '")]';
        $option = $element->find('xpath', $xpath);

        if ($option) {
            return $option->getAttribute('value');
        }

        // Failing that assume the value is the raw value for the field already.
        return $value;
    }

    /**
     * Fills in multiple fields in a form.
     *
     * Requires the following values:
     * - field
     * - value
     *
     * @When /^I set the fields:$/
     *
     * @param TableNode $fields
     * @return void
     */
    public function iSetFields(TableNode $fields)
    {
        foreach ($fields->getHash() as $row) {
            $this->i_set_field($row['field'], $row['value']);
        }
    }

    /**
     * Gets a form with a specific id on the page.
     *
     * @param string $id The id of the form.
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Exception
     */
    protected function getForm(string $id): NodeElement
    {
        // Find the form.
        $form = $this->find('css', "form#$id");
        if (is_null($form)) {
            throw new \Exception("The form $id could not be found");
        }
        return $form;
    }

    /**
     * @param \Behat\Mink\Element\NodeElement $form
     * @param string $field
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Exception
     */
    protected function getFormField(NodeElement $form, string $field): NodeElement
    {
        $element = $form->find('named', ['field', $field]);
        if (is_null($element)) {
            throw new \Exception("The form field $field could not be found");
        }
        return $element;
    }

    /**
     * Checks if a field element is disabled.
     *
     * @param \Behat\Mink\Element\NodeElement $field
     * @return bool
     */
    protected function assertFieldIsDisabled(NodeElement $field): bool
    {
        return $field->hasAttribute('disabled');
    }

    /**
     * Verifies a field in a form is disabled.
     *
     * @Then /^"([^"]*)" is disabled in "([^"]*)" form$/
     *
     * @param string $field The name, id or label of the field
     * @param string $form The id of the form
     * @throws \Exception
     */
    public function isDisabledInForm(string $field, string $form)
    {
        $formelement = $this->getForm($form);
        $element = $this->getFormField($formelement, $field);
        if (!$this->assertFieldIsDisabled($element)) {
            throw new \Exception("The field $field is enabled.");
        }
    }

    /**
     * Verifies a field in a form is enabled.
     *
     * @Then /^"([^"]*)" is enabled in "([^"]*)" form$/
     *
     * @param string $field The name, id or label of the field
     * @param string $form The id of the form
     * @throws \Exception
     */
    public function isEnabledInForm(string $field, string $form): void
    {
        try {
            $this->isDisabledInForm($field, $form);
        } catch (\Exception) {
            // All is good.
            return;
        }
        throw new \Exception("The field $field is disabled.");
    }

    /**
     * Checks a checkbox in a specific form.
     *
     * @When /^I check "([^"]*)" in "([^"]*)" form$/
     *
     * @param string $field The name, id or label of the field
     * @param string $form The id of the form
     * @throws \Exception
     */
    public function iCheckInForm(string $field, string $form)
    {
        $formelement = $this->getForm($form);
        $element = $this->getFormField($formelement, $field);
        if ($this->assertFieldIsDisabled($element)) {
            throw new \Exception("The field $field is disabled.");
        }
        $element->check();
    }

    /**
     * Verifies a checkbox is checked in a specific form.
     *
     * @Then /^"([^"]*)" is checked in "([^"]*)" form$/
     *
     * @param string $field The name, id or label of the field
     * @param string $form The id of the form
     * @throws \Exception
     */
    public function isCheckInForm(string $field, string $form)
    {
        $formelement = $this->getForm($form);
        $element = $this->getFormField($formelement, $field);
        if (!$element->isChecked()) {
            throw new \Exception("The field $field is not checked.");
        }
    }

    /**
     * Verifies a checkbox is unchecked in a specific form.
     *
     * @Then /^"([^"]*)" is unchecked in "([^"]*)" form$/
     *
     * @param string $field The name, id or label of the field
     * @param string $form The id of the form
     * @return void
     * @throws \Exception
     */
    public function isUncheckInForm(string $field, string $form): void
    {
        try {
            $this->isCheckInForm($field, $form);
        } catch (\Exception) {
            // All is good.
            return;
        }
        throw new \Exception("The field $field is checked.");
    }

    /**
     * Unchecks a checkbox in a specific form.
     *
     * @When /^I uncheck "([^"]*)" in "([^"]*)" form$/
     *
     * @param string $field The name, id or label of the field
     * @param string $form The id of the form
     * @throws \Exception
     */
    public function iUncheckInForm(string $field, string $form)
    {
        $formelement = $this->getForm($form);
        $element = $this->getFormField($formelement, $field);
        if ($this->assertFieldIsDisabled($element)) {
            throw new \Exception("The field $field is disabled.");
        }
        $element->uncheck();
    }

    /**
     * Presses the first submit button in a form.
     *
     * @When /^I submit "([^"]*)" form$/
     *
     * @param string $form The id of a form
     * @throws \Exception
     */
    public function iSubmitForm(string $form)
    {
        $formelement = $this->getForm($form);
        $submit = $formelement->find('css', 'input[type=submit]');
        if (is_null($submit)) {
            throw new \Exception('The form has no submit button');
        }
        if ($this->assertFieldIsDisabled($submit)) {
            throw new \Exception('Saving is disabled.');
        }
        $submit->press();
        $this->lookForErrors();
    }

    /**
     * Checks that the fist submit button is disabled.
     *
     * @When /^I cannot submit "([^"]*)" form$/
     *
     * @param string $form The id of a form
     * @throws \Exception
     */
    public function iCannotSubmitForm(string $form)
    {
        $formelement = $this->getForm($form);
        $submit = $formelement->find('css', 'input[type=submit]');
        if (is_null($submit)) {
            throw new \Exception('The form has no submit button');
        }
        if (!$this->assertFieldIsDisabled($submit)) {
            throw new \Exception('Saving is enabled.');
        }
    }
}
