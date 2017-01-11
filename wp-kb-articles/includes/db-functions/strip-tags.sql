CREATE FUNCTION `%%prefix%%strip_tags`(_str LONGTEXT) RETURNS LONGTEXT
    LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA

    BEGIN
        DECLARE _start, _end INT DEFAULT 1; LOOP
            SET _start = LOCATE('<', _str, _start);
            IF(!_start) THEN RETURN _str; END IF;
            SET _end = LOCATE('>', _str, _start);
            IF(!_end) THEN SET _end = _start; END IF;
            SET _str = INSERT(_str, _start, _end - _start + 1, '');
        END LOOP;
    END;
