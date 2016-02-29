<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1642_lticontextid')) {
        // Update context ids from short codes to module ids.
        $select_sql = "SELECT c.c_internal_id, m.id FROM lti_context c, modules m WHERE SUBSTRING_INDEX(c.c_internal_id, '-', 1) = m.moduleid";
        $modules = $mysqli->prepare($select_sql);
        $modules->execute();
        $modules->store_result();
        $modules->bind_result($lti, $id);
        while ($modules->fetch()) {
            $update_sql = "UPDATE lti_context SET c_internal_id = ? WHERE c_internal_id = ?";
            $update = $mysqli->prepare($update_sql);
            $update->bind_param('ss', $id, $lti);
            $update->execute();
            $update->close();
        }
        $modules->close();
        // Add alter context id to be integer
        $altersql = "ALTER TABLE lti_context MODIFY COLUMN `c_internal_id` int(11) NOT NULL";
        $updater_utils->execute_query($altersql, true);
        // Make internal id colmn a foreign key reference to modules table.
        $altersql = "ALTER TABLE lti_context ADD CONSTRAINT lticontext_fk0 FOREIGN KEY (c_internal_id) REFERENCES modules(id)";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1642_lticontextid');
    }
}