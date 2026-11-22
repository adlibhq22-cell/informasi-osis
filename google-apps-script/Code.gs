/*
 * Informasi OSIS — Google Drive private storage bridge.
 * Script Properties (Project Settings > Script properties):
 *   OSIS_FOLDER_ID = ID folder Drive utama
 *   OSIS_SECRET    = token acak panjang, sama dengan OSIS_DRIVE_SECRET di Laravel
 * Deploy > New deployment > Web app > Execute as Me > Anyone.
 * URL deployment hanya dipanggil dari server Laravel, jangan dari JavaScript browser.
 */
var CATEGORIES = ['anggota', 'pengurus', 'kegiatan', 'galeri', 'laporan', 'tugas'];
var MIMES = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
var MAX_BYTES = 8 * 1024 * 1024;

function reply_(object) {
  return ContentService.createTextOutput(JSON.stringify(object))
    .setMimeType(ContentService.MimeType.JSON);
}

function belongsToRoot_(file, root) {
  var parents = file.getParents();
  while (parents.hasNext()) {
    var folder = parents.next();
    if (folder.getId() === root.getId()) return true;
    var grandparents = folder.getParents();
    while (grandparents.hasNext()) {
      if (grandparents.next().getId() === root.getId()) return true;
    }
  }
  return false;
}

function doPost(e) {
  try {
    var props = PropertiesService.getScriptProperties();
    var secret = props.getProperty('OSIS_SECRET');
    var folderId = props.getProperty('OSIS_FOLDER_ID');
    if (!secret || !folderId) throw new Error('Script Properties belum dikonfigurasi.');
    if (!e || !e.postData || !e.postData.contents) throw new Error('Body request kosong.');
    var body = JSON.parse(e.postData.contents);
    if (!body.secret || body.secret !== secret) throw new Error('Token tidak valid.');
    var root = DriveApp.getFolderById(folderId);

    if (body.action === 'upload') {
      if (CATEGORIES.indexOf(body.category) === -1) throw new Error('Kategori tidak valid.');
      if (MIMES.indexOf(body.mimeType) === -1) throw new Error('Tipe file tidak diizinkan.');
      if (typeof body.name !== 'string' || !/^[a-f0-9]{32}\.(jpg|png|webp|pdf)$/.test(body.name)) {
        throw new Error('Nama file tidak valid.');
      }
      if (typeof body.data !== 'string' || body.data.length > Math.ceil(MAX_BYTES / 3) * 4 + 8) {
        throw new Error('File melebihi batas 8 MB.');
      }
      var bytes = Utilities.base64Decode(body.data);
      if (bytes.length > MAX_BYTES || bytes.length === 0) throw new Error('Ukuran file tidak valid.');
      var subfolders = root.getFoldersByName(body.category);
      var folder = subfolders.hasNext() ? subfolders.next() : root.createFolder(body.category);
      var file = folder.createFile(Utilities.newBlob(bytes, body.mimeType, body.name));
      return reply_({ok: true, id: file.getId()});
    }

    if (!/^[A-Za-z0-9_-]{10,128}$/.test(String(body.id || ''))) throw new Error('ID file tidak valid.');
    var existing = DriveApp.getFileById(body.id);
    if (existing.isTrashed() || !belongsToRoot_(existing, root)) throw new Error('File tidak ditemukan di folder OSIS.');

    if (body.action === 'read') {
      if (MIMES.indexOf(existing.getMimeType()) === -1 || existing.getSize() > MAX_BYTES) {
        throw new Error('File tidak dapat ditampilkan.');
      }
      return reply_({ok: true, mimeType: existing.getMimeType(), data: Utilities.base64Encode(existing.getBlob().getBytes())});
    }
    if (body.action === 'delete') {
      existing.setTrashed(true);
      return reply_({ok: true});
    }
    throw new Error('Aksi tidak dikenali.');
  } catch (error) {
    return reply_({ok: false, error: String(error.message || error)});
  }
}
