<?php require_once 'config/database.php'; \ = db(); print_r(\->select('SHOW TABLES')); \ = \->select('DESCRIBE contacts'); if (\) print_r(\); else echo 'No contacts table'; ?>
