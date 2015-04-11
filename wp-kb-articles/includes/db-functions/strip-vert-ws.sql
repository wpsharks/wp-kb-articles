CREATE FUNCTION `%%prefix%%strip_vert_ws`($str LONGTEXT) RETURNS LONGTEXT
    LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA

    BEGIN
        SET $str = REPLACE($str, '\r', ' ');
        SET $str = REPLACE($str, '\n', ' ');
        SET $str = REPLACE($str, '\t', ' ');
        RETURN $str;
    END;
