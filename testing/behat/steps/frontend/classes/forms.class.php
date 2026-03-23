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
use Behat\Mink\Exception\ExpectationException;
use DateTime;
use testing\behat\LocaleFormat;

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
     * Gets a field element.
     *
     * When there are multiple fields using the same name on a page we use >
     * to separate the names of fieldssets that contain the field we are looking for.
     *
     * For example: Available from > Date
     * would be for the Date field inside the Available from fieldset.
     *
     * @param string $anme The name or fieldset path of the field
     * @return NodeElement
     */
    protected function getField(string $name): nodeElement
    {
        $path = explode('>', $name);

        // This is the name field we want to field.
        $field = trim(array_pop($path));

        $fieldsetcount = count($path);

        if ($fieldsetcount === 0) {
            // There were no fieldsets defined.
            $element = $this->find('field', $field);

            if (is_null($element)) {
                $element = $this->find('fieldset', $field);
            }

            if (is_null($element)) {
                $message = "Could not find '$name'";
                throw new ExpectationException($message, $this->getSession());
            }

            return $element;
        }

        // Get the first element of the path.
        $fieldsetname = trim(array_shift($path));
        $fieldset = $this->find('fieldset', $fieldsetname);

        if (is_null($fieldset)) {
            // The first fieldset could not be found.
            $missing = $fieldsetname;
            $message = "Could not find '$missing' in '$name'";
            throw new ExpectationException($message, $this->getSession());
        }

        foreach ($path as $key => $partname) {
            // We are looking for nested fieldsets
            $fieldset = $fieldset->find('named', ['fieldset', trim($partname)]);

            if (is_null($fieldset)) {
                // The fieldset could not be found.
                $missing = trim($partname);
                $message = "Could not find '$missing' in '$name'";
                throw new ExpectationException($message, $this->getSession());
            }
        }

        $element = $fieldset->find('named', ['field', $field]);

        if (is_null($element)) {
            $element = $fieldset->find('named', ['fieldset', $field]);
        }

        if (is_null($element)) {
            $message = "Could not find '$name'";
            throw new ExpectationException($message, $this->getSession());
        }

        return $element;
    }

    /**
     * Gets a form field.
     *
     * Returns an array made up of two values:
     * - NodeElement: The element that we are trying to find
     * - mixed: The actual form value
     *
     * @param string $field The name of a form field.
     * @param string $value The value of a field we want to translate to it's value
     * @return array
     */
    protected function getFieldAndValue($field, $value, bool $set = false): array
    {
        $element = $this->getField($field);

        if (is_null($element)) {
            throw new \Exception("The form field '$field' could not be found");
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
            case 'input':
                switch ($element->getAttribute('type')) {
                    case 'checkbox':
                        // Get the value the form will send, this is the value we need to set the form to be.
                        $elementvalue = $element->getAttribute('value');

                        // The value matches the one we that the form will send, we want to treat that as checked.
                        $match = $elementvalue === $value;

                        // The value parses as a boolean from a form, so we would want to treat it as checked.
                        $boolean = \param::clean($value, \param::BOOLEAN);

                        // The value should be null if the checkbox is unchecked.
                        $formvalue = ($match or $boolean) ? $elementvalue : null;
                        break;
                    case 'date':
                        // We want to allow relative date formats.
                        $date = new DateTime($value);

                        // When setting a date we need to input it using the format of the locale
                        // retrieval is always in the yyyy-mm-dd format.
                        if ($set) {
                            $locale = $this->getBrowserLocale();
                            $formvalue = $date->format(LocaleFormat::getFormat($locale, LocaleFormat::FORMAT_DATE));
                        } else {
                            $formvalue = $date->format('Y-m-d');
                        }
                        break;
                    case 'time':
                        // We want to allow relative time formats.
                        $date = new DateTime($value);

                        // When setting a time we need to input it using the format of the locale
                        // retrieval is always in the 24 hour clock format.
                        if ($set) {
                            $locale = $this->getBrowserLocale();
                            $formvalue = $date->format(LocaleFormat::getFormat($locale, LocaleFormat::FORMAT_TIME));
                        } else {
                            $formvalue = $date->format('H:i');
                        }
                        break;
                }
                break;
        }

        return [$element, $formvalue];
    }

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
        /** @var NodeElement $element */
        [$element, $formvalue] = $this->getFieldAndValue($field, $value, true);

        $element->setValue($formvalue);
    }

    /**
     * Tests that a single field is set to a value.
     *
     * @Then /^I should see the field "([^"]*)" is "([^"]*)"$/
     *
     * @param string $field The name, id or label of the field
     * @param string $value The value the field should be set to
     */
    public function iSeeField($field, $value): void
    {
        [$element, $formvalue] = $this->getFieldAndValue($field, $value);

        $actualvalue = $element->getValue();

        if ($actualvalue !== $formvalue) {
            throw new \Exception("'$field' has a value of '$actualvalue', rather then the expected '$formvalue'");
        }
    }

    /**
     * Tests that a single field is not in the form.
     *
     * @Then /^I should not see the field "([^"]*)"$/
     *
     * @param string $field The name, id or label of the field
     */
    public function iNotSeeField($field): void
    {
        try {
            $this->getField($field);
            throw new \Exception("'$field' was found in the form.");
        } catch (\Exception) {
            // We could not find the field.
        }
    }

    /**
     * Tests that a single field cannot be modified.
     *
     * @Then /^I cannot change "([^"]*)" field$/
     *
     * @param string $field The name, id or label of the field
     */
    public function iCannotEditField($field): void
    {
        $element = $this->getField($field);

        if (!$element->isVisible()) {
            throw new \Exception("'$field' is not visible");
        }

        $enabled = !$element->hasAttribute('disabled');
        $writable = !$element->hasAttribute('readonly');

        if ($enabled && $writable) {
            throw new \Exception("'$field' may be changed");
        }
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
     * Checks that multiple fields in a form have an expected value.
     *
     * Requires the following values:
     * - field
     * - value
     *
     * @When /^I should see the following fields:$/
     *
     * @param TableNode $fields
     */
    public function iSeeFields(TableNode $fields)
    {
        foreach ($fields->getHash() as $row) {
            $this->iSeeField($row['field'], $row['value']);
        }
    }

    /**
     * Checks that multiple fields are not present in the form.
     *
     * Requires the following values:
     * - field
     *
     * @When /^I should not see the following fields:$/
     *
     * @param TableNode $fields
     */
    public function iNotSeeFields(TableNode $fields)
    {
        foreach ($fields->getHash() as $row) {
            $this->iNotSeeField($row['field']);
        }
    }

    /**
     * Checks that multiple fields in a form cannot be edited
     *
     * Requires the following values:
     * - field
     *
     * @When /^I cannot change fields:$/
     *
     * @param TableNode $fields
     */
    public function iCannotEditFields(TableNode $fields)
    {
        foreach ($fields->getHash() as $row) {
            $this->iCannotEditField($row['field']);
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
