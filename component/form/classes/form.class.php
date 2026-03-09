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

namespace component\form;

use component\Component;
use component\Helper;
use component\tabs\Tab;
use component\tabs\TabList;
use render;

/**
 * Stores a list of all components in the form collection
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Form implements Component
{
    /** Submit a form that is inside a dialog element */
    public const METHOD_DIALOG = 'dialog';

    /** Submit a form via a GET request. */
    public const METHOD_GET = 'get';

    /** Submit a form by a PST request. */
    public const METHOD_POST = 'post';

    /** @var string The encoding to use when files are being sent by the form. */
    public const ENCODE_DATA = 'multipart/form-data';

    /** @var Button[] The buttons at the bottom of the form. */
    protected array $buttons = [];

    /** @var FormElement[] An array of form element components */
    protected array $elements = [];

    /**
     * The constructor.
     *
     * @param string $action The location that form data is sent.
     * @param string $method The method by which the form is submitted, valid values are:
     *                       Form::METHOD_POST, Form::METHOD_GET, Form::METHOD_DIALOG.
     * @param bool $autocomplete If autocompletion is enabled on the form (default false)
     * @param string|null $target Where the response will be displayed (for example: _top)
     * @param string|null $encode The encoding used to send the form (default: null)
     * @param bool $floatingbuttons Flags if the forms buttons should always be visible (default: false)
     */
    public function __construct(
        protected string $action,
        protected string $method = self::METHOD_POST,
        protected bool $autocomplete = false,
        protected ?string $target = null,
        protected ?string $encode = null,
        protected bool $floatingbuttons = false,
    ) {
        // Intentionally blank.
    }

    /**
     * Add a new element to the form.
     *
     * @param FormElement $element
     * @return void
     */
    public function addElement(FormElement $element): void
    {
        if ($element->requiresMultiPartFormData()) {
            $this->encode = self::ENCODE_DATA;
        }

        $this->elements[] = $element;
    }

    /**
     * Adds a button to the bottom of the form.
     *
     * @param Button $button
     * @return void
     */
    public function addButton(Button $button): void
    {
        $this->buttons[] = $button;
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/form.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        $buttons = [];

        foreach ($this->buttons as $button) {
            $buttons[] = $button->getData($renderer);
        }

        $elements = [];

        foreach ($this->elements as $element) {
            $elements[] = $element->getData($renderer);
        }

        return [
            'action' => $this->action,
            'autocomplete' => $this->autocomplete,
            'buttons' => $buttons,
            'elements' => $elements,
            'encode' => $this->encode,
            'floatingbuttons' => $this->floatingbuttons,
            'method' => $this->method,
            'target' => $this->target,
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        $form = new self(
            action: '',
            floatingbuttons: true,
        );

        // Demo a hidden element.
        $form->addElement(Hidden::getExample());

        $tab1 = new Tab(
            id: 'tab1',
            name: 'Inputs',
        );
        $tab2 = new Tab(
            id: 'tab2',
            name: 'Selects and text areas',
        );
        $tab3 = new Tab(
            id: 'tab3',
            name: 'Fieldsets',
        );
        $tab4 = new Tab(
            id: 'tab4',
            name: 'Statics',
        );

        // Demo of the static components.
        $tablist = new TabList(
            id: 'tablist',
            name: 'Examsys forms demo',
            tabs: [
                $tab1,
                $tab2,
                $tab3,
                $tab4,
            ],
            orientation: TabList::ORIENTATION_VERTICAL,
        );

        // This is the start of the tab area.
        $form->addElement(new StaticComponent(
            component: $tablist,
            template: '@tabs/tab_list_start.html',
        ));
        $form->addElement(new StaticComponent(
            component: $tab1,
            template: '@tabs/tab_panel_start.html',
        ));

        // Demo each input.
        $form->addElement(Checkbox::getExample());
        $form->addElement(Color::getExample());
        $form->addElement(Date::getExample());
        $form->addElement(Email::getExample());
        $form->addElement(File::getExample());
        $form->addElement(Number::getExample());
        $form->addElement(Radio::getExample());
        $form->addElement(Range::getExample());
        $form->addElement(Search::getExample());
        $form->addElement(Telephone::getExample());
        $form->addElement(Text::getExample());
        $form->addElement(Time::getExample());
        $form->addElement(URL::getExample());

        // Change of tabs.
        $form->addElement(new StaticComponent(
            component: $tab1,
            template: '@tabs/tab_panel_end.html',
        ));
        $form->addElement(new StaticComponent(
            component: $tab2,
            template: '@tabs/tab_panel_start.html',
        ));

        // Demo the select.
        $form->addElement(Select::getExample());
        $form->addElement(new Select(
            id: 'multi-select',
            name: 'multi-select',
            label: 'Multi select',
            options: [
                'val1' => 'Option 1',
                'val2' => 'Option 2',
                'val3' => 'Option 3',
            ],
            description: 'This element has some help text to give more information about it',
            multiple: true,
            size: 3,
        ));

        // Demo a textarea
        $form->addElement(TextArea::getExample());

        // Change of tabs.
        $form->addElement(new StaticComponent(
            component: $tab2,
            template: '@tabs/tab_panel_end.html',
        ));
        $form->addElement(new StaticComponent(
            component: $tab3,
            template: '@tabs/tab_panel_start.html',
        ));

        // Demo each of the Fieldsets.
        $form->addElement(RadioGroup::getExample());
        $form->addElement(CheckboxGroup::getExample());
        $form->addElement(CheckboxOptGroup::getExample());
        $form->addElement(GeneralGroup::getExample());

        // Change of tabs.
        $form->addElement(new StaticComponent(
            component: $tab3,
            template: '@tabs/tab_panel_end.html',
        ));
        $form->addElement(new StaticComponent(
            component: $tab4,
            template: '@tabs/tab_panel_start.html',
        ));

        // Demo some static elements.
        $form->addElement(StaticHtml::getExample());
        $form->addElement(StaticTemplate::getExample());

        // Close off tabs.
        $form->addElement(new StaticComponent(
            component: $tab4,
            template: '@tabs/tab_panel_end.html',
        ));
        $form->addElement(new StaticComponent(
            component: $tablist,
            template: '@tabs/tab_list_end.html',
        ));

        // Demo each of the button types.
        $form->setStandardButtons('Submit', 'Cancel');
        $form->addButton(new Reset(name: 'reset', value: 'Reset'));
        return $form;
    }

    #[\Override]
    public function getJavascriptForHead(): array
    {
        // Include JavaScript for all child elements.
        $js = [];
        foreach ($this->elements as $element) {
            $js[] = $element->getJavascriptForHead();
        }
        return Helper::combineJS([], ...$js);
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        // Include JavaScript for all child elements.
        $js = [];
        foreach ($this->elements as $element) {
            $js[] = $element->getJavascriptForFooter();
        }
        return Helper::combineJS([], ...$js);
    }

    #[\Override]
    public function getStrings(): array
    {
        // Include the strings for all the child elements.
        return Helper::combineLang([], ...$this->elements);
    }

    /**
     * Gives the form a standard submit and cancel button.
     *
     * It will overwrite any existing buttons.
     *
     * @param string $submit The string for the submit button
     * @param string $cancel The string for the cancel button
     * @return void
     */
    public function setStandardButtons(string $submit, string $cancel): void
    {
        $this->buttons = [
            // Submit button.
            new Submit(
                name: 'submit',
                value: $submit,
            ),
            // Cancel button.
            new Button(
                name: 'cancel',
                value: $cancel
            ),
        ];
    }
}
