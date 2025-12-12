# Checklist Integritas Data Tool Drawing Engineering

## ID-ID yang HARUS SAMA PERSIS di Tabel Database

### 1. **TMS_TOOL_MASTER_LIST_REV** → **TMS_TOOL_MASTER_LIST**
- **Field**: `MLR_ML_ID` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `ML_ID` (di TMS_TOOL_MASTER_LIST)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_REV_TMS_TOOL_MASTER_LIST`
- **Status**: **REQUIRED** (NOT NULL)

### 2. **TMS_TOOL_MASTER_LIST_REV** → **MS_OPERATION**
- **Field**: `MLR_OP_ID` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `OP_ID` (di MS_OPERATION)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_REV_MS_OPERATION`
- **Status**: **REQUIRED** (NOT NULL) - untuk Process/Operation Name

### 3. **TMS_TOOL_MASTER_LIST_REV** → **MS_TOOL_CLASS**
- **Field**: `MLR_TC_ID` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `TC_ID` (di MS_TOOL_CLASS)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_REV_MS_TOOL_CLASS`
- **Status**: **OPTIONAL** (NULL allowed) - untuk Tool Name

### 4. **TMS_TOOL_MASTER_LIST_REV** → **MS_MAKER**
- **Field**: `MLR_MAKER_ID` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `MAKER_ID` (di MS_MAKER)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_REV_MS_MAKER`
- **Status**: **OPTIONAL** (NULL allowed) - untuk Maker Name

### 5. **TMS_TOOL_MASTER_LIST_REV** → **MS_MATERIAL**
- **Field**: `MLR_MAT_ID` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `MAT_ID` (di MS_MATERIAL)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_REV_MS_MATERIAL`
- **Status**: **OPTIONAL** (NULL allowed) - untuk Material Name

### 6. **TMS_TOOL_MASTER_LIST_REV** → **MS_MACHINES**
- **Field**: `MLR_MACG_ID` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `MAC_ID` (di MS_MACHINES)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_REV_MS_MACHINES`
- **Status**: **OPTIONAL** (NULL allowed) - untuk Machine Group Name

### 7. **TMS_TOOL_MASTER_LIST_REV** → **MS_USERS**
- **Field**: `MLR_MODIFIED_BY` (di TMS_TOOL_MASTER_LIST_REV)
- **Harus sama dengan**: `USR_ID` (di MS_USERS)
- **Constraint**: Tidak ada Foreign Key (tapi harus valid)
- **Status**: **REQUIRED** (NOT NULL) - untuk Modified By Name

### 8. **TMS_TOOL_MASTER_LIST_PARTS** → **TMS_TOOL_MASTER_LIST**
- **Field**: `TMLP_ML_ID` (di TMS_TOOL_MASTER_LIST_PARTS)
- **Harus sama dengan**: `ML_ID` (di TMS_TOOL_MASTER_LIST)
- **Constraint**: Foreign Key (implied)
- **Status**: **REQUIRED** - untuk Product Name

### 9. **TMS_TOOL_MASTER_LIST_PARTS** → **MS_PARTS**
- **Field**: `TMLP_PART_ID` (di TMS_TOOL_MASTER_LIST_PARTS)
- **Harus sama dengan**: `PART_ID` (di MS_PARTS)
- **Constraint**: Foreign Key `FK_TMS_TOOL_MASTER_LIST_PARTS_MS_PARTS`
- **Status**: **REQUIRED** - untuk Product Name

## Query untuk Cek Data

Jalankan file: `tms_modules/Tool_engineering/sql/check_data_integrity.sql`

Atau jalankan query berikut di SQL Server Management Studio:

```sql
-- Cek data terbaru (Top 10) dengan semua relasi
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
FROM TMS_DB.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_DB.dbo.TMS_TOOL_MASTER_LIST ml ON ml.ML_ID = rev.MLR_ML_ID
LEFT JOIN TMS_DB.dbo.MS_OPERATION op ON op.OP_ID = rev.MLR_OP_ID
LEFT JOIN TMS_DB.dbo.MS_TOOL_CLASS tc ON tc.TC_ID = rev.MLR_TC_ID
LEFT JOIN TMS_DB.dbo.MS_MAKER maker ON maker.MAKER_ID = rev.MLR_MAKER_ID
LEFT JOIN TMS_DB.dbo.MS_MATERIAL mat ON mat.MAT_ID = rev.MLR_MAT_ID
LEFT JOIN TMS_DB.dbo.MS_MACHINES mac ON mac.MAC_ID = rev.MLR_MACG_ID
LEFT JOIN TMS_DB.dbo.MS_USERS usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
WHERE ml.ML_TYPE = 1
ORDER BY rev.MLR_ID DESC;
```

## Masalah yang Sering Terjadi

1. **Data terbaru tidak muncul di tampilan pertama**
   - Cek: Apakah `ORDER BY rev.MLR_ID DESC` benar?
   - Cek: Apakah `MLR_ID` adalah ID terbesar untuk data terbaru?

2. **Tanggal tidak muncul**
   - Cek: Apakah `MLR_EFFECTIVE_DATE` dan `MLR_MODIFIED_DATE` tidak NULL?
   - Cek: Format tanggal di database (datetime vs date)

3. **Modified By kosong**
   - Cek: Apakah `MLR_MODIFIED_BY` ada di tabel `MS_USERS`?
   - Cek: Apakah `USR_ID` di `MS_USERS` sama dengan `MLR_MODIFIED_BY`?

4. **Product Name kosong**
   - Cek: Apakah ada data di `TMS_TOOL_MASTER_LIST_PARTS` untuk `ML_ID` tersebut?
   - Cek: Apakah `TMLP_PART_ID` ada di tabel `MS_PARTS`?

5. **Operation/Process Name kosong**
   - Cek: Apakah `MLR_OP_ID` ada di tabel `MS_OPERATION`?
   - Cek: Apakah `OP_ID` di `MS_OPERATION` sama dengan `MLR_OP_ID`?

6. **Tool Name kosong**
   - Cek: Apakah `MLR_TC_ID` ada di tabel `MS_TOOL_CLASS`?
   - Cek: Apakah `TC_ID` di `MS_TOOL_CLASS` sama dengan `MLR_TC_ID`?

## Cara Perbaikan

Jika ada ID yang tidak match:

1. **Update ID yang salah**:
   ```sql
   UPDATE TMS_DB.dbo.TMS_TOOL_MASTER_LIST_REV
   SET MLR_OP_ID = [ID_YANG_BENAR]
   WHERE MLR_ID = [TD_ID_YANG_SALAH];
   ```

2. **Atau hapus data yang tidak valid** (jika memang tidak diperlukan)

3. **Atau insert data master yang hilang** (jika ID master tidak ada)

