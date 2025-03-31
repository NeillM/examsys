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
 *
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class IE_qti_Load extends IE_Main
{
    public $params;

    /** @var string Debugging information. */
    public $debug;

    public function Load($params)
    {
        global $string;

        echo "<h4>{$string['params']}</h4>";
        print_p($params);

        global $import_directory;

        $xml_files = [];

        //print_p($params);
        $this->params = $params;
        $import_directory = $params->base_dir . $params->dir . '/';

        $filename = $params->sourcefile;

        $ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext == 'xml') {
            $xml_files[basename($filename)] = $filename;
        } elseif ($ext == 'zip') {
            echo 'Extracting zip<br />';
            $zip = new ZipArchive();
            $res = $zip->open($filename);
            if ($res === true) {
                $zip->extractTo($params->base_dir . $params->dir . '/');
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    $filename = $stat['name'];
                    $ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if ($ext == 'xml') {
                        $xml_files[$filename] = $params->base_dir . $params->dir . '/' . $filename;
                    }
                }
                $zip->close();
            } else {
                print 'zip invalid ';
                $ErrMsg = match ($res) {
                    ZipArchive::ER_EXISTS => 'File already exists.',
                    ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
                    ZipArchive::ER_MEMORY => 'Malloc failure.',
                    ZipArchive::ER_NOENT => 'No such file.',
                    ZipArchive::ER_NOZIP => 'Not a zip archive.',
                    ZipArchive::ER_OPEN => "Can't open file.",
                    ZipArchive::ER_READ => 'Read error.',
                    ZipArchive::ER_SEEK => 'Seek error.',
                    default => "Unknown (Code $rOpen)",
                };
                print 'Zip Error Message: ' . $ErrMsg . "\r\n";
                $this->AddError($string['invalidzip']);
                return null;
            }
        }

        $files['qti12'] = []; // qti 1.2 files, each unrelated to the rest
        $files['manifest'] = []; // manifest files
        $files['item'] = []; // qti 2 questions
        $files['paper'] = []; // qti 2 test files

        foreach ($xml_files as $filename => $fullpath) {
            $type = $this->DetectFileType($fullpath);
            $files[$type][$filename] = $fullpath;
        }

        if (count($files['qti12']) == 0) {
            $this->AddError($string['noqtiinzip']);
            return null;
        }

        $result = new stdClass();
        $result->questions = [];

        // process qti 1.2 files
        foreach ($files['qti12'] as $filename => $fullpath) {
            $qti12 = new IE_QTI12_Load();

            $params->sourcefile = $fullpath;
            $ob = new OB();
            $ob->ClearAndSave();
            $output = $qti12->Load($params);
            $this->debug .= $ob->GetContent();
            $ob->Restore();
            foreach ($qti12->warnings as $qid => $warnings) {
                foreach ($warnings as $warn) {
                    $this->warnings[$qid][] = $warn;
                }
            }

            foreach ($qti12->errors as $qid => $errors) {
                foreach ($errors as $error) {
                    $this->errors[$qid][] = $error;
                }
            }

            echo "<h4>{$string['fileoutput']}: $filename</h4>";
            echo $this->debug;

            foreach ($output->questions as $id => $question) {
                $result->questions[$id] = $question;
            }

            if (!empty($output->papers)) {
                foreach ($output->papers as $id => $paper) {
                    $result->papers[$id] = $paper;
                }
            }
        }

        return $result;
    }

    public function DetectFileType($filename)
    {
        global $string;

        $xmlStr = file_get_contents($filename);
        $xml = simplexml_load_string($xmlStr);

        if (!$xml) {
            $this->AddError(sprintf($string['invalidxml'], basename($filename)));
            return '';
        }

        $basenode = mb_strtolower($xml->getName());
        if ($basenode == 'questestinterop') {
            return 'qti12';
        }

        if ($basenode == 'assessmentitem') {
            return 'item';
        }

        if ($basenode == 'manifest') {
            return 'manifest';
        }

        if ($basenode == 'assessmenttest') {
            return 'paper';
        }

        echo $basenode . '<br />';
        return '';
    }
}
