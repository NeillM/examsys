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
use render;

/**
 * A fieldset containing only check boxes in option groups.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class CheckboxOptGroup extends Fieldset
{
    /** @var array List of opt groups. */
    protected array $groups = [];

    /** @var Checkbox[][] An array of checkboxes for each group. */
    protected array $groupoptions = [];

    /**
     * The constructor.
     *
     * @param string $id The unique id of the form element
     * @param string $name The name of the form element
     * @param string $label The label for the fieldset
     * @param array $classes Classes to be added to the fieldset (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param string $default The default value of the fieldset (optional)
     * @param array $groupclasses Classes to be added to each group (optional)
     * @param string $orientation The orientation of fields that are in the fieldset (default: Fieldset::ORIENTATION_VERTICAL)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        array $classes = [],
        string $description = '',
        string $default = '',
        protected array $groupclasses = [],
        string $orientation = self::ORIENTATION_VERTICAL,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            label: $label,
            classes: array_merge(['checkbox-opt-group'], $classes),
            description: $description,
            default: $default,
            orientation: $orientation,
        );
    }

    /**
     * Adds an opt group to the field.
     *
     * @param string $id The identifier of an opt group
     * @param string $label The localised label for the opt group
     * @return void
     */
    public function addGroup(
        string $id,
        string $label,
    ): void {
        if (isset($this->groups[$id])) {
            throw new \coding_exception("optgroup '$id' already has a exists");
        }

        $this->groups[$id] = $label;
        $this->groupoptions[$id] = [];
    }

    /**
     * Adds a new checkbox to the fieldset.
     *
     * @param string $group The identifier for an option group.
     * @param string $value The value to be sent when the option is selected
     * @param string $label The localised label for the option
     * @param string $description The localised help text for the option (optional)
     * @param bool $disabled Flag if the checkbox is disabled.
     * @param bool $checked Flag if the checkbox is checked.
     * @return void
     */
    public function addOption(
        string $group,
        string $value,
        string $label,
        string $description = '',
        bool $disabled = false,
        bool $checked = false,
    ): void {
        if (!isset($this->groups[$group])) {
            throw new \coding_exception("optgroup '$group' must exist before you can add an option to it.");
        }

        $option = new Checkbox(
            id: $this->id . '-' . $value,
            name: $this->name,
            label: $label,
            value: $value,
            checked: $checked,
            disabled: $disabled,
            description: $description,
        );
        $this->groupoptions[$group][] = $option;
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/fieldsetoptgroups.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        $groups = [];

        foreach ($this->groups as $id => $group) {
            $groups[$id] = [
                'id' => $id,
                'label' => $group,
                'options' => [],
            ];
            foreach ($this->groupoptions[$id] as $option) {
                $groups[$id]['options'][] = $option->getData($renderer);
            }
        }

        return array_merge(
            parent::getData($renderer),
            [
                'groupclasses' => $this->groupclasses,
                'groups' => $groups,
            ]
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        $example = new self(
            id: 'checkbox-opt-group',
            name: 'checkbox-opt-group',
            label: 'Checkbox option group',
            default: 'opt3',
        );
        $example->addGroup('g1', 'Group 1');
        $example->addGroup('g2', 'Group 2');
        $example->addGroup('g3', 'Group 3');
        $example->addOption('g1', 'opt1', 'Option 1');
        $example->addOption('g1', 'opt2', 'Option 2');
        $example->addOption('g2', 'opt3', 'Option 3');
        $example->addOption('g3', 'opt4', 'Option 4');
        return $example;
    }
}
