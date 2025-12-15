# Analisis Penyimpanan File Drawing dan Sketch

## Struktur Database

Berdasarkan analisis `struktur-tms.sql`, file gambar/PDF **TIDAK disimpan sebagai BLOB di database**, melainkan:

### Kolom di Tabel `TMS_TOOL_MASTER_LIST_REV`:
- `MLR_DRAWING` [varchar](50) - Menyimpan **identifier/path file** (bukan file content)
- `MLR_SKETCH` [varchar](50) - Menyimpan **identifier/path file** (bukan file content)

### Kesimpulan:
1. **File disimpan di filesystem server** (bukan di database)
2. Kolom `MLR_DRAWING` dan `MLR_SKETCH` hanya menyimpan **identifier** yang digunakan untuk:
   - Mencari file di filesystem server
   - Atau digunakan sebagai parameter untuk `GetFile.aspx`

## Format URL Server

Berdasarkan contoh URL yang diberikan:
```
http://10.82.101.79/FexTMS/Shared/GetFile.aspx?m=ToolDrawing&f=u%2fh99NB%2bGssvx%2bBTsaBr8mVAuwMDXSOnvKAFX2iyartrkQMQGd1uypLR7tpXu2iJ0c%2b7JkjIBcu%2bXZu7r%2bN7pw%3d%3d
```

Format:
- **Base URL**: `http://10.82.101.79/FexTMS/Shared/GetFile.aspx`
- **Parameter `m`**: Module name (contoh: `ToolDrawing`)
- **Parameter `f`**: File identifier (dari kolom `MLR_DRAWING` atau `MLR_SKETCH`, sudah di-encode)

## Cara Melihat Data File di Database

Jalankan script SQL berikut untuk melihat data aktual:

### 1. Lihat Sample Data File Identifier
```sql
USE TMS_NEW;
GO

SELECT TOP 20
    rev.MLR_ID,
    ml.ML_TOOL_DRAW_NO AS Drawing_No,
    rev.MLR_REV AS Revision,
    rev.MLR_DRAWING AS Drawing_File_Identifier,
    rev.MLR_SKETCH AS Sketch_File_Identifier
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1
    AND (
        (rev.MLR_DRAWING IS NOT NULL AND rev.MLR_DRAWING <> '')
        OR (rev.MLR_SKETCH IS NOT NULL AND rev.MLR_SKETCH <> '')
    )
ORDER BY rev.MLR_ID DESC;
```

### 2. Hitung Total File yang Ada
```sql
-- Total dengan Drawing file
SELECT 
    COUNT(*) AS Total_Records,
    COUNT(CASE WHEN rev.MLR_DRAWING IS NOT NULL AND rev.MLR_DRAWING <> '' THEN 1 END) AS With_Drawing,
    COUNT(CASE WHEN rev.MLR_SKETCH IS NOT NULL AND rev.MLR_SKETCH <> '' THEN 1 END) AS With_Sketch
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1;
```

### 3. Analisa Pattern File Identifier
```sql
-- Lihat pattern file identifier
SELECT TOP 50
    rev.MLR_DRAWING AS File_Identifier,
    LEN(rev.MLR_DRAWING) AS Length,
    CASE 
        WHEN rev.MLR_DRAWING LIKE 'http%' THEN 'Full URL'
        WHEN rev.MLR_DRAWING LIKE '%.%' THEN 'Has Extension (filename)'
        WHEN rev.MLR_DRAWING LIKE '%/%' OR rev.MLR_DRAWING LIKE '%\%' THEN 'Path'
        ELSE 'Encoded/Identifier'
    END AS Type
FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
    ON ml.ML_ID = rev.MLR_ML_ID
WHERE ml.ML_TYPE = 1
    AND rev.MLR_DRAWING IS NOT NULL 
    AND rev.MLR_DRAWING <> ''
ORDER BY rev.MLR_ID DESC;
```

## Implementasi di Aplikasi

### Controller (`Tool_draw_engin.php`)
Method `build_file_url()` sudah dibuat untuk:
1. Menerima file identifier dari database
2. Membangun URL server dengan format: `GetFile.aspx?m=ToolDrawing&f={identifier}`
3. Meng-encode parameter `f` dengan `urlencode()`

### View (`index_tool_draw_engin.php`)
JavaScript sudah diperbarui untuk:
1. Menggunakan URL server (bukan path lokal)
2. Menampilkan gambar sebagai thumbnail
3. Menampilkan tombol untuk PDF dan file lainnya

## Langkah Selanjutnya

1. **Jalankan script SQL** di file `check_file_data.sql` untuk melihat data aktual
2. **Verifikasi format identifier** yang tersimpan di database:
   - Apakah sudah dalam format encoded?
   - Apakah perlu di-encode lagi?
   - Atau sudah dalam format path/filename?
3. **Test URL** dengan data aktual dari database
4. **Sesuaikan encoding** jika diperlukan

## Catatan

- File **TIDAK disimpan di database** sebagai BLOB
- File disimpan di **filesystem server** (lokasi fisik tidak diketahui dari struktur database)
- `GetFile.aspx` adalah handler yang membaca file dari filesystem berdasarkan identifier
- Identifier di database (`MLR_DRAWING`/`MLR_SKETCH`) digunakan untuk mencari file di server

