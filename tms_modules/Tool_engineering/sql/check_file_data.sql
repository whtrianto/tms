-- Script untuk melihat data file (Drawing dan Sketch) di database TMS_NEW
-- Menampilkan sample data untuk memahami format file identifier

USE TMS_NEW;
GO

-- 1. Lihat sample data MLR_DRAWING dan MLR_SKETCH dari TMS_TOOL_MASTER_LIST_REV
--    yang memiliki file (tidak NULL dan tidak kosong)
SELECT TOP 20
    rev.MLR_ID,
    ml.ML_TOOL_DRAW_NO AS Drawing_No,
    rev.MLR_REV AS Revision,
    rev.MLR_DRAWING AS Drawing_File_Identifier,
    rev.MLR_SKETCH AS Sketch_File_Identifier,
    LEN(rev.MLR_DRAWING) AS Drawing_Length,
    LEN(rev.MLR_SKETCH) AS Sketch_Length,
    CASE 
        WHEN rev.MLR_DRAWING LIKE 'http%' THEN 'Full URL'
        WHEN rev.MLR_DRAWING LIKE '%.%' THEN 'Has Extension'
        ELSE 'Identifier/Encoded'
    END AS Drawing_Type,
    CASE 
        WHEN rev.MLR_SKETCH LIKE 'http%' THEN 'Full URL'
        WHEN rev.MLR_SKETCH LIKE '%.%' THEN 'Has Extension'
        ELSE 'Identifier/Encoded'
    END AS Sketch_Type
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

-- 2. Hitung total record yang memiliki Drawing file
SELECT 
    COUNT(*) AS Total_With_Drawing,
    COUNT(DISTINCT CASE WHEN rev.MLR_DRAWING IS NOT NULL AND rev.MLR_DRAWING <> '' THEN rev.MLR_ID END) AS Unique_With_Drawing
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1;
GO

-- 3. Hitung total record yang memiliki Sketch file
SELECT 
    COUNT(*) AS Total_With_Sketch,
    COUNT(DISTINCT CASE WHEN rev.MLR_SKETCH IS NOT NULL AND rev.MLR_SKETCH <> '' THEN rev.MLR_ID END) AS Unique_With_Sketch
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1;
GO

-- 4. Analisa pattern file identifier (apakah ada pattern khusus)
SELECT TOP 50
    rev.MLR_DRAWING AS File_Identifier,
    COUNT(*) AS Count,
    MIN(rev.MLR_ID) AS First_MLR_ID,
    MAX(rev.MLR_ID) AS Last_MLR_ID
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1
    AND rev.MLR_DRAWING IS NOT NULL 
    AND rev.MLR_DRAWING <> ''
GROUP BY rev.MLR_DRAWING
ORDER BY Count DESC, First_MLR_ID DESC;
GO

-- 5. Cek apakah ada tabel lain yang menyimpan file content (BLOB/IMAGE/VARBINARY)
SELECT 
    t.name AS Table_Name,
    c.name AS Column_Name,
    ty.name AS Data_Type,
    c.max_length AS Max_Length
FROM sys.tables t
INNER JOIN sys.columns c ON t.object_id = c.object_id
INNER JOIN sys.types ty ON c.user_type_id = ty.user_type_id
WHERE ty.name IN ('image', 'varbinary', 'binary', 'varbinary(max)', 'image')
    AND t.name LIKE '%TMS%'
ORDER BY t.name, c.name;
GO

-- 6. Cek apakah ada stored procedure atau function yang terkait dengan file
SELECT 
    o.name AS Object_Name,
    o.type_desc AS Object_Type
FROM sys.objects o
WHERE o.type IN ('P', 'FN', 'IF', 'TF') -- Procedure, Function
    AND (
        o.name LIKE '%file%' 
        OR o.name LIKE '%drawing%'
        OR o.name LIKE '%sketch%'
        OR o.name LIKE '%attachment%'
        OR o.name LIKE '%GetFile%'
    )
ORDER BY o.name;
GO

-- 7. Cek apakah ada view yang terkait dengan file
SELECT 
    v.name AS View_Name,
    m.definition AS View_Definition
FROM sys.views v
INNER JOIN sys.sql_modules m ON v.object_id = m.object_id
WHERE (
    m.definition LIKE '%MLR_DRAWING%'
    OR m.definition LIKE '%MLR_SKETCH%'
    OR m.definition LIKE '%GetFile%'
)
ORDER BY v.name;
GO

