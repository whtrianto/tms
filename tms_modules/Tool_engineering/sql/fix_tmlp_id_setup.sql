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
-- PERHATIAN: Script ini akan memodifikasi struktur tabel!
-- BACKUP DATABASE dulu sebelum menjalankan!

-- ============================================
-- STEP 1: Backup data
-- ============================================
IF OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS_BACKUP', 'U') IS NOT NULL
    DROP TABLE TMS_TOOL_MASTER_LIST_PARTS_BACKUP;
GO

SELECT * INTO TMS_TOOL_MASTER_LIST_PARTS_BACKUP 
FROM TMS_TOOL_MASTER_LIST_PARTS;
GO

PRINT 'Data berhasil di-backup ke TMS_TOOL_MASTER_LIST_PARTS_BACKUP';
GO

-- ============================================
-- STEP 2: Hapus constraint DEFAULT jika ada
-- ============================================
IF EXISTS (SELECT 1 FROM sys.default_constraints 
            WHERE parent_object_id = OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS') 
            AND name = 'DF_TMS_TOOL_MASTER_LIST_PARTS_TMLP_ID')
BEGIN
    ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
    DROP CONSTRAINT DF_TMS_TOOL_MASTER_LIST_PARTS_TMLP_ID;
    PRINT 'DEFAULT constraint dihapus';
END
ELSE
    PRINT 'Tidak ada DEFAULT constraint untuk dihapus';
GO

-- ============================================
-- STEP 3: Hapus PRIMARY KEY constraint
-- ============================================
IF EXISTS (SELECT 1 FROM sys.key_constraints 
            WHERE parent_object_id = OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS') 
            AND name = 'PK_TMS_TOOL_MASTER_LIST_PARTS')
BEGIN
    ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
    DROP CONSTRAINT PK_TMS_TOOL_MASTER_LIST_PARTS;
    PRINT 'PRIMARY KEY constraint dihapus';
END
ELSE
    PRINT 'Tidak ada PRIMARY KEY constraint untuk dihapus';
GO

-- ============================================
-- STEP 4: Hapus kolom TMLP_ID lama
-- ============================================
IF EXISTS (SELECT 1 FROM sys.columns 
            WHERE object_id = OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS') 
            AND name = 'TMLP_ID')
BEGIN
    ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
    DROP COLUMN TMLP_ID;
    PRINT 'Kolom TMLP_ID lama dihapus';
END
ELSE
    PRINT 'Kolom TMLP_ID tidak ditemukan';
GO

-- ============================================
-- STEP 5: Tambah kolom TMLP_ID baru sebagai IDENTITY
-- ============================================
IF NOT EXISTS (SELECT 1 FROM sys.columns 
                WHERE object_id = OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS') 
                AND name = 'TMLP_ID')
BEGIN
    ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
    ADD TMLP_ID BIGINT IDENTITY(1,1) NOT NULL;
    PRINT 'Kolom TMLP_ID baru ditambahkan sebagai IDENTITY';
END
ELSE
    PRINT 'Kolom TMLP_ID sudah ada';
GO

-- ============================================
-- STEP 6: Tambah PRIMARY KEY constraint
-- ============================================
IF NOT EXISTS (SELECT 1 FROM sys.key_constraints 
                WHERE parent_object_id = OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS') 
                AND name = 'PK_TMS_TOOL_MASTER_LIST_PARTS')
BEGIN
    ALTER TABLE TMS_TOOL_MASTER_LIST_PARTS 
    ADD CONSTRAINT PK_TMS_TOOL_MASTER_LIST_PARTS 
    PRIMARY KEY CLUSTERED (TMLP_ID ASC);
    PRINT 'PRIMARY KEY constraint ditambahkan';
END
ELSE
    PRINT 'PRIMARY KEY constraint sudah ada';
GO

-- ============================================
-- STEP 7: Restore data (TMLP_ID akan auto-generate)
-- ============================================
-- Hapus data lama dulu (jika ada)
DELETE FROM TMS_TOOL_MASTER_LIST_PARTS;
GO

-- Insert data dari backup (TMLP_ID akan auto-generate)
SET IDENTITY_INSERT TMS_TOOL_MASTER_LIST_PARTS ON;
GO

INSERT INTO TMS_TOOL_MASTER_LIST_PARTS (TMLP_ID, TMLP_ML_ID, TMLP_PART_ID)
SELECT 
    ROW_NUMBER() OVER (ORDER BY TMLP_ML_ID, TMLP_PART_ID) AS TMLP_ID,
    TMLP_ML_ID, 
    TMLP_PART_ID 
FROM TMS_TOOL_MASTER_LIST_PARTS_BACKUP;
GO

SET IDENTITY_INSERT TMS_TOOL_MASTER_LIST_PARTS OFF;
GO

PRINT 'Data berhasil di-restore';
GO

-- ============================================
-- STEP 8: Verifikasi
-- ============================================
SELECT 
    COUNT(*) AS TotalRows,
    COUNT(DISTINCT TMLP_ID) AS UniqueTMLP_ID,
    MIN(TMLP_ID) AS MinTMLP_ID,
    MAX(TMLP_ID) AS MaxTMLP_ID
FROM TMS_TOOL_MASTER_LIST_PARTS;
GO

-- Cek apakah IDENTITY sudah aktif
SELECT 
    c.name AS ColumnName,
    t.name AS TableName,
    c.is_identity AS IsIdentity,
    IDENT_SEED(t.name) AS SeedValue,
    IDENT_INCR(t.name) AS IncrementValue
FROM sys.columns c
INNER JOIN sys.tables t ON c.object_id = t.object_id
WHERE t.name = 'TMS_TOOL_MASTER_LIST_PARTS'
  AND c.name = 'TMLP_ID';
GO

PRINT '========================================';
PRINT 'TMLP_ID berhasil diubah ke BIGINT IDENTITY';
PRINT 'Backup table: TMS_TOOL_MASTER_LIST_PARTS_BACKUP';
PRINT 'Hapus backup table setelah verifikasi data benar';
PRINT '========================================';
GO

-- ============================================
-- STEP 9: (OPSIONAL) Hapus backup table setelah verifikasi
-- ============================================
/*
-- Uncomment baris di bawah ini untuk menghapus backup setelah verifikasi
IF OBJECT_ID('TMS_TOOL_MASTER_LIST_PARTS_BACKUP', 'U') IS NOT NULL
BEGIN
    DROP TABLE TMS_TOOL_MASTER_LIST_PARTS_BACKUP;
    PRINT 'Backup table dihapus';
END
GO
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

