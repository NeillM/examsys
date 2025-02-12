<?php
require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

// Get variables from paper_options.php
$paperID = $_GET['paperID'] ?? '';
$module = $_GET['module'] ?? '';
$folder = $_GET['folder'] ?? '';

// Get paper properties
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $string['copypaper']; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/source/copy_paper.css">
</head>
<body>
    <div id="content">
        <h1><?php echo $string['copypaper']; ?></h1>
        
        <?php
        if ($configObject->get_setting('core', 'cfg_summative_mgmt')) {
            echo '<form method="post" action="copy.php" id="checkcopy" class="copy-form" autocomplete="off">';
        } else {
            echo '<form method="post" action="copy.php" class="copy-form" autocomplete="off">';
        }
        ?>
        
        <div class="form-row">
            <label for="new_paper"><?php echo $string['copyname']; ?></label>
            <input type="text" 
                   id="new_paper" 
                   name="new_paper" 
                   maxlength="255" 
                   value="<?php echo $properties->get_paper_title(); ?>" 
                   required />
        </div>

        <div class="form-row">
            <label for="paper_type"><?php echo $string['type']; ?></label>
            <select name="paper_type" id="paper_type" class="changepapertype">
                <?php
                $currentType = $properties->get_paper_type();
                
                // Show paper types based on current type
                switch ($currentType) {
                    case \assessment::TYPE_FORMATIVE:
                    case \assessment::TYPE_PROGRESS:
                    case \assessment::TYPE_SUMMATIVE:
                        echo '<option value="' . \assessment::TYPE_FORMATIVE . '"' . 
                             ($currentType == \assessment::TYPE_FORMATIVE ? ' selected' : '') . 
                             '>' . $string['formative self-assessment'] . '</option>';
                        echo '<option value="' . \assessment::TYPE_PROGRESS . '"' . 
                             ($currentType == \assessment::TYPE_PROGRESS ? ' selected' : '') . 
                             '>' . $string['progress test'] . '</option>';
                        echo '<option value="' . \assessment::TYPE_SUMMATIVE . '"' . 
                             ($currentType == \assessment::TYPE_SUMMATIVE ? ' selected' : '') . 
                             '>' . $string['summative exam'] . '</option>';
                        break;
                    case \assessment::TYPE_SURVEY:
                        echo '<option value="' . \assessment::TYPE_SURVEY . '" selected>' . 
                             $string['survey'] . '</option>';
                        break;
                    case \assessment::TYPE_OSCE:
                        echo '<option value="' . \assessment::TYPE_OSCE . '" selected>' . 
                             $string['osce station'] . '</option>';
                        break;
                }
                ?>
            </select>
        </div>

        <div class="form-row">
            <label></label>
            <input type="checkbox" name="copy_std_setting" id="copy_std_setting" 
                   <?php echo $configObject->get_setting('core', 'stdset_copy_std_setting') ? 'checked' : ''; ?> />
            <label for="copy_std_setting" class="checkbox-label"><?php echo $string['copystdsetting']; ?></label>
        </div>

        <div class="form-row" id="copytype_selector">
            <label><?php echo $string['copytype']; ?></label>
            <div class="radio-group">
                <div class="radio-option">
                    <input type="radio" name="copytype" id="copytype_paperonly" value="paperonly" />
                    <label for="copytype_paperonly">
                        <span><?php echo $string['paperonly']; ?></span>
                        <img src="../artwork/copy_paper_only.png" width="120" height="130" alt="" />
                    </label>
                </div>
                <div class="radio-option">
                    <input type="radio" name="copytype" id="copytype_paperquestions" value="paperquestions" checked />
                    <label for="copytype_paperquestions">
                        <span><?php echo $string['paperandquestions']; ?></span>
                        <img src="../artwork/copy_paper_questions.png" width="120" height="130" alt="" />
                    </label>
                </div>
            </div>
        </div>

        <input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
        <input type="hidden" name="module" value="<?php echo $module; ?>" />
        <input type="hidden" name="folder" value="<?php echo $folder; ?>" />

        <div class="button-group">
            <?php if ($properties->get_paper_type() == \assessment::TYPE_SUMMATIVE && 
                      $configObject->get_setting('core', 'cfg_summative_mgmt')) : ?>
                <input type="button" class="cancel" value="<?php echo $string['cancel']; ?>" name="cancel" id="cancel" />
                <input type="button" class="ok" value="<?php echo $string['next']; ?>" name="next" id="next_button" />
                <input type="submit" class="ok" style="display:none" value="<?php echo $string['copypaper']; ?>" name="submit" id="submit_button" />
            <?php else : ?>
                <input type="button" class="cancel" value="<?php echo $string['cancel']; ?>" name="cancel" id="cancel" />
                <input type="button" class="ok" style="display:none" value="<?php echo $string['next']; ?>" name="next" id="next_button" />
                <input type="submit" class="ok" value="<?php echo $string['copypaper']; ?>" name="submit" id="submit_button" />
            <?php endif; ?>
        </div>
        
        </form>
    </div>
</body>
</html>
