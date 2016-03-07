<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1642_lticontextid')) {
        $configObj = Config::get_instance();
        $lti_integration = $configObj->get('lti_integration');
        // Delete metamodule contet links as they make no sense.
        if ($lti_integration == 'UoN') {
            $select_sql = "SELECT c.c_internal_id FROM lti_context c WHERE (LENGTH(c.c_internal_id) - LENGTH(REPLACE(c.c_internal_id, '-', ''))) > 1
                AND c.c_internal_id NOT LIKE 'ZZ-%'";
            $modules = $mysqli->prepare($select_sql);
            $modules->execute();
            $modules->store_result();
            $modules->bind_result($id);
            while ($modules->fetch()) {
                $delete_sql = "DELETE FROM lti_context WHERE c_internal_id = ?";
                $delete = $mysqli->prepare($delete_sql);
                $delete->bind_param('s', $id);
                $delete->execute();
                $delete->close();
            }
            $modules->close();
        }
        // Update context ids from short codes to module ids.
        if ($lti_integration == 'UoN') {
            // Saturn modules and fake modules accounted for.
            $select_sql = "SELECT c.c_internal_id, m.id FROM lti_context c, modules m WHERE
                (m.moduleid LIKE '%\_UNNC' and SUBSTRING_INDEX(c.c_internal_id, '-CN', 1) = SUBSTRING_INDEX (m.moduleid, '_', 1))
                OR (m.moduleid LIKE '%\_UNMC' and SUBSTRING_INDEX(c.c_internal_id, '-MY', 1) = SUBSTRING_INDEX (m.moduleid, '_', 1))
                OR (SUBSTRING_INDEX(c.c_internal_id, '-UK', 1) = m.moduleid)
                OR ((m.moduleid LIKE '%\_UNNMC' and SUBSTRING_INDEX (m.moduleid, '_', 1) = SUBSTRING_INDEX(SUBSTRING(c_internal_id, 4), '-MY', 1))
                OR (m.moduleid LIKE '%\_UNNC' and SUBSTRING_INDEX (m.moduleid, '_', 1) = SUBSTRING_INDEX(SUBSTRING(c_internal_id, 4), '-CN', 1))
                OR (m.moduleid = SUBSTRING_INDEX(SUBSTRING(c_internal_id, 4), '-UK', 1)) AND c_internal_id LIKE 'ZZ-%')";
        } else {
            $select_sql = "SELECT c.c_internal_id, m.id FROM lti_context c, modules m WHERE c.c_internal_id = m.moduleid";
        }
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