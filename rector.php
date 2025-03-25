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

/**
 * Configures the Rector checks.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/LTI',
        __DIR__ . '/admin',
        __DIR__ . '/ajax',
        __DIR__ . '/api',
        __DIR__ . '/classes',
        __DIR__ . '/cli',
        __DIR__ . '/cosign',
        __DIR__ . '/credits',
        __DIR__ . '/csv',
        __DIR__ . '/delete',
        __DIR__ . '/demo',
        __DIR__ . '/export',
        __DIR__ . '/folder',
        __DIR__ . '/help',
        __DIR__ . '/import',
        __DIR__ . '/include',
        __DIR__ . '/install',
        __DIR__ . '/invigilator',
        __DIR__ . '/js',
        __DIR__ . '/lang',
        __DIR__ . '/maintenance',
        __DIR__ . '/mapping',
        __DIR__ . '/module',
        __DIR__ . '/osce',
        __DIR__ . '/paper',
        __DIR__ . '/peer_review',
        __DIR__ . '/plugins',
        __DIR__ . '/qti',
        __DIR__ . '/question',
        __DIR__ . '/reports',
        __DIR__ . '/requirements',
        __DIR__ . '/reviews',
        __DIR__ . '/statistics',
        __DIR__ . '/std_setting',
        __DIR__ . '/students',
        __DIR__ . '/testing',
        __DIR__ . '/tools',
        __DIR__ . '/updates',
        __DIR__ . '/users',
        __DIR__ . '/webServices',
    ])
    ->withRootFiles()
    ->withFileExtensions(['php', 'inc'])
    ->withPhpSets(php71: true)
    ->withSkip([
        // We cannot run this rule because it breaks ExamSys.
        // We have several functions with the same name, but using different numbers of parameters.
        // When this runs it only seems to count the numbers of parameters in one of these methods,
        // this means that it deleted parameters from some function calls that need them.
        RemoveExtraParametersRector::class,
    ]);
