CREATE FUNCTION `%%prefix%%strip_vert_ws`(_str LONGTEXT) RETURNS LONGTEXT
    LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA

    BEGIN
        SET _str = REPLACE(_str, '\r', ' ');
        SET _str = REPLACE(_str, '\n', ' ');
        SET _str = REPLACE(_str, '\t', ' ');
        RETURN _str;
    END;
