-- Script untuk menambahkan kolom IS_TRIAL_BOM ke tabel TMS_TC_TOOL_BOM_ENGIN
-- Jalankan script ini untuk menambahkan kolom Trial BOM

USE TMS_NEW;
GO

-- Cek dan tambah kolom IS_TRIAL_BOM
IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'dbo' 
               AND TABLE_NAME = 'TMS_TC_TOOL_BOM_ENGIN' 
               AND COLUMN_NAME = 'IS_TRIAL_BOM')
BEGIN
    ALTER TABLE TMS_TC_TOOL_BOM_ENGIN
    ADD IS_TRIAL_BOM BIT NULL DEFAULT 0;
    
    PRINT 'Kolom IS_TRIAL_BOM berhasil ditambahkan ke TMS_TC_TOOL_BOM_ENGIN.';
END
ELSE
BEGIN
    PRINT 'Kolom IS_TRIAL_BOM sudah ada di TMS_TC_TOOL_BOM_ENGIN.';
END
GO

PRINT 'Script selesai.';
GO

