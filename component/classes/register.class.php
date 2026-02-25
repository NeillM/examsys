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

namespace component;

/**
 * Stores a list of all collections
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Register
{
    /**
     * List of collections in ExamSys
     *
     * We hard code a list of collections to avoid the time that trying
     * to dynamically load them by scanning the directory would take.
     *
     * @var string[]
     */
    protected static array $collections = [
        'breadcrumb',
        'form',
        'tabs',
    ];

    /**
     * Gets a list of components template directories
     *
     * The array keys the paths to the template directories by the
     *
     * @return array
     */
    public static function getTemplateList(): array
    {
        // This method could be called many times on each page load,
        // the directory structure should not change during a page load.
        static $collections;

        if ($collections === null) {
            $collections = [];

            foreach (self::$collections as $collection) {
                $template_directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . $collection . DIRECTORY_SEPARATOR . 'templates';
                if (is_dir($template_directory)) {
                    $collections[$collection] = $template_directory;
                }
            }
        }

        return $collections;
    }

    /**
     * Gets the list of collections in ExamSys.
     *
     * @return string[]
     */
    public static function getCollectionList(): array
    {
        return self::$collections;
    }

    /**
     * Get a list of collections in a component.
     *
     * @param string $collection The name of the collection
     * @return array
     */
    public static function getComponentList(string $collection): array
    {
        $registerclass = "\\component\\{$collection}\\Register";
        if (!class_exists($registerclass) && in_array(ComponentRegister::class, class_implements($registerclass))) {
            // Without a register we cannot get the components.
            return [];
        }

        return $registerclass::getComponentList();
    }

    /**
     * Tests if the collection is valid.
     *
     * @param string $name
     * @return bool
     */
    public static function isCollection(string $name): bool
    {
        return in_array($name, self::$collections);
    }

    /**
     * Tests if a component exists.
     *
     * @param string $collection
     * @param string $component
     * @return bool
     */
    public static function isComponent(string $collection, string $component): bool
    {
        $classname = self::getComponentClassName($collection, $component);
        return class_exists($classname) && in_array(Component::class, class_implements($classname));
    }

    /**
     * Gets the fully qualified name of a component class.
     *
     * It does not validate that it exists.
     *
     * @param string $collection
     * @param string $component
     * @return string
     */
    public static function getComponentClassName(string $collection, string $component): string
    {
        return "\\component\\{$collection}\\{$component}";
    }
}
