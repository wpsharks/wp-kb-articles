CREATE FUNCTION `%%prefix%%strip_md_syntax`($str LONGTEXT) RETURNS LONGTEXT
    LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA

    BEGIN
        SET $str = REPLACE($str, '`', '');
        SET $str = REPLACE($str, '*', '');
        SET $str = REPLACE($str, '~', '');
        RETURN $str;
    END;
