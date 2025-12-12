-- =============================================
-- Script untuk cek integritas data Tool Drawing Engineering
-- Jalankan query ini untuk memastikan semua ID relasi sudah benar
-- =============================================

USE TMS_DB;
GO

-- 1. Cek Tool Drawing yang tidak punya Master List
SELECT 'Missing Master List' AS Issue, 
       rev.MLR_ID AS TD_ID,
       rev.MLR_ML_ID AS ML_ID,
       'MLR_ML_ID tidak ada di TMS_TOOL_MASTER_LIST' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
LEFT JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 2. Cek Tool Drawing yang MLR_OP_ID tidak ada di MS_OPERATION
SELECT 'Invalid Operation ID' AS Issue,
       rev.MLR_ID AS TD_ID,
       rev.MLR_OP_ID AS OP_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'MLR_OP_ID tidak ada di MS_OPERATION' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_OPERATION op ON op.OP_ID = rev.MLR_OP_ID
WHERE rev.MLR_OP_ID IS NOT NULL AND op.OP_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 3. Cek Tool Drawing yang MLR_TC_ID tidak ada di MS_TOOL_CLASS
SELECT 'Invalid Tool Class ID' AS Issue,
       rev.MLR_ID AS TD_ID,
       rev.MLR_TC_ID AS TC_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'MLR_TC_ID tidak ada di MS_TOOL_CLASS' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_TOOL_CLASS tc ON tc.TC_ID = rev.MLR_TC_ID
WHERE rev.MLR_TC_ID IS NOT NULL AND tc.TC_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 4. Cek Tool Drawing yang MLR_MAKER_ID tidak ada di MS_MAKER
SELECT 'Invalid Maker ID' AS Issue,
       rev.MLR_ID AS TD_ID,
       rev.MLR_MAKER_ID AS MAKER_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'MLR_MAKER_ID tidak ada di MS_MAKER' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_MAKER maker ON maker.MAKER_ID = rev.MLR_MAKER_ID
WHERE rev.MLR_MAKER_ID IS NOT NULL AND maker.MAKER_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 5. Cek Tool Drawing yang MLR_MAT_ID tidak ada di MS_MATERIAL
SELECT 'Invalid Material ID' AS Issue,
       rev.MLR_ID AS TD_ID,
       rev.MLR_MAT_ID AS MAT_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'MLR_MAT_ID tidak ada di MS_MATERIAL' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_MATERIAL mat ON mat.MAT_ID = rev.MLR_MAT_ID
WHERE rev.MLR_MAT_ID IS NOT NULL AND mat.MAT_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 6. Cek Tool Drawing yang MLR_MACG_ID tidak ada di MS_MACHINES
SELECT 'Invalid Machine ID' AS Issue,
       rev.MLR_ID AS TD_ID,
       rev.MLR_MACG_ID AS MAC_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'MLR_MACG_ID tidak ada di MS_MACHINES' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_MACHINES mac ON mac.MAC_ID = rev.MLR_MACG_ID
WHERE rev.MLR_MACG_ID IS NOT NULL AND mac.MAC_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 7. Cek Tool Drawing yang MLR_MODIFIED_BY tidak ada di MS_USERS
SELECT 'Invalid Modified By User ID' AS Issue,
       rev.MLR_ID AS TD_ID,
       rev.MLR_MODIFIED_BY AS USR_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'MLR_MODIFIED_BY tidak ada di MS_USERS' AS Description
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_USERS usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
WHERE rev.MLR_MODIFIED_BY IS NOT NULL AND usr.USR_ID IS NULL
ORDER BY rev.MLR_ID DESC;

-- 8. Cek Tool Drawing yang TMLP_PART_ID tidak ada di MS_PARTS
SELECT 'Invalid Part ID in Tool Master List Parts' AS Issue,
       ml.ML_ID AS ML_ID,
       mlparts.TMLP_PART_ID AS PART_ID,
       ml.ML_TOOL_DRAW_NO AS Drawing_No,
       'TMLP_PART_ID tidak ada di MS_PARTS' AS Description
FROM TMS_TOOL_MASTER_LIST ml
INNER JOIN TMS_TOOL_MASTER_LIST_PARTS mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
LEFT JOIN MS_PARTS part ON part.PART_ID = mlparts.TMLP_PART_ID
WHERE part.PART_ID IS NULL
ORDER BY ml.ML_ID DESC;

-- 9. Cek data terbaru (Top 10) dengan semua relasi
SELECT TOP 10
    rev.MLR_ID AS TD_ID,
    ml.ML_TOOL_DRAW_NO AS Drawing_No,
    rev.MLR_REV AS Revision,
    rev.MLR_STATUS AS Status,
    rev.MLR_EFFECTIVE_DATE AS Effective_Date,
    rev.MLR_MODIFIED_DATE AS Modified_Date,
    usr.USR_NAME AS Modified_By,
    op.OP_NAME AS Operation,
    tc.TC_NAME AS Tool_Class,
    maker.MAKER_NAME AS Maker,
    mat.MAT_NAME AS Material,
    mac.MAC_NAME AS Machine,
    dbo.fnGetToolMasterListParts(ml.ML_ID) AS Products
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_OPERATION op ON op.OP_ID = rev.MLR_OP_ID
LEFT JOIN MS_TOOL_CLASS tc ON tc.TC_ID = rev.MLR_TC_ID
LEFT JOIN MS_MAKER maker ON maker.MAKER_ID = rev.MLR_MAKER_ID
LEFT JOIN MS_MATERIAL mat ON mat.MAT_ID = rev.MLR_MAT_ID
LEFT JOIN MS_MACHINES mac ON mac.MAC_ID = rev.MLR_MACG_ID
LEFT JOIN MS_USERS usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
WHERE ml.ML_TYPE = 1
ORDER BY rev.MLR_ID DESC;

-- 10. Summary: Hitung jumlah data yang bermasalah
SELECT 
    'Total Tool Drawing (ML_TYPE=1)' AS Category,
    COUNT(*) AS Count
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1

UNION ALL

SELECT 
    'Missing Operation Name' AS Category,
    COUNT(*) AS Count
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_OPERATION op ON op.OP_ID = rev.MLR_OP_ID
WHERE ml.ML_TYPE = 1 AND rev.MLR_OP_ID IS NOT NULL AND op.OP_ID IS NULL

UNION ALL

SELECT 
    'Missing Tool Class Name' AS Category,
    COUNT(*) AS Count
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_TOOL_CLASS tc ON tc.TC_ID = rev.MLR_TC_ID
WHERE ml.ML_TYPE = 1 AND rev.MLR_TC_ID IS NOT NULL AND tc.TC_ID IS NULL

UNION ALL

SELECT 
    'Missing User Name (Modified By)' AS Category,
    COUNT(*) AS Count
FROM TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN MS_USERS usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
WHERE ml.ML_TYPE = 1 AND rev.MLR_MODIFIED_BY IS NOT NULL AND usr.USR_ID IS NULL

UNION ALL

SELECT 
    'Missing Product Name' AS Category,
    COUNT(*) AS Count
FROM TMS_TOOL_MASTER_LIST ml
LEFT JOIN TMS_TOOL_MASTER_LIST_PARTS mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
WHERE ml.ML_TYPE = 1 AND mlparts.TMLP_ML_ID IS NULL;

GO

