-- =============================================
-- Script untuk memastikan TMLP_ID setup dengan benar
-- Setelah diubah dari uniqueidentifier ke bigint
-- =============================================

USE TMS_DB;
GO

-- 1. Cek struktur tabel saat ini
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    CHARACTER_MAXIMUM_LENGTH,
    NUMERIC_PRECISION,
    NUMERIC_SCALE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbo' 
  AND TABLE_NAME = 'TMS_TOOL_MASTER_LIST_PARTS'
ORDER BY ORDINAL_POSITION;

-- 2. Cek apakah TMLP_ID adalah IDENTITY
SELECT 
    c.name AS ColumnName,
    t.name AS DataType,
    c.is_identity AS IsIdentity,
    IDENT_SEED(t.name) AS SeedValue,
    IDENT_INCR(t.name) AS IncrementValue
FROM sys.columns c
INNER JOIN sys.tables t ON c.object_id = t.object_id
INNER JOIN sys.types ty ON c.user_type_id = ty.user_type_id
WHERE t.name = 'TMS_TOOL_MASTER_LIST_PARTS'
  AND c.name = 'TMLP_ID';

-- 3. Jika TMLP_ID belum IDENTITY, ubah ke IDENTITY
-- PERHATIAN: Script ini akan menghapus semua data di tabel!
-- Backup dulu sebelum menjalankan!

/*
-- Hapus constraint DEFAULT jika ada
IF EXISTS (SELECT 1 FROM sys.default_constraints WHERE parent_object_id = OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS') AND name = 'DF_TMS_TOOL_MASTER_LIST_PARTS_TMLP_ID')
BEGIN
    ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS DROP CONSTRAINT DF_TMS_TOOL_MASTER_LIST_PARTS_TMLP_ID;
    PRINT 'DEFAULT constraint dihapus';
END
GO

-- Backup data
SELECT * INTO TMS_TOOL_MASTER_LIST_PARTS_BACKUP FROM TMS_TOOL_MASTER_LIST_PARTS;
GO

-- Hapus PRIMARY KEY constraint
ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS DROP CONSTRAINT PK_TMS_TOOL_MASTER_LIST_PARTS;
GO

-- Hapus kolom TMLP_ID
ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS DROP COLUMN TMLP_ID;
GO

-- Tambah kolom TMLP_ID baru sebagai IDENTITY
ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
ADD TMLP_ID BIGINT IDENTITY(1,1) NOT NULL;
GO

-- Tambah PRIMARY KEY constraint
ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
ADD CONSTRAINT PK_TMS_TOOL_MASTER_LIST_PARTS PRIMARY KEY CLUSTERED (TMLP_ID ASC);
GO

-- Restore data (tanpa TMLP_ID, akan auto-generate)
SET IDENTITY_INSERT TMS_TOOL_MASTER_LIST_PARTS ON;
GO

INSERT INTO TMS_TOOL_MASTER_LIST_PARTS (TMLP_ML_ID, TMLP_PART_ID)
SELECT TMLP_ML_ID, TMLP_PART_ID FROM TMS_TOOL_MASTER_LIST_PARTS_BACKUP;
GO

SET IDENTITY_INSERT TMS_TOOL_MASTER_LIST_PARTS OFF;
GO

-- Hapus backup table
DROP TABLE TMS_TOOL_MASTER_LIST_PARTS_BACKUP;
GO

PRINT 'TMLP_ID berhasil diubah ke BIGINT IDENTITY';
*/

-- 4. Cek apakah ada data yang TMLP_ID nya NULL atau invalid
SELECT 
    COUNT(*) AS TotalRows,
    COUNT(TMLP_ID) AS RowsWithTMLP_ID,
    COUNT(TMLP_ML_ID) AS RowsWithTMLP_ML_ID,
    COUNT(TMLP_PART_ID) AS RowsWithTMLP_PART_ID
FROM TMS_TOOL_MASTER_LIST_PARTS;

-- 5. Cek apakah ada duplicate TMLP_ML_ID + TMLP_PART_ID (seharusnya tidak boleh)
SELECT 
    TMLP_ML_ID,
    TMLP_PART_ID,
    COUNT(*) AS DuplicateCount
FROM TMS_TOOL_MASTER_LIST_PARTS
GROUP BY TMLP_ML_ID, TMLP_PART_ID
HAVING COUNT(*) > 1;

-- 6. Test query yang digunakan di aplikasi
SELECT TOP 10
    ml.ML_ID,
    ml.ML_TOOL_DRAW_NO,
    dbo.fnGetToolMasterListParts(ml.ML_ID) AS ProductNames
FROM TMS_TOOL_MASTER_LIST ml
WHERE ml.ML_TYPE = 1
ORDER BY ml.ML_ID DESC;

GO

