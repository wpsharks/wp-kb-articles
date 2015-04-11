CREATE FUNCTION `%%prefix%%strip_tags`($str LONGTEXT) RETURNS LONGTEXT
    LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA

    BEGIN
        DECLARE $start, $end INT DEFAULT 1; LOOP
            SET $start = LOCATE('<', $str, $start);
            IF(!$start) THEN RETURN $str; END IF;
            SET $end = LOCATE('>', $str, $start);
            IF(!$end) THEN SET $end = $start; END IF;
            SET $str = INSERT($str, $start, $end - $start + 1, '');
        END LOOP;
    END;
