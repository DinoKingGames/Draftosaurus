DELIMITER $$

DROP TRIGGER IF EXISTS unico_superadmin_insert $$
CREATE TRIGGER unico_superadmin_insert
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.rol = 'superadmin' AND
       (SELECT COUNT(*) FROM usuarios WHERE rol = 'superadmin') > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un superadmin. No se puede crear otro.';
    END IF;
END $$

DROP TRIGGER IF EXISTS unico_superadmin_update $$
CREATE TRIGGER unico_superadmin_update
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.rol = 'superadmin' AND
       (SELECT COUNT(*) FROM usuarios WHERE rol = 'superadmin' AND id <> NEW.id) > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un superadmin. No se puede asignar otro.';
    END IF;
END $$

DELIMITER ;