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

use testing\unittest\UnitTest;

/**
 * Test hotspot marking.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 *
 * @group html5
 * @group hotspot
 */
class html5hotspottest extends UnitTest
{
    /**
     * Test that correct answers have their coordinates removed by the correct_to_answer_mode method.
     *
     * This tests multiple shapes with 1 shape per layers.
     */
    public function test_correct_to_answer_mode1()
    {
        // Three layers are defined, the first contains an ellipse, the second a rectangle, the third a polygon.
        $input = 'Deer~16776960~ellipse~384,335,51b,3c3~0~|birds~45136~rectangle~fa,51,1db,121~0~|AT-AT~12582912~polygon~15d,154,167,150,18d,144,220,11e,2a1,13e,2a5,153,2e7,183~0~';
        $expected = 'Deer~16776960~|birds~45136~|AT-AT~12582912~';
        $result = hotspot_helper::get_instance()->correct_to_answer_mode($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that correct answers have their coordinates removed by the correct_to_answer_mode method.
     *
     * This tests multiple shapes with 3 shapes in one layer.
     */
    public function test_correct_to_answer_mode2()
    {
        // Three layers are defined, the first contains an ellipse, the second a rectangle, the third a polygon.
        $input = 'Deer~16776960~ellipse~384,335,51b,3c3~0~rectangle~fa,51,1db,121~1~polygon~15d,154,167,150,18d,144,220,11e,2a1,13e,2a5,153,2e7,183~2~';
        $expected = 'Deer~16776960~';
        $result = hotspot_helper::get_instance()->correct_to_answer_mode($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the marking result is removed from a stored user answer.
     */
    public function test_answer_strip_correct_information()
    {
        // Two layers, the first layer the user has answered correctly, the second layer the user has answered incorrectly.
        $input = '1,300,50|0,24,80';
        $expected = '300,50|24,80';
        $result = hotspot_helper::get_instance()->answer_strip_correct_information($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point inside an ellipse is marked as correct.
     */
    public function test_mark_ellipse_correct()
    {
        // The bounding box coordinates are encoded as hexadecimal.
        $correct = 'Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '1115,891';
        $expected = '1,1115,891';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point outside an ellipse is marked as incorrect.
     */
    public function test_mark_ellipse_incorrect()
    {
        // The bounding box coordinates are encoded as hexadecimal.
        $correct = 'Deer~16776960~ellipse~384,335,51b,3c3~0~';
        // One pixel inside the bounding rectangle.
        $answer = '901,821';
        $expected = '0,901,821';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that if an ellipse is one dimensional it is marked as incorrect.
     */
    public function test_mark_ellipse_one_dimensional_incorrect1()
    {
        // The bounding box coordinates are encoded as hexadecimal.
        $correct = 'Deer~16776960~ellipse~1,1,1,9~0~';
        // One pixel inside the bounding rectangle.
        $answer = '1,5';
        $expected = '0,1,5';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that if an ellipse is one dimensional it is marked as incorrect.
     */
    public function test_mark_ellipse_one_dimensional_incorrect2()
    {
        // The bounding box coordinates are encoded as hexadecimal.
        $correct = 'Deer~16776960~ellipse~1,1,9,1~0~';
        // One pixel inside the bounding rectangle.
        $answer = '5,1';
        $expected = '0,5,1';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point inside an rectangle is marked as correct.
     */
    public function test_mark_rectangle_correct()
    {
        // The bounding box coordinates are encoded as hexadecimal.
        $correct = 'birds~45136~rectangle~fa,51,1db,121~0~';
        // Top corner.
        $answer = '250,81';
        $expected = '1,250,81';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point outside an rectangle is marked as incorrect.
     */
    public function test_mark_rectangle_incorrect()
    {
        // The bounding box coordinates are encoded as hexadecimal.
        $correct = 'birds~45136~rectangle~fa,51,1db,121~0~';
        // One pixel outside the top corner.
        $answer = '249,80';
        $expected = '0,249,80';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point inside an polygon is marked as correct.
     */
    public function test_mark_polygon_correct()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~';
        // Top corner.
        $answer = '250,81';
        $expected = '1,250,81';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that answers that intersect the polygon line count as inside.
     *
     * @param string $answer A user's answer to the question
     * @param string $expected The expected marked value
     *
     * @dataProvider dataMarkPolygonCorrect2
     */
    public function test_mark_polygon_correct2(string $answer, string $expected)
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'birds~45136~polygon~1,1,1,9,9,9,9,1~0~';

        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * User answers and the expected marking.
     *
     * @return array
     */
    public function dataMarkPolygonCorrect2(): array
    {
        return [
            // Test just in or out of the shape.
            ['0.9,0.9', '0,0.9,0.9'],
            ['9.1,9.1', '0,9.1,9.1'],
            ['1.1,1.1', '1,1.1,1.1'],
            ['8.9,8.9', '1,8.9,8.9'],
            // Test it matches the vertices.
            ['1,1', '1,1,1'],
            ['9,9', '1,9,9'],
            ['1,9', '1,1,9'],
            ['9,1', '1,9,1'],
            // Test it matches on the edges.
            ['1,5', '1,1,5'],
            ['1,10', '0,1,10'],
            ['1,0', '0,1,0'],
            ['9,8', '1,9,8'],
            ['9,0', '0,9,0'],
            ['9,10', '0,9,10'],
            ['9,5', '1,9,5'],
            ['0,9', '0,0,9'],
            ['10,9', '0,10,9'],
            ['5,1', '1,5,1'],
            ['0,1', '0,0,1'],
            ['10,1', '0,10,1'],
        ];
    }

    /**
     * Test that a point outside an polygon is marked as incorrect.
     */
    public function test_mark_polygon_incorrect()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~';
        // One pixel outside the top corner.
        $answer = '249,80';
        $expected = '0,249,80';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * A one dimensional polygon.
     *
     * This test can fail if the edge detection is wrong.
     */
    public function test_mark_1d_polygon_incorrect()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'Question~16711680~polygon~e4,3f,e4,3f~2~';
        // The point is not in the shape.
        $answer = '163,134';
        $expected = '0,163,134';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * A one dimensional polygon.
     *
     * This test can fail if the edge detection is wrong.
     */
    public function test_mark_1d_polygon_correct()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'Question~16711680~polygon~e4,3f,e4,3f~2~';
        // The point is on the line.
        $answer = '228,63';
        $expected = '1,228,63';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that when there are multiple layers that each layer is marked correctly.
     */
    public function test_mark_multiple_layers()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '250,81|901,821';
        $expected = '1,250,81|0,901,821';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test when the user answer is in the first of multiple shapes.
     */
    public function test_mark_multiple_shapes1()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '250,81';
        $expected = '1,250,81';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test when the user answer is in the last of multiple shapes.
     */
    public function test_mark_multiple_shapes2()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '1115,891';
        $expected = '1,1115,891';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test when the user answer is not in any of multiple shapes.
     */
    public function test_mark_multiple_shapes3()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '901,821';
        $expected = '0,901,821';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * No answer for a one layer question.
     */
    public function test_mark_unaswered1()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '';
        $expected = 'u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * This is a possible remarking scenario, or not attempted at all.
     */
    public function test_mark_unaswered2()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '';
        $expected = 'u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * No answer, but some interaction.
     */
    public function test_mark_unaswered3()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '|';
        $expected = 'u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Partially answered.
     */
    public function test_mark_partial_unaswered()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '250,81|';
        $expected = '1,250,81|u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * PTests that if we get an unanswered layer that is saved in an old format.
     */
    public function test_mark_partial_unaswered_oldstyle()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '1,250,81|0,false,false';
        $expected = '1,250,81|u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Letter answered layers with all correct
     */
    public function testMarkCorrectLetters()
    {
        $correct = 'Top left square~16711680~rectangle~10,11,74,65~0~|Top circle~16776960~ellipse~11b,17,17d,71~0~|Bottom square overlaid with diamond~45136~rectangle~47,ac,9a,f9~0~|Bottom mid - circle top left~28864~ellipse~e2,9e,12a,e6~0~|Bottom mid - circle bottom~7352480~ellipse~143,108,fe,c4~0~|Bottom right - circle~12582912~ellipse~181,c8,1cc,112~0~';
        $answer = '1,71,55|1,331,75|1,78,178|1,260,187|1,293,249|1,412,256';
        $expected = '1,A|1,B|1,C|1,D|1,E|1,F';
        $result = hotspot_helper::get_instance()->markWithLetters($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Letter answered layers with all correct, some overlaps with other correct layers
     * C overlays I, D overlays E and J, E overlays D and J, F overlays K and L
     * I, J, K and L are part of incorrect marking
     */
    public function testMarkCorrectLettersOverlapping()
    {
        $correct = 'Top left square~16711680~rectangle~10,11,74,65~0~|Top circle~16776960~ellipse~11b,17,17d,71~0~|Bottom square overlaid with diamond~45136~rectangle~47,ac,9a,f9~0~|Bottom mid - circle top left~28864~ellipse~e2,9e,12a,e6~0~|Bottom mid - circle bottom~7352480~ellipse~143,108,fe,c4~0~|Bottom right - circle~12582912~ellipse~181,c8,1cc,112~0~';
        $incorrect = '~16760832~rectangle~8d,17,f4,68~0~|~9621584~polygon~1c7,18,200,4c,1c7,7e,191,4c~0~|~45296~polygon~71,99,ad,d2,72,10c,34,d2~0~|~8288~ellipse~117,e9,162,9e~0~|~10206041~rectangle~185,a0,1c9,df~0~|~2050429~polygon~1f6,e1,1c9,109,19e,e0,1cb,bb~0~';
        $answer = '1,70,67|1,328,84|1,109,211|1,288,204|1,289,208|1,437,215';
        $expected = '1,A|1,B|1,C|1,D|1,E|1,F';
        $result = hotspot_helper::get_instance()->markWithLetters($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Letter answered layers with all wrong, no incorrect overlaps, E not matching any layers
     * A=>C; B=>D,E; C=>F, D=>A, E=>x, F=>B
     */
    public function testMarkWrongLettersOverlapping()
    {
        $correct = 'Top left square~16711680~rectangle~10,11,74,65~0~|Top circle~16776960~ellipse~11b,17,17d,71~0~|Bottom square overlaid with diamond~45136~rectangle~47,ac,9a,f9~0~|Bottom mid - circle top left~28864~ellipse~e2,9e,12a,e6~0~|Bottom mid - circle bottom~7352480~ellipse~143,108,fe,c4~0~|Bottom right - circle~12582912~ellipse~181,c8,1cc,112~0~';
        $answer = '0,114,212|0,292,205|0,439,216|0,70,64|0,187,69|0,340,68';
        $expected = '0,C|0,D,E|0,F|0,A|0,x|0,B';
        $result = hotspot_helper::get_instance()->markWithLetters($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Letter answered layers with all wrong
     * A=>C,I; B=>D,E,J; C=>F,K,L, D=>A, E=>G, F=>B
     * I, J, K and L are part of incorrect marking
     */
    public function testMarkIncorrectLettersOverlapping()
    {
        $correct = 'Top left square~16711680~rectangle~10,11,74,65~0~|Top circle~16776960~ellipse~11b,17,17d,71~0~|Bottom square overlaid with diamond~45136~rectangle~47,ac,9a,f9~0~|Bottom mid - circle top left~28864~ellipse~e2,9e,12a,e6~0~|Bottom mid - circle bottom~7352480~ellipse~143,108,fe,c4~0~|Bottom right - circle~12582912~ellipse~181,c8,1cc,112~0~';
        $incorrect = '~16760832~rectangle~8d,17,f4,68~0~|~9621584~polygon~1c7,18,200,4c,1c7,7e,191,4c~0~|~45296~polygon~71,99,ad,d2,72,10c,34,d2~0~|~8288~ellipse~117,e9,162,9e~0~|~10206041~rectangle~185,a0,1c9,df~0~|~2050429~polygon~1f6,e1,1c9,109,19e,e0,1cb,bb~0~';
        $answer = '0,114,212|0,292,205|0,439,216|0,70,64|0,187,69|0,340,68';
        $expected = '0,C,I|0,D,E,J|0,F,K,L|0,A|0,G|0,B';
        $result = hotspot_helper::get_instance()->markWithLetters($answer, $correct, $incorrect);
        $this->assertEquals($expected, $result);
    }

    /**
     * Answer incorrect, overlays 3 other layers. Includes 3 unanswered layers, and one layer added post-exam sitting
     */
    public function testMarkIncorrectThreeLayers()
    {
        $correct = 'Top left square~16711680~rectangle~10,11,74,65~0~|Bottom mid - circle top left~28864~ellipse~e2,9e,12a,e6~0~|Bottom mid - circle bottom~7352480~ellipse~143,108,fe,c4~0~|Bottom mid - circle right~8288~ellipse~117,e9,162,9e~0~|Bottom right - circle~12582912~ellipse~181,c8,1cc,112~0~';
        $answer = '0,292,205|u|u|u';
        $expected = '0,B,C,D|0,u|0,u|0,u|0,?';
        $result = hotspot_helper::get_instance()->markWithLetters($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Answer incorrect, overlays 3 other layers, one of which is incorrect. Rest unanswered.
     */
    public function testMarkIncorrectLayerThreeLayers()
    {
        $correct = 'Top left square~16711680~rectangle~10,11,74,65~0~|Bottom mid - circle top left~28864~ellipse~e2,9e,12a,e6~0~|Bottom mid - circle bottom~7352480~ellipse~143,108,fe,c4~0~';
        $incorrect = '~8288~ellipse~117,e9,162,9e~0~';
        $answer = '0,292,205|u|u';
        $expected = '0,B,C,D|0,u|0,u';
        $result = hotspot_helper::get_instance()->markWithLetters($answer, $correct, $incorrect);
        $this->assertEquals($expected, $result);
    }
}
