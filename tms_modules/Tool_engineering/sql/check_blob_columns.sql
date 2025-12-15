-- Script untuk mengecek kolom BLOB/VARBINARY/IMAGE di database TMS_NEW
-- Digunakan untuk menemukan di mana file disimpan sebagai BLOB

USE TMS_NEW;
GO

-- 1. Cek kolom BLOB di tabel TMS_TOOL_MASTER_LIST_REV
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbo'
    AND TABLE_NAME = 'TMS_TOOL_MASTER_LIST_REV'
    AND (
        DATA_TYPE IN ('image', 'varbinary', 'binary', 'varbinary(max)')
        OR COLUMN_NAME LIKE '%DRAWING%'
        OR COLUMN_NAME LIKE '%SKETCH%'
        OR COLUMN_NAME LIKE '%FILE%'
        OR COLUMN_NAME LIKE '%BLOB%'
        OR COLUMN_NAME LIKE '%BINARY%'
        OR COLUMN_NAME LIKE '%IMAGE%'
    )
ORDER BY COLUMN_NAME;
GO

-- 2. Cek apakah ada tabel file storage terpisah
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbo'
    AND (
        TABLE_NAME LIKE '%FILE%'
        OR TABLE_NAME LIKE '%STORAGE%'
        OR TABLE_NAME LIKE '%ATTACHMENT%'
        OR TABLE_NAME LIKE '%BLOB%'
        OR TABLE_NAME LIKE '%BINARY%'
    )
    AND (
        DATA_TYPE IN ('image', 'varbinary', 'binary', 'varbinary(max)')
        OR COLUMN_NAME LIKE '%CONTENT%'
        OR COLUMN_NAME LIKE '%DATA%'
        OR COLUMN_NAME LIKE '%BLOB%'
        OR COLUMN_NAME LIKE '%BINARY%'
    )
ORDER BY TABLE_NAME, COLUMN_NAME;
GO

-- 3. Cek sample data dari MLR_DRAWING dan MLR_SKETCH
--    untuk melihat apakah isinya adalah identifier atau BLOB
SELECT TOP 5
    rev.MLR_ID,
    rev.MLR_DRAWING,
    rev.MLR_SKETCH,
    LEN(rev.MLR_DRAWING) AS DRAWING_LENGTH,
    LEN(rev.MLR_SKETCH) AS SKETCH_LENGTH,
    -- Cek apakah isinya terlihat seperti binary (non-printable characters)
    CASE 
        WHEN rev.MLR_DRAWING IS NOT NULL THEN 
            CASE 
                WHEN PATINDEX('%[^a-zA-Z0-9%+\/=_-]%', rev.MLR_DRAWING) > 0 THEN 'Contains special chars (might be encoded)'
                ELSE 'Looks like identifier/text'
            END
        ELSE 'NULL'
    END AS DRAWING_TYPE,
    CASE 
        WHEN rev.MLR_SKETCH IS NOT NULL THEN 
            CASE 
                WHEN PATINDEX('%[^a-zA-Z0-9%+\/=_-]%', rev.MLR_SKETCH) > 0 THEN 'Contains special chars (might be encoded)'
                ELSE 'Looks like identifier/text'
            END
        ELSE 'NULL'
    END AS SKETCH_TYPE
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1
    AND (
        (rev.MLR_DRAWING IS NOT NULL AND rev.MLR_DRAWING <> '')
        OR (rev.MLR_SKETCH IS NOT NULL AND rev.MLR_SKETCH <> '')
    )
ORDER BY rev.MLR_ID DESC;
GO

-- 4. Cek semua tabel yang mungkin menyimpan file BLOB
SELECT DISTINCT
    t.name AS TABLE_NAME,
    c.name AS COLUMN_NAME,
    ty.name AS DATA_TYPE,
    c.max_length AS MAX_LENGTH
FROM sys.tables t
INNER JOIN sys.columns c ON t.object_id = c.object_id
INNER JOIN sys.types ty ON c.user_type_id = ty.user_type_id
WHERE t.schema_id = SCHEMA_ID('dbo')
    AND ty.name IN ('image', 'varbinary', 'binary')
ORDER BY t.name, c.name;
GO

-- 5. Cek apakah ada kolom VARBINARY(MAX) yang mungkin menyimpan file
SELECT 
    t.name AS TABLE_NAME,
    c.name AS COLUMN_NAME,
    ty.name AS DATA_TYPE,
    c.max_length AS MAX_LENGTH
FROM sys.tables t
INNER JOIN sys.columns c ON t.object_id = c.object_id
INNER JOIN sys.types ty ON c.user_type_id = ty.user_type_id
WHERE t.schema_id = SCHEMA_ID('dbo')
    AND (
        (ty.name = 'varbinary' AND c.max_length = -1) -- VARBINARY(MAX)
        OR ty.name = 'image'
    )
ORDER BY t.name, c.name;
GO

