CREATE FUNCTION `%%prefix%%strip_md_syntax`(_str LONGTEXT) RETURNS LONGTEXT
    LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA

    BEGIN
        SET _str = REPLACE(_str, '`', ' ');
        SET _str = REPLACE(_str, '*', ' ');
        SET _str = REPLACE(_str, '~', ' ');
        SET _str = REPLACE(_str, '#', ' ');
        SET _str = REPLACE(_str, '<', ' ');
        SET _str = REPLACE(_str, '>', ' ');
        RETURN _str;
    END;
